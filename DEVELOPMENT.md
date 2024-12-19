# Development

## `composer.json`

```json
"require": {
  "vandres/craft-monitoring-client": "*",
}
```
```json
"minimum-stability": "dev",
```
```json
"repositories": [
    {
        "type": "path",
        "url": "plugins/monitoring-client"
    },
    {
        "type": "composer",
        "url": "https://composer.craftcms.com",
        "canonical": false
    }
]
```

## `.env`

```php
# Plugin development
VANDRES_PLUGIN_DEVSERVER=1
```
