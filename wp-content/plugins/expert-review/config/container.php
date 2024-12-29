<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\Container\ServiceIterator;
use WPShop\Container\ServiceRegistry;
use Wpshop\ExpertReview\AdminMenu;
use Wpshop\ExpertReview\CustomStyle;
use Wpshop\ExpertReview\Likes;
use Wpshop\ExpertReview\McePluginHelper;
use Wpshop\ExpertReview\MetaBoxProvider;
use Wpshop\ExpertReview\MicroData;
use Wpshop\ExpertReview\Preset;
use Wpshop\ExpertReview\Question;
use Wpshop\ExpertReview\ExpertReview;
use Wpshop\ExpertReview\Plugin;
use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Settings\CustomStyleOptions;
use Wpshop\ExpertReview\Settings\ExpertOptions;
use Wpshop\ExpertReview\Settings\QaOptions;
use Wpshop\ExpertReview\Settings\LikeOptions;
use Wpshop\ExpertReview\Settings\PluginOptions;
use Wpshop\ExpertReview\SettingsProvider;
use Wpshop\ExpertReview\Shortcodes;
use Wpshop\ExpertReview\Support\AmpSupport;
use Wpshop\ExpertReview\Support\SimpleAuthorBoxSupport as SABSupport;
use Wpshop\ExpertReview\Support\YTurboSupport;
use Wpshop\MetaBox\Form\Render\ElementRenderer;
use Wpshop\MetaBox\Form\Render\LabelRenderer;
use Wpshop\MetaBox\MetaBoxContainer\SimpleMetaBoxContainer;
use Wpshop\MetaBox\MetaBoxManager;
use Wpshop\SettingApi\SettingsManager;

return function ( $config ) {
    global $wpdb;

    $container = new ServiceRegistry( [
        'config'                  => $config,
        Plugin::class             => function ( $c ) {
            return new Plugin( $c['config']['plugin_config'], $c[ PluginOptions::class ] );
        },
        ExpertReview::class       => function ( $c ) {
            return new ExpertReview(
                $c[ Plugin::class ],
                $c[ PluginOptions::class ]
            );
        },
        PluginOptions::class      => function () {
            return new PluginOptions();
        },
        ExpertOptions::class      => function () {
            return new ExpertOptions();
        },
        QaOptions::class          => function () {
            return new QaOptions();
        },
        LikeOptions::class        => function () {
            return new LikeOptions();
        },
        AdvancedOptions::class    => function () {
            return new AdvancedOptions();
        },
        CustomStyleOptions::class => function () {
            return new CustomStyleOptions();
        },
        SettingsProvider::class   => function ( $c ) {
            return new SettingsProvider(
                $c[ Plugin::class ],
                $c[ PluginOptions::class ],
                $c[ LikeOptions::class ],
                $c[ AdvancedOptions::class ],
                $c[ QaOptions::class ],
                $c[ CustomStyleOptions::class ]
            );
        },
        Shortcodes::class         => function ( $c ) {
            return new Shortcodes(
                $c[ Plugin::class ],
                $c[ MicroData::class ],
                $c[ ExpertOptions::class ],
                $c[ QaOptions::class ],
                $c[ AdvancedOptions::class ],
                $c['config']['icons']
            );
        },
        CustomStyle::class        => function ( $c ) {
            return new CustomStyle( $c[ CustomStyleOptions::class ] );
        },
        McePluginHelper::class    => function ( $c ) {
            return new McePluginHelper( $c['config']['icons'] );
        },
        Question::class           => function ( $c ) {
            return new Question( $c[ AdvancedOptions::class ] );
        },
        Likes::class              => function ( $c ) {
            return new Likes( $c[ LikeOptions::class ] );
        },
        MicroData::class          => function ( $c ) {
            return new MicroData( $c[ AdvancedOptions::class ] );
        },
        Preset::class             => function () use ( $wpdb ) {
            return new Preset( $wpdb );
        },
        Shortcodes\Poll::class    => function ( $c ) {
            return new Shortcodes\Poll( $c[ AdvancedOptions::class ] );
        },
        MetaBoxProvider::class    => function ( $c ) {
            return new MetaBoxProvider( $c[ SimpleMetaBoxContainer::class ] );
        },
        AdminMenu::class          => function () {
            return new AdminMenu();
        },
        YTurboSupport::class      => function () {
            return new YTurboSupport();
        },
        SABSupport::class         => function () {
            return new SABSupport();
        },
        AmpSupport::class         => function () {
            return new AmpSupport();
        },
    ] );

    _expert_review_register_setting_provider( $container );
    _expert_review_register_metabox_provider( $container );

    return $container;
};

function _expert_review_register_setting_provider( $c ) {
    if ( $c['config']['settings_providers'] && is_array( $c['config']['settings_providers'] ) ) {
        $providers = $c['config']['settings_providers'];
    }

    foreach ( $providers as $provider ) {
        if ( ! isset( $c[ $provider ] ) ) {
            $c[ $provider ] = function () use ( $provider ) {
                return new $provider;
            };
        }
    }

    $c[ SettingsManager::class ] = function ( $c ) use ( $providers ) {
        return new SettingsManager( new ServiceIterator( $c, $providers ) );
    };
}

function _expert_review_register_metabox_provider( $c ) {
    $c[ SimpleMetaBoxContainer::class ] = $c->factory( function ( $c ) {
        return new SimpleMetaBoxContainer(
            $c[ LabelRenderer::class ],
            $c[ ElementRenderer::class ]
        );
    } );

    $metaBoxProviders = [];
    if ( isset( $c['config']['metabox_providers'] ) && is_array( $c['config']['metabox_providers'] ) ) {
        $metaBoxProviders = $c['config']['metabox_providers'];
    }
    $c[ MetaBoxManager::class ] = function ( $c ) use ( $metaBoxProviders ) {
        return new MetaBoxManager( new ServiceIterator( $c, $metaBoxProviders ) );
    };

    $register_metabox_render_provider = include __DIR__ . '/metabox-render-provider.php';
    $register_metabox_render_provider( $c );
}
