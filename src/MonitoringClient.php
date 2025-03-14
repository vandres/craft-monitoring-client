<?php

namespace vandres\monitoringclient;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use vandres\monitoringclient\models\Settings;
use yii\base\Event;

/**
 * monitoring-client plugin
 *
 * @method static MonitoringClient getInstance()
 * @method Settings getSettings()
 * @author Volker Andres <andres@voan.ch>
 * @copyright Volker Andres
 * @license https://craftcms.github.io/license/ Craft License
 */
class MonitoringClient extends Plugin
{
    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    public static function config(): array
    {
        return [
            'components' => [
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->setUp();
        $this->setUpSite();
        $this->setUpCp();
    }

    private function setUp()
    {
        Craft::$app->onInit(function () {
            Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES,
                function (RegisterUrlRulesEvent $event) {
                    $event->rules = array_merge($event->rules, [
                        'POST monitoring/api/system-report' => 'monitoring-client/api/system-report',
                    ]);
                }
            );
        });
    }

    private function setUpSite()
    {
        Craft::$app->onInit(function () {
            if (!Craft::$app->getRequest()->getIsSiteRequest()) {
                return;
            }
        });
    }

    private function setUpCp()
    {
        Craft::$app->onInit(function () {
            if (!Craft::$app->getRequest()->getIsCpRequest()) {
                return;
            }
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(\vandres\monitoringclient\models\Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        // Get and pre-validate the settings
        $settings = $this->getSettings();
        $settings->validate();

        // Get the settings that are being defined by the config file
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

        return Craft::$app->view->renderTemplate('monitoring-client/_settings.twig', [
            'plugin' => $this,
            'overrides' => array_keys($overrides),
            'settings' => $settings,
        ]);
    }
}
