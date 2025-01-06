<?php

namespace vandres\monitoringclient\console\controllers;

use craft\console\Controller;
use yii\console\ExitCode;

class ApiController extends Controller
{
    /**
     * Generates and displays a secret key
     */
    public function actionKey()
    {
        $this->stdout(\Craft::$app->getSecurity()->generateRandomString(20));
        $this->stdout(PHP_EOL);

        return ExitCode::OK;
    }
}
