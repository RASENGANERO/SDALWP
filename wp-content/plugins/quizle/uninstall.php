<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

use function Wpshop\Quizle\uninstall;

require __DIR__ . '/vendor/autoload.php';

uninstall();
