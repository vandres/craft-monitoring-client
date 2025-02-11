<?php

namespace vandres\monitoringclient\controllers;

use vandres\monitoringclient\models\Settings;
use vandres\monitoringclient\MonitoringClient;
use vandres\monitoringclient\services\SystemReportService;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ApiController extends \craft\web\Controller
{
    public $enableCsrfValidation = false;
    protected $allowAnonymous = true;
    private $settings;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requirePostRequest();
        $this->settings = MonitoringClient::getInstance()->getSettings();

        $params = \Craft::$app->request->getBodyParams();
        $clientSecret = $params['clientSecret'] ?? null;

        if (empty($clientSecret) || empty($this->settings->clientSecret)) {
            throw new BadRequestHttpException('Valid token required');
        }

        if ($clientSecret !== $this->settings->clientSecret) {
            throw new BadRequestHttpException('Valid token required');
        }

        return true;
    }

    /**
     * Get all the client information.
     *
     * - PHP settings
     * - Plugins
     * - Craft version
     *
     * @return Response
     */
    public function actionSystemReport()
    {
        $service = new SystemReportService();

        return $this->asJson($service->getReport());
    }
}
