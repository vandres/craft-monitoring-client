<?php

namespace vandres\monitoringclient\models;

use craft\base\Model;

/**
 * monitoring-client settings
 */
class Settings extends Model
{
    public $secretsInPlainText = false;
    public $clientSecret = '';

    protected function defineRules(): array
    {
        return [
            [['clientSecret'], 'required'],
            [['secretsInPlainText'], 'boolean'],
        ];
    }
}
