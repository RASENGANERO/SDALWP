<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\Container\ServiceRegistry;
use Wpshop\Quizle\Admin\QuizleResultActions;
use Wpshop\Quizle\Admin\QuizlePostGrid;
use Wpshop\Quizle\Admin\QuizleResultGrid;
use Wpshop\Quizle\Admin\ResultListTable;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Admin\Templates;
use Wpshop\Quizle\Analytics;
use Wpshop\Quizle\AssetsProvider;
use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\Db\Upgrade;
use Wpshop\Quizle\DefaultHooks;
use Wpshop\Quizle\Encryption;
use Wpshop\Quizle\Admin\ImportExport;
use Wpshop\Quizle\FileUpload;
use Wpshop\Quizle\Integration\AmoCRM;
use Wpshop\Quizle\Integration\Bitrix24;
use Wpshop\Quizle\Integration\ReCaptcha;
use Wpshop\Quizle\Integration\Telegram;
use Wpshop\Quizle\Integration\YandexMetrika;
use Wpshop\Quizle\Logger;
use Wpshop\Quizle\Admin\MenuPage;
use Wpshop\Quizle\Admin\MetaBoxes;
use Wpshop\Quizle\MailService;
use Wpshop\Quizle\QuizleResultExport;
use Wpshop\Quizle\QuizleResultHandler;
use Wpshop\Quizle\RestAPI;
use Wpshop\Quizle\Shortcodes;
use Wpshop\Quizle\Quizle;
use Wpshop\Quizle\Social;
use Wpshop\Quizle\Support\SeoSupport;
use Wpshop\Settings\Maintenance;
use Wpshop\Settings\MaintenanceInterface;

return function ( $config ) {
    $container = new ServiceRegistry( [
        'config'                    => $config,
        Analytics::class            => function ( $c ) {
            return new Analytics( $c[ Database::class ] );
        },
        AmoCRM::class               => function ( $c ) {
            return new AmoCRM(
                $c[ Settings::class ],
                new Logger(
                    null,
                    'quizle-amorcm.log'
                )
            );
        },
        AssetsProvider::class       => function ( $c ) {
            return new AssetsProvider( $c[ Settings::class ] );
        },
        Bitrix24::class             => function ( $c ) {
            return new Bitrix24( $c[ Settings::class ] );
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
        FileUpload::class           => function ( $c ) {
            return new FileUpload( $c[ Settings::class ] );
        },
        ImportExport::class         => function () {
            return new ImportExport();
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
        QuizleResultExport::class   => function ( $c ) {
            return new QuizleResultExport(
                $c[ Settings::class ],
                $c[ Database::class ]
            );
        },
        QuizleResultGrid::class     => function ( $c ) {
            return new QuizleResultGrid( $c['config']['quizle_grid_screen_id'] );
        },
        QuizleResultHandler::class  => function ( $c ) {
            return new QuizleResultHandler(
                $c[ Database::class ],
                $c[ MailService::class ],
                $c[ ReCaptcha::class ]
            );
        },
        ReCaptcha::class            => function ( $c ) {
            return new ReCaptcha( $c[ Settings::class ] );
        },
        RestAPI::class              => function ( $c ) {
            return new RestAPI( $c[ Database::class ] );
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
        Telegram::class             => function ( $c ) {
            return new Telegram( $c[ Settings::class ] );
        },
        Templates::class            => function ( $c ) {
            return new Templates(
                $c[ Settings::class ],
                $c['config']['templates_api']
            );
        },
        Upgrade::class              => function ( $c ) {
            global $wpdb;

            return new Upgrade(
                $wpdb,
                $c[ Database::class ]
            );
        },
        YandexMetrika::class        => function ( $c ) {
            return new YandexMetrika( $c[ Settings::class ] );
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
