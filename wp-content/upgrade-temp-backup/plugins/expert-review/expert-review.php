<?php

/**
 * Expert Review
 *
 * @wordpress-plugin
 * Plugin Name:       Expert Review
 * Plugin URI:        http://wpshop.biz/plugins/expert-review
 * Description:       Plugin helps to create expert content on your site.
 * Version:           1.8.0
 * Author:            WPShop.ru
 * Author URI:        https://wpshop.ru/
 * License:           WPShop License
 * License URI:       https://wpshop.ru/license
 * Text Domain:       expert-review
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\AdminMenu;
use Wpshop\ExpertReview\CustomStyle;
use Wpshop\ExpertReview\ExpertReview;
use Wpshop\ExpertReview\Likes;
use Wpshop\ExpertReview\McePluginHelper;
use Wpshop\ExpertReview\Plugin;
use Wpshop\ExpertReview\PluginContainer;
use Wpshop\ExpertReview\Preset;
use Wpshop\ExpertReview\Question;
use Wpshop\ExpertReview\Shortcodes;
use Wpshop\ExpertReview\Support\AmpSupport;
use Wpshop\ExpertReview\Support\SimpleAuthorBoxSupport;
use Wpshop\ExpertReview\Support\YTurboSupport;
use Wpshop\MetaBox\MetaBoxManager;
use Wpshop\SettingApi\SettingsManager;

const EXPERT_REVIEW_VERSION = '1.8.0';
define( 'EXPERT_REVIEW_FILE', __FILE__ );

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

PluginContainer::get( McePluginHelper::class )->init();
PluginContainer::get( Plugin::class )->init( __FILE__ );
PluginContainer::get( AdminMenu::class )->init( __FILE__ );
PluginContainer::get( Likes::class )->init();

if ( PluginContainer::has( SettingsManager::class ) ) {
    PluginContainer::get( SettingsManager::class )->init();
}
if ( PluginContainer::has( MetaBoxManager::class ) ) {
    PluginContainer::get( MetaBoxManager::class )->init();
}

PluginContainer::get( ExpertReview::class )->init();
PluginContainer::get( Question::class )->init();
PluginContainer::get( Shortcodes::class )->init();
PluginContainer::get( Preset::class )->init();
PluginContainer::get( Shortcodes\Poll::class )->init();
//PluginContainer::get( YTurboSupport::class )->init();
PluginContainer::get( SimpleAuthorBoxSupport::class )->init();
PluginContainer::get( AmpSupport::class )->init();
PluginContainer::get( CustomStyle::class )->init();

register_activation_hook( __FILE__, 'expert_review_activate' );
register_deactivation_hook( __FILE__, 'expert_review_deactivate' );
register_uninstall_hook( __FILE__, 'expert_review_uninstall' );
