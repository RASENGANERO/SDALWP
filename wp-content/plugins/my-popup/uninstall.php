<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

use Wpshop\PluginMyPopup\Settings\PluginOptions;
use function Wpshop\PluginMyPopup\container;

require __DIR__ . '/vendor/autoload.php';

container()->get( PluginOptions::class )->destroy();
