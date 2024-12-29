<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\Container\ServiceIterator;
use WPShop\Container\ServiceRegistry;
use Wpshop\MetaBox\Form\Render\ElementRenderer;
use Wpshop\MetaBox\Form\Render\LabelRenderer;
use Wpshop\MetaBox\Form\Render\RendererProvider;
use Wpshop\MetaBox\MetaBoxManager;
use Wpshop\MetaBox\MetaBoxManagerProvider;
use Wpshop\PluginMyPopup\AdminMenu;
use Wpshop\PluginMyPopup\DefaultHooks;
use Wpshop\PluginMyPopup\FormHandler;
use Wpshop\PluginMyPopup\Icons;
use Wpshop\PluginMyPopup\MetaBox\Container\TabbedMetaBoxContainer;
use Wpshop\PluginMyPopup\MetaBox\Render\ElementRendererProvider;
use Wpshop\PluginMyPopup\MyPopup;
use Wpshop\PluginMyPopup\Logger;
use Wpshop\PluginMyPopup\MyPopupPreview;
use Wpshop\PluginMyPopup\Plugin;
use Wpshop\PluginMyPopup\PopupPresets;
use Wpshop\PluginMyPopup\PostGrid;
use Wpshop\PluginMyPopup\Rule\RuleAutocomplete;
use Wpshop\PluginMyPopup\Rule\RuleValidation;
use Wpshop\PluginMyPopup\Settings\PluginOptions;
use Wpshop\PluginMyPopup\SettingsProvider;
use Wpshop\PluginMyPopup\Shortcodes;
use Wpshop\PluginMyPopup\Support\ContactForm7Support;
use Wpshop\PluginMyPopup\Support\WpReelSupport;
use Wpshop\SettingApi\SettingsManager;
use Wpshop\SettingApi\SettingsManagerProvider;
use Wpshop\PluginMyPopup\MetaBoxProvider;
use Wpshop\MetaBox\MetaBoxContainer\SimpleMetaBoxContainer;

return function ( $config ) {
    global $wpdb;

    $container = new ServiceRegistry( [
        'config'                   => $config,
        Plugin::class              => function ( $c ) {
            return new Plugin( $c['config']['plugin_config'], $c[ PluginOptions::class ] );
        },
        Icons::class               => function () {
            return new Icons();
        },
        Logger::class              => function ( $c ) {
            /** @var PluginOptions $options */
            $options = $c[ PluginOptions::class ];

            return new Logger( $options->error_log_level );
        },
        MetaBoxProvider::class     => function ( $c ) {
            return new MetaBoxProvider( $c[ SimpleMetaBoxContainer::class ], $c[ TabbedMetaBoxContainer::class ] );
        },
        MyPopup::class             => function ( $c ) {
            return new MyPopup(
                $c[ Plugin::class ],
                $c[ PluginOptions::class ],
                $c[ RuleValidation::class ],
                $c[ Icons::class ]
            );
        },
        MyPopupPreview::class      => function ( $c ) {
            return new MyPopupPreview( $c[ MyPopup::class ] );
        },
        PopupPresets::class        => function () {
            return new PopupPresets();
        },
        PostGrid::class            => function () {
            return new PostGrid();
        },
        PluginOptions::class       => function () {
            return new PluginOptions();
        },
        SettingsProvider::class    => function ( $c ) {
            return new SettingsProvider(
                $c[ Plugin::class ],
                $c[ PluginOptions::class ]
            );
        },
        RuleAutocomplete::class    => function () use ( $wpdb ) {
            return new RuleAutocomplete( $wpdb );
        },
        RuleValidation::class      => function () {
            return new RuleValidation();
        },
        Shortcodes::class          => function () {
            return new Shortcodes();
        },
        AdminMenu::class           => function () {
            return new AdminMenu();
        },
        DefaultHooks::class        => function () {
            return new DefaultHooks();
        },
        ContactForm7Support::class => function () {
            return new ContactForm7Support();
        },
        WpReelSupport::class       => function () {
            return new WpReelSupport();
        },
        FormHandler::class         => function () {
            return new FormHandler();
        },
    ] );

    $container[ TabbedMetaBoxContainer::class ] = $container->factory( function ( $c ) {
        return new TabbedMetaBoxContainer(
            $c[ Plugin::class ],
            $c[ LabelRenderer::class ],
            $c[ ElementRenderer::class ]
        );
    } );
    $container->register( new ElementRendererProvider() );

    _register_setting_provider( $container );
    _register_metabox_provider( $container );

    return $container;
};

function _register_setting_provider( $c ) {
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

function _register_metabox_provider( $c ) {
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
