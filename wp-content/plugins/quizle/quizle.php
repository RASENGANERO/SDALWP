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
 * Version:           1.4.0
 * Requires at least: 5.6
 * Tested up to:      6.7.1
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
use Wpshop\Quizle\Admin\Templates;
use Wpshop\Quizle\Analytics;
use Wpshop\Quizle\AssetsProvider;
use Wpshop\Quizle\Db\Upgrade;
use Wpshop\Quizle\DefaultHooks;
use Wpshop\Quizle\Encryption;
use Wpshop\Quizle\Admin\ImportExport;
use Wpshop\Quizle\FileUpload;
use Wpshop\Quizle\Integration\ReCaptcha;
use Wpshop\Quizle\Integration\YandexMetrika;
use Wpshop\Quizle\MailService;
use Wpshop\Quizle\Quizle;
use Wpshop\Quizle\QuizleResultExport;
use Wpshop\Quizle\QuizleResultHandler;
use Wpshop\Quizle\RestAPI;
use Wpshop\Quizle\Shortcodes;
use Wpshop\Quizle\Support\SeoSupport;
use function Wpshop\Quizle\container;

require __DIR__ . '/vendor/autoload.php';


const QUIZLE_VERSION    = '1.4.0';
const QUIZLE_FILE       = __FILE__;
const QUIZLE_SLUG       = 'quizle';
const QUIZLE_TEXTDOMAIN = 'quizle';
define( 'QUIZLE_BASENAME', plugin_basename( QUIZLE_FILE ) );

add_action( 'plugins_loaded', 'Wpshop\Quizle\init_i18n' );
add_action( 'activated_plugin', 'Wpshop\Quizle\redirect_on_activated' );
add_filter( 'plugin_action_links_' . QUIZLE_BASENAME, 'Wpshop\Quizle\add_settings_plugin_action' );
add_action( 'plugins_loaded', function () {
    container()->get( Analytics::class )->init();
    container()->get( AssetsProvider::class )->init();
    container()->get( DefaultHooks::class )->init();
    container()->get( Encryption::class )->init();
    container()->get( FileUpload::class )->init();
    container()->get( ImportExport::class )->init();
    container()->get( MailService::class )->init();
    container()->get( MenuPage::class )->init();
    container()->get( Quizle::class )->init();
    container()->get( QuizleResultActions::class )->init();
    container()->get( QuizlePostGrid::class )->init();
    container()->get( QuizleResultExport::class )->init();
    container()->get( QuizleResultGrid::class )->init();
    container()->get( QuizleResultHandler::class )->init();
    container()->get( ReCaptcha::class )->init();
    container()->get( RestAPI::class )->init();
    container()->get( SeoSupport::class )->init();
    container()->get( Settings::class )->init();
    container()->get( Shortcodes::class )->init();
    container()->get( Templates::class )->init();
    container()->get( Upgrade::class )->init();
    container()->get( YandexMetrika::class )->init();
} );

add_action( 'init', function () {
    if ( get_option( 'quizle--flush_rewrite_rules' ) ) {
        flush_rewrite_rules();
        delete_option( 'quizle--flush_rewrite_rules' );
    }
} );

register_activation_hook( QUIZLE_FILE, 'Wpshop\Quizle\activate' );
register_deactivation_hook( QUIZLE_FILE, 'Wpshop\Quizle\deactivate' );
//register_uninstall_hook( QUIZLE_FILE, 'Wpshop\Quizle\uninstall' );
