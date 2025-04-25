<?php

namespace vandres\monitoringclient\console\controllers;

use yii\console\Controller as YiiController;
use yii\console\ExitCode;

class ApiController extends YiiController
{
    /**
     * Generates and displays a secret key
     */
    public function actionKey()
    {
        $this->stdout(\Craft::$app->getSecurity()->generateRandomString(50));
        $this->stdout(PHP_EOL);

        return ExitCode::OK;
    }
}
