<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\SettingsProvider;
use Wpshop\ExpertReview\MetaBoxProvider;

return [
    'plugin_config'           => [
        'verify_url' => 'https://wpshop.ru/api.php',
        'update'     => [
            'url'          => 'https://api.wpgenerator.ru/wp-update-server/?action=get_metadata&slug=expert-review',
            'slug'         => 'expert-review',
            'check_period' => 12,
            'opt_name'     => 'expert-review-check-update',
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

    'icons' => require __DIR__ . '/icons.php',
];
