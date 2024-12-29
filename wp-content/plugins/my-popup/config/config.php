<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\PluginMyPopup\SettingsProvider;
use Wpshop\PluginMyPopup\MetaBoxProvider;

return [
    'plugin_config'           => [
        'verify_url' => 'https://wpshop.ru/api.php',
        'update'     => [
            'url'          => 'https://api.wpgenerator.ru/wp-update-server/?action=get_metadata&slug=my-popup',
            'slug'         => 'my-popup',
            'check_period' => 12,
            'opt_name'     => 'my-popup-check-update',
        ],
    ],
    'settings_providers'      => [
        SettingsProvider::class,
    ],
    'metabox_providers'       => [
        MetaBoxProvider::class,
    ],
    'metabox_render_classmap' => [
    ],
];
