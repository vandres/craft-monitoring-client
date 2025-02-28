<?php

namespace vandres\monitoringclient\services;

use Composer\InstalledVersions;
use Craft;
use craft\base\PluginInterface;
use craft\db\Connection;
use craft\helpers\App;
use craft\helpers\Db;
use vandres\monitoringclient\MonitoringClient;
use yii\base\Module;

class SystemReportService
{
    /**
     * @return array
     */
    public function getReport()
    {
        $modules = [];
        foreach (Craft::$app->getModules() as $id => $module) {
            if ($module instanceof PluginInterface) {
                continue;
            }
            if ($module instanceof Module) {
                $modules[$id] = get_class($module);
            } elseif (is_string($module)) {
                $modules[$id] = $module;
            } elseif (is_array($module) && isset($module['class'])) {
                $modules[$id] = $module['class'];
            } else {
                $modules[$id] = Craft::t('app', 'Unknown type');
            }
        }

        $aliases = [];
        foreach (Craft::$aliases as $alias => $value) {
            if (is_array($value)) {
                foreach ($value as $a => $v) {
                    $aliases[$a] = $v;
                }
            } else {
                $aliases[$alias] = $value;
            }
        }
        ksort($aliases);

        return [
            'appInfo' => $this->_appInfo(),
            'plugins' => array_map(fn($plugin) => [
                'name' => $plugin->name,
                'description' => $plugin->description,
                'documentation' => $plugin->documentationUrl,
                'edition' => $plugin->edition,
                'version' => $plugin->getVersion(),
            ], Craft::$app->getPlugins()->getAllPlugins(),),
            'modules' => $modules,
            'aliases' => $aliases,
            'phpInfo' => $this->_phpInfo(),
            'requirements' => $this->_requirementResults(),
            'updates' => $this->_getUpdates(),
        ];
    }

    private function _appInfo(): array
    {
        $info = [
            'php' => [
                'name' => 'PHP',
                'version' => App::phpVersion(),
            ],
            'os' => [
                'name' => PHP_OS,
                'version' => php_uname('r'),
            ],
            'database' => $this->_dbDriver(),
            'image' => $this->_imageDriver(),
            'craft' => [
                'name' => Craft::$app->getEditionName(),
                'version' => Craft::$app->getVersion(),
            ],
        ];

        if (!class_exists(InstalledVersions::class, false)) {
            $path = Craft::$app->getPath()->getVendorPath() . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'InstalledVersions.php';
            if (file_exists($path)) {
                require $path;
            }
        }

        if (class_exists(InstalledVersions::class, false)) {
            $this->_addVersion($info, 'Yii', 'yiisoft/yii2');
            $this->_addVersion($info, 'Twig', 'twig/twig');
            $this->_addVersion($info, 'Guzzle', 'guzzlehttp/guzzle');
        }

        return $info;
    }

    private static function _dbDriver(): array
    {
        $db = Craft::$app->getDb();
        $label = $db->getDriverLabel();
        $version = App::normalizeVersion($db->getSchema()->getServerVersion());
        return [
            'name' => $label,
            'version' => $version,
        ];
    }

    private function _imageDriver(): array
    {
        $imagesService = Craft::$app->getImages();

        if ($imagesService->getIsGd()) {
            $driverName = 'GD';
        } else {
            $driverName = 'Imagick';
        }

        return [
            'name' => $driverName,
            'version' => $imagesService->getVersion(),
        ];
    }

    private function _addVersion(array &$info, string $label, string $packageName): void
    {
        try {
            $version = InstalledVersions::getPrettyVersion($packageName) ?? InstalledVersions::getVersion($packageName);
        } catch (\OutOfBoundsException) {
            return;
        }

        if ($version !== null) {
            $info[strtolower($label)] = [
                'name' => $label,
                'version' => $version,
            ];
        }
    }

    private static function _phpInfo(): array
    {
        // Remove any arrays from $_ENV and $_SERVER to get around an "Array to string conversion" error
        $envVals = [];
        $serverVals = [];

        foreach ($_ENV as $key => $value) {
            if (is_array($value)) {
                $envVals[$key] = $value;
                $_ENV[$key] = 'Array';
            }
        }

        foreach ($_SERVER as $key => $value) {
            if (is_array($value)) {
                $serverVals[$key] = $value;
                $_SERVER[$key] = 'Array';
            }
        }

        ob_start();
        phpinfo(INFO_ALL);
        $phpInfoStr = ob_get_clean();

        // Put the original $_ENV and $_SERVER values back
        foreach ($envVals as $key => $value) {
            $_ENV[$key] = $value;
        }
        foreach ($serverVals as $key => $value) {
            $_SERVER[$key] = $value;
        }

        $replacePairs = [
            '#^.*<body>(.*)</body>.*$#ms' => '$1',
            '#<h2>PHP License</h2>.*$#ms' => '',
            '#<h1>Configuration</h1>#' => '',
            "#\r?\n#" => '',
            '#</(h1|h2|h3|tr)>#' => '</$1>' . "\n",
            '# +<#' => '<',
            "#[ \t]+#" => ' ',
            '#&nbsp;#' => ' ',
            '#  +#' => ' ',
            '# class=".*?"#' => '',
            '%&#039;%' => ' ',
            '#<tr>(?:.*?)"src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#' => '<h2>PHP Configuration</h2>' . "\n" . '<tr><td>PHP Version</td><td>$2</td></tr>' . "\n" . '<tr><td>PHP Egg</td><td>$1</td></tr>',
            '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#' => '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#' => '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" . '<tr><td>Zend Egg</td><td>$1</td></tr>',
            '# +#' => ' ',
            '#<tr>#' => '%S%',
            '#</tr>#' => '%E%',
        ];

        $phpInfoStr = preg_replace(array_keys($replacePairs), array_values($replacePairs), $phpInfoStr);

        $sections = explode('<h2>', strip_tags($phpInfoStr, '<h2><th><td>'));
        unset($sections[0]);

        $phpInfo = [];
        $security = Craft::$app->getSecurity();

        foreach ($sections as $section) {
            $heading = strtolower(substr($section, 0, strpos($section, '</h2>')));

            if (preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $matches, PREG_SET_ORDER) !== 0) {
                /** @var array[] $matches */
                foreach ($matches as $row) {
                    if (!isset($row[2])) {
                        continue;
                    }

                    $value = $row[2];
                    $name = strtolower($row[1]);

                    if (MonitoringClient::getInstance()->getSettings()->secretsInPlainText) {
                        $phpInfo[$heading][$name] = $value;
                    } else {
                        $phpInfo[$heading][$name] = $security->redactIfSensitive($name, $value);
                    }
                }
            }
        }

        return $phpInfo;
    }

    private function _requirementResults(): array
    {
        $reqCheck = new \RequirementsChecker();
        $dbConfig = Craft::$app->getConfig()->getDb();
        $reqCheck->dsn = $dbConfig->dsn;
        $reqCheck->dbDriver = $dbConfig->dsn ? Db::parseDsn($dbConfig->dsn, 'driver') : Connection::DRIVER_MYSQL;
        $reqCheck->dbUser = $dbConfig->user;
        $reqCheck->dbPassword = $dbConfig->password;
        $reqCheck->checkCraft();

        return $reqCheck->getResult()['requirements'];
    }

    private function _getUpdates()
    {
        return Craft::$app->getUpdates()->getUpdates();
    }
}
