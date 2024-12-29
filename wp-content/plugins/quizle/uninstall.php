<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

use function Wpshop\Quizle\uninstall;

require __DIR__ . '/vendor/autoload.php';

const QUIZLE_FILE       = __DIR__ . DIRECTORY_SEPARATOR . 'quizle.php';
const QUIZLE_SLUG       = 'quizle';
const QUIZLE_TEXTDOMAIN = 'quizle';

uninstall();
