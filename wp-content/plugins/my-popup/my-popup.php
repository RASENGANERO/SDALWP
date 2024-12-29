<?php

/**
 * My Popup
 *
 * @wordpress-plugin
 * Plugin Name:       My Popup
 * Plugin URI:        https://wpshop.ru/plugins/my-popup
 * Description:       Fully customizable popup plugin
 * Version:           2.1.1
 * Author:            WPShop.ru
 * Author URI:        https://wpshop.ru/
 * License:           WPShop License
 * License URI:       https://wpshop.ru/license
 * Text Domain:       my-popup
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\MetaBox\MetaBoxManager;
use Wpshop\PluginMyPopup\AdminMenu;
use Wpshop\PluginMyPopup\DefaultHooks;
use Wpshop\PluginMyPopup\FormHandler;
use Wpshop\PluginMyPopup\MyPopup;
use Wpshop\PluginMyPopup\MyPopupPreview;
use Wpshop\PluginMyPopup\Plugin;
use Wpshop\PluginMyPopup\PopupPresets;
use Wpshop\PluginMyPopup\PostGrid;
use Wpshop\PluginMyPopup\Rule\RuleAutocomplete;
use Wpshop\PluginMyPopup\Shortcodes;
use Wpshop\PluginMyPopup\Support\ContactForm7Support;
use Wpshop\PluginMyPopup\Support\WpReelSupport;
use Wpshop\SettingApi\SettingsManager;
use function Wpshop\PluginMyPopup\container;
use function Wpshop\PluginMyPopup\get_settings_page_url;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/legacy-support.php';

const MY_POPUP_FILE       = __FILE__;
const MY_POPUP_SLUG       = 'my-popup';
const MY_POPUP_TEXTDOMAIN = 'my-popup';
define( 'MY_POPUP_BASENAME', plugin_basename( MY_POPUP_FILE ) );

container()->get( Plugin::class )->init( __FILE__ );
container()->get( AdminMenu::class )->init();

//if ( container()->has( SettingsManager::class ) ) {
//    container()->get( SettingsManager::class )->init();
//}

container()->get( PopupPresets::class )->init( __FILE__ ); // should be before Plugin::init
container()->get( MyPopupPreview::class )->init();
container()->get( MetaBoxManager::class )->init();
container()->get( RuleAutocomplete::class )->init();
container()->get( Shortcodes::class )->init();
add_action( 'plugins_loaded', function () {
    container()->get( MyPopup::class )->init();
    container()->get( DefaultHooks::class )->init();
    container()->get( PostGrid::class )->init();
    container()->get( ContactForm7Support::class )->init();
    container()->get( WpReelSupport::class )->init();
    container()->get( FormHandler::class )->init();
} );

register_activation_hook( __FILE__, 'Wpshop\PluginMyPopup\activate' );
register_deactivation_hook( __FILE__, 'Wpshop\PluginMyPopup\deactivate' );
register_uninstall_hook( __FILE__, 'Wpshop\PluginMyPopup\uninstall' );

add_action( 'activated_plugin', function ( $plugin ) {
    if ( $plugin == MY_POPUP_BASENAME ) {
        wp_redirect( get_settings_page_url() );
        die;
    }
} );
