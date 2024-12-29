<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

use Wpshop\ExpertReview\Settings\PluginOptions;
use function Wpshop\ExpertReview\container;

require __DIR__ . '/vendor/autoload.php';

container()->get( PluginOptions::class )->destroy();
