<?php

/**
 * Expert Review
 *
 * @wordpress-plugin
 * Plugin Name:       Expert Review
 * Plugin URI:        http://wpshop.biz/plugins/expert-review
 * Description:       Plugin helps to create expert content on your site.
 * Version:           1.8.1
 * Author:            WPShop.ru
 * Author URI:        https://wpshop.ru/
 * License:           WPShop License
 * License URI:       https://wpshop.ru/license
 * Tested up to:      6.6
 * Requires PHP:      7.2
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
use Wpshop\ExpertReview\Preset;
use Wpshop\ExpertReview\Question;
use Wpshop\ExpertReview\Shortcodes;
use Wpshop\ExpertReview\Support\AmpSupport;
use Wpshop\ExpertReview\Support\SimpleAuthorBoxSupport;
use Wpshop\ExpertReview\Support\YTurboSupport;
use Wpshop\MetaBox\MetaBoxManager;
use Wpshop\SettingApi\SettingsManager;
use function Wpshop\ExpertReview\container;

const EXPERT_REVIEW_VERSION = '1.8.1';
define( 'EXPERT_REVIEW_FILE', __FILE__ );

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

container()->get( McePluginHelper::class )->init();
container()->get( Plugin::class )->init( __FILE__ );
container()->get( AdminMenu::class )->init( __FILE__ );
container()->get( Likes::class )->init();

if ( container()->has( SettingsManager::class ) ) {
    container()->get( SettingsManager::class )->init();
}
if ( container()->has( MetaBoxManager::class ) ) {
    container()->get( MetaBoxManager::class )->init();
}

container()->get( ExpertReview::class )->init();
container()->get( Question::class )->init();
container()->get( Shortcodes::class )->init();
container()->get( Preset::class )->init();
container()->get( Shortcodes\Poll::class )->init();
//container()->get( YTurboSupport::class )->init();
container()->get( SimpleAuthorBoxSupport::class )->init();
container()->get( AmpSupport::class )->init();
container()->get( CustomStyle::class )->init();

register_activation_hook( __FILE__, 'Wpshop\ExpertReview\activate' );
register_deactivation_hook( __FILE__, 'Wpshop\ExpertReview\deactivate' );
//register_uninstall_hook( __FILE__, 'Wpshop\ExpertReview\uninstall' );
