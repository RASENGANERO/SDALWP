<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

use Wpshop\ExpertReview\PluginContainer;
use Wpshop\ExpertReview\Settings\PluginOptions;

require __DIR__ . '/vendor/autoload.php';

PluginContainer::get( PluginOptions::class )->destroy();
