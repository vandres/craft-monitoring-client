<?php

namespace vandres\monitoringclient\models;

use Craft;
use craft\base\Model;

/**
 * monitoring-client settings
 */
class Settings extends Model
{
    public string $clientSecret = '';

    public function defineRules(): array
    {
        return [
            [['clientSecret'], 'required'],
        ];
    }
}
