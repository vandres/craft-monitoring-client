<?php

namespace vandres\monitoringclient\models;

use craft\base\Model;

/**
 * monitoring-client settings
 */
class Settings extends Model
{
    public bool $secretsInPlainText = false;
    public string $clientSecret = '';

    public function defineRules(): array
    {
        return [
            [['clientSecret'], 'required'],
            [['secretsInPlainText'], 'boolean'],
        ];
    }
}
