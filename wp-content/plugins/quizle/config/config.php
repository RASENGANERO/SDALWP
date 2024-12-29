<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

return [
    'plugin_config' => [
        'verify_url' => 'https://wpshop.ru/api.php',
        'update'     => [
            'url'          => 'https://api.wpgenerator.ru/wp-update-server/?action=get_metadata&slug=quizle',
            'slug'         => 'quizle',
            'check_period' => 12,
            'opt_name'     => 'quizle-check-update',
        ],
    ],

    'templates_api' => defined( 'QUIZLE_LOCAL_TEMPLATES' ) ? QUIZLE_LOCAL_TEMPLATES : 'https://wpshop.pro/services/quizle-templates/api.php',

    'quizle_grid_screen_id' => 'quizle_page_quizle-results',
];
