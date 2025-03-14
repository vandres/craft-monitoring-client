# Monitoring Client

Provides information about the Craft installation via an API

## Goal

This plugin is very simple and just provides a JSON of all the versions of plugins, PHP and so on.

The goal is, to have the client installed on all your Craft websites and using a central server,
which aggregates and interprets the data.

## SaaS-Server

We offer a SaaS server for you to use directly. Only thing you will need, is the Pro edition of the plugin or an agency account.
The server is available, free for use, here https://craft-monitoring.com

## Requirements

This plugin requires Craft CMS 3.5 or later, and PHP 7.0 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “monitoring-client”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require vandres/craft-monitoring-client

# tell Craft to install the plugin
./craft plugin/install monitoring-client
```

## Generate key

You can use whatever key you want (except empty string). If you like, the plugin can create a key for you.
You will need to copy and paste it yourself.

`php craft monitoring-client/api/key`

## Configuration

You can use the settings dialog in the control panel. But I would recommend creating a `monitoring-client.php` in your config folder.

```php
return [
    'clientSecret' => \craft\helpers\App::env('MONITORING_CLIENT_SECRET'),
    'secretsInPlainText' => false,
];

```

## Usage

By installing and configuring the plugin, the information of the installation gets available via an endpoint.
You can access it manually like the following:

```shell
curl \
    --location 'https://my-project.test/monitoring/api/system-report' \
    --header 'Content-Type: application/json' \
    --data-raw '{
      "clientSecret": "Your secret"
    }'
```

## Support my work

PayPal: https://www.paypal.com/donate/?hosted_button_id=3WDU85HZCKMPA

Buy me a coffee: https://buymeacoffee.com/vandres

## Supporter

1. [Ambition Creative](https://www.ambitioncreative.co.uk/): Icons
