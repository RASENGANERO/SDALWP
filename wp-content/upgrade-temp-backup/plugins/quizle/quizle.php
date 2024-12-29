<?php

/**
 * A plugin for creating quizzes and tests in WordPress.
 *
 * @wordpress-plugin
 * Plugin Name:       Quizle
 * Plugin URI:        https://wpshop.ru/plugins/quizle
 * Description:       A plugin for creating quizzes and tests in WordPress.
 * Author:
 * Author URI:
 * License:
 * License URI:
 * Text Domain:       quizle
 * Domain Path:       /languages
 * Version:           1.1.0
 * Requires at least: 5.6
 * Tested up to:      6.2
 * Requires PHP:      7.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\Quizle\Admin\MenuPage;
use Wpshop\Quizle\Admin\QuizleResultActions;
use Wpshop\Quizle\Admin\QuizlePostGrid;
use Wpshop\Quizle\Admin\QuizleResultGrid;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Analytics;
use Wpshop\Quizle\AssetsProvider;
use Wpshop\Quizle\Db\Upgrade;
use Wpshop\Quizle\DefaultHooks;
use Wpshop\Quizle\Encryption;
use Wpshop\Quizle\MailService;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use Wpshop\Quizle\QuizleResultHandler;
use Wpshop\Quizle\Shortcodes;
use Wpshop\Quizle\Support\SeoSupport;

require __DIR__ . '/vendor/autoload.php';

const QUIZLE_VERSION    = '1.1.0';
const QUIZLE_FILE       = __FILE__;
const QUIZLE_SLUG       = 'quizle';
const QUIZLE_TEXTDOMAIN = 'quizle';
define( 'QUIZLE_BASENAME', plugin_basename( QUIZLE_FILE ) );

add_action( 'plugins_loaded', 'Wpshop\Quizle\init_i18n' );
add_action( 'activated_plugin', 'Wpshop\Quizle\redirect_on_activated' );
add_filter( 'plugin_action_links_' . QUIZLE_BASENAME, 'Wpshop\Quizle\add_settings_plugin_action' );
add_action( 'plugins_loaded', function () {
    PluginContainer::get( Analytics::class )->init();
    PluginContainer::get( AssetsProvider::class )->init();
    PluginContainer::get( DefaultHooks::class )->init();
    PluginContainer::get( Encryption::class )->init();
    PluginContainer::get( MailService::class )->init();
    PluginContainer::get( MenuPage::class )->init();
    PluginContainer::get( Quizle::class )->init();
    PluginContainer::get( QuizleResultActions::class )->init();
    PluginContainer::get( QuizlePostGrid::class )->init();
    PluginContainer::get( QuizleResultGrid::class )->init();
    PluginContainer::get( QuizleResultHandler::class )->init();
    PluginContainer::get( SeoSupport::class )->init();
    PluginContainer::get( Settings::class )->init();
    PluginContainer::get( Shortcodes::class )->init();
    PluginContainer::get( Upgrade::class )->init();
} );

register_activation_hook( __FILE__, 'Wpshop\Quizle\activate' );
register_deactivation_hook( __FILE__, 'Wpshop\Quizle\deactivate' );
//register_uninstall_hook( __FILE__, 'Wpshop\Quizle\uninstall' );
