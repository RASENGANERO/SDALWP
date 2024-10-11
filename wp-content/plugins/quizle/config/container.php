<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\Quizle\Admin\QuizleResultActions;
use Wpshop\Quizle\Admin\QuizlePostGrid;
use Wpshop\Quizle\Admin\QuizleResultGrid;
use Wpshop\Quizle\Admin\ResultListTable;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Analytics;
use Wpshop\Quizle\AssetsProvider;
use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\Db\Upgrade;
use Wpshop\Quizle\DefaultHooks;
use Wpshop\Quizle\Encryption;
use Wpshop\Quizle\Logger;
use Wpshop\Quizle\Admin\MenuPage;
use Wpshop\Quizle\Admin\MetaBoxes;
use Wpshop\Quizle\MailService;
use Pimple\Container;
use Wpshop\Quizle\QuizleResultHandler;
use Wpshop\Quizle\Shortcodes;
use Wpshop\Quizle\Quizle;
use Wpshop\Quizle\Social;
use Wpshop\Quizle\Support\SeoSupport;
use Wpshop\Settings\Maintenance;
use Wpshop\Settings\MaintenanceInterface;

return function ( $config ) {
    $container = new Container( [
        'config'                    => $config,
        Analytics::class            => function ( $c ) {
            return new Analytics( $c[ Database::class ] );
        },
        AssetsProvider::class       => function ( $c ) {
            return new AssetsProvider( $c[ Settings::class ] );
        },
        Database::class             => function () {
            global $wpdb;

            return new Database( $wpdb );
        },
        DefaultHooks::class         => function ( $c ) {
            return new DefaultHooks( $c[ Settings::class ] );
        },
        Encryption::class           => function () {
            return new Encryption();
        },
        Logger::class               => function () {
            return new Logger( get_option( 'quizle-log-level', Logger::DISABLED ) );
        },
        MailService::class          => function ( $c ) {
            return new MailService( $c[ Logger::class ] );
        },
        MaintenanceInterface::class => function ( $c ) {
            return new Maintenance(
                $c['config']['plugin_config'],
                'plugin',
                QUIZLE_SLUG,
                QUIZLE_FILE,
                QUIZLE_TEXTDOMAIN
            );
        },
        MenuPage::class             => function ( $c ) {
            return new MenuPage( $c[ Settings::class ] );
        },
        Quizle::class               => function ( $c ) {
            return new Quizle(
                $c[ AssetsProvider::class ],
                $c[ Settings::class ]
            );
        },
        QuizleResultActions::class  => function ( $c ) {
            return new QuizleResultActions( $c[ Database::class ] );
        },
        QuizlePostGrid::class       => function () {
            return new QuizlePostGrid();
        },
        QuizleResultGrid::class     => function ( $c ) {
            return new QuizleResultGrid( $c['config']['quizle_grid_screen_id'] );
        },
        QuizleResultHandler::class  => function ( $c ) {
            return new QuizleResultHandler(
                $c[ Database::class ],
                $c[ MailService::class ]
            );
        },
        ResultListTable::class      => function ( $c ) {
            return new ResultListTable( [ 'screen' => $c['config']['quizle_grid_screen_id'] ] );
        },
        SeoSupport::class           => function () {
            return new SeoSupport();
        },
        Settings::class             => function ( $c ) {
            return new Settings(
                $c[ MaintenanceInterface::class ],
                'quizle-r',
                'quizle-settings'
            );
        },
        Shortcodes::class           => function ( $c ) {
            return new Shortcodes(
                $c[ Settings::class ],
                $c[ Encryption::class ],
                $c[ Social::class ]
            );
        },
        Social::class               => function () {
            return new Social();
        },
        Upgrade::class              => function ( $c ) {
            global $wpdb;

            return new Upgrade(
                $wpdb,
                $c[ Database::class ]
            );
        },
    ] );

    $container[ MetaBoxes::class ] = $container->factory( function ( $c ) {
        return new MetaBoxes(
            $c[ Settings::class ],
            $c[ Social::class ]
        );
    } );

    return $container;
};
