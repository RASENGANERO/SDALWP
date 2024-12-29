<?php

namespace Wpshop\Quizle;

use Wpshop\Quizle\Admin\Settings;

class AssetsProvider {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, '_admin_enqueue_scripts' ] );
    }

    /**
     * @return void
     */
    public function _enqueue_scripts() {
        if ( ! $this->settings->verify() ) {
            return;
        }

        $version       = QUIZLE_VERSION;
        $goodshare_ver = '1.0';

        wp_enqueue_style( 'quizle-style', plugin_dir_url( QUIZLE_FILE ) . 'assets/public/css/style.min.css', [], $version );
        wp_style_add_data( 'quizle-style', 'rtl', 'replace' );

        $script_deps = (array) apply_filters( 'quizle/assets/script_deps', [ 'jquery' ] );
        wp_enqueue_script( 'quizle-scripts', plugin_dir_url( QUIZLE_FILE ) . 'assets/public/js/scripts.min.js', $script_deps, $version, true );

        // @todo move to ajax component
        wp_localize_script( 'quizle-scripts', 'quizle_script_params',
            [
                'url'           => admin_url( 'admin-ajax.php' ),
                'nonce'         => wp_create_nonce( 'quizle-nonce' ),
                'goodshare_url' => plugin_dir_url( QUIZLE_FILE ) . "assets/public/js/plugins/goodshare.min.js?v={$goodshare_ver}",
            ]
        );

        if ( is_quizle_result_page() ) {
            wp_enqueue_script( 'quizle-goodshare-plugin', plugin_dir_url( QUIZLE_FILE ) . 'assets/public/js/plugins/goodshare.min.js', [], $goodshare_ver );
        }
    }

    /**
     * @return void
     */
    public function _admin_enqueue_scripts() {
        if ( ! get_current_screen() ) {
            return;
        }

        if ( in_array( get_current_screen()->id, [
            'quizle',
            'quizle_page_quizle-results',
            'toplevel_page_quizle-settings',
            'quizle_page_analytics',
        ] ) ) {
            if ( ! did_action( 'wp_enqueue_media' ) ) {
                wp_enqueue_media();
            }
            wp_enqueue_style( 'quizle-style', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/css/style.min.css', [
                'wp-color-picker',
            ], QUIZLE_VERSION );
            wp_enqueue_script( 'quizle-scripts', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/js/scripts.min.js', [
                'jquery',
                'wp-color-picker',
            ], QUIZLE_VERSION, true );
            wp_localize_script( 'quizle-scripts', 'quizle_i18n', [
                'requireResultsOrContacts' => __( 'You should to set the results or contacts for quiz.', QUIZLE_TEXTDOMAIN ),
            ] );
            wp_localize_script( 'quizle-scripts', 'quizleScriptOptions', [
                'videoIcon' => includes_url( 'images/media/video.png' ),
            ] );
        }

        if ( in_array( get_current_screen()->id, [
            'quizle_page_quizle-settings',
            'toplevel_page_quizle-settings',
        ] ) ) {
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_style( 'wp-color-picker' );

            wp_enqueue_style( 'quizle-settings', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/css/settings.min.css', [
                'wp-color-picker',
            ], QUIZLE_VERSION );
            wp_enqueue_script( 'quizle-settings', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/js/settings.min.js', [
                'jquery',
            ], QUIZLE_VERSION, true );
            wp_localize_script( 'quizle-settings', 'wpshop_settings_globals', [
                'storage_key' => 'quizle-settings-tab',
            ] );
        }

        wp_register_script( 'echarts', 'https://cdn.jsdelivr.net/npm/echarts@5.4.2/dist/echarts.min.js', [], false, true );

        if ( get_current_screen()->id === 'quizle_page_analytics' ) {
            $use_echarts_cdn = true;
            wp_enqueue_script(
                'quizle-analytics',
                plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/js/analytics' . ( $use_echarts_cdn ? '-cdn' : '' ) . '.min.js',
                $use_echarts_cdn ? [ 'echarts' ] : [],
                QUIZLE_VERSION,
                true
            );
        }

        if ( 'edit-quizle' == get_current_screen()->id ) {
            wp_enqueue_style( 'quizle-grid-styles', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/css/grid.min.css', [], QUIZLE_VERSION );
            wp_enqueue_script(
                'quizle-grid',
                plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/js/quizle-grid.min.js',
                [ 'jquery' ],
                QUIZLE_VERSION,
                true
            );
        }
    }
}
