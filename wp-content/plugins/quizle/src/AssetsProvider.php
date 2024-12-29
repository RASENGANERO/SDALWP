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


        $scrip_params = [
            'url'           => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'quizle-nonce' ),
            'goodshare_url' => plugin_dir_url( QUIZLE_FILE ) . "assets/public/js/plugins/goodshare.min.js?v={$goodshare_ver}",
            'i18n'          => [
                'wrongPhoneMessage' => __( 'Please enter your correct phone number', 'quizle' ),
            ],
        ];

        if ( $this->settings->get_value( 'enable_phone_mask' ) ) {
            wp_enqueue_style( 'quizle-intl-tel-input-style', plugin_dir_url( QUIZLE_FILE ) . 'assets/public/css/plugins/intl-tel-input.min.css', [], $version );
            wp_enqueue_script( 'quizle-intl-tel-input', plugin_dir_url( QUIZLE_FILE ) . 'assets/public/js/plugins/intl-tel/intlTelInput.js', $script_deps, $version, true );

            /**
             * Filter that allows to change js onlyCountries option of window.intlTelInput()
             *
             * @since 1.2.0
             */
            $only_countries = apply_filters( 'quizle/intl_tel_input/only_countries', [] );

            /**
             * Filter that allows to change js preferredCountries option of window.intlTelInput()
             *
             * @since 1.2.0
             */
            $preferred_countries = apply_filters( 'quizle/intl_tel_input/preferred_countries', [ 'ru', 'ua' ] );

            /**
             * Filter that allows to change js localizedCountries option of window.intlTelInput()
             *
             * @since 1.2.0
             */
            $localized_countries = apply_filters( 'quizle/intl_tel_input/localized_countries', [
                'ru' => __( 'Russia', QUIZLE_TEXTDOMAIN ),
                'ua' => __( 'Ukraine', QUIZLE_TEXTDOMAIN ),
            ] );

            $scrip_params['intlTelInput'] = [
                'localizedCountries' => $localized_countries,
                'onlyCountries'      => $only_countries,
                'preferredCountries' => $preferred_countries,
                'utilsScriptSrc'     => plugin_dir_url( QUIZLE_FILE ) . 'assets/public/js/plugins/intl-tel/utils.js',
            ];
        }

        $scrip_params['files'] = [
            'allowed'         => is_file_upload_allowed(),
            'limit'           => $this->settings->get_value( 'file_upload.limit' ),
            'accept'          => $this->settings->get_value( 'file_upload.accept' ),
            'reset_on_reload' => $this->settings->get_value( 'file_upload.reset_on_reload' ),
            'messages'        => [
                'maxFilesExceed' => __( 'You have chosen too many files.', 'quizle' ) . ' ' . _n(
                        'A maximum of %d file is allowed',
                        'A maximum of %в files is allowed',
                        intval( $this->settings->get_value( 'file_upload.limit' ) ),
                        'quizle'
                    ),
            ],
        ];

        if ( $this->settings->get_value( 'grecaptcha.enabled' ) ) {
            wp_enqueue_script(
                'quizle-g-recaptcha',
                'https://www.google.com/recaptcha/api.js?render=' . $this->settings->get_value( 'grecaptcha.site_key' ),
                [ 'quizle-scripts' ]
            );
            $scrip_params['gr_enabled'] = 1;

//            $js = <<<'JS'
//var quizleSubmitCallback = function (token) {
//};
//JS;
//
//            wp_add_inline_script( 'quizle-g-recaptcha', $js, 'before' );
        }

        wp_localize_script( 'quizle-scripts', 'quizle_script_params', $scrip_params );

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
            wp_enqueue_editor();

            if ( ! did_action( 'wp_enqueue_media' ) ) {
                wp_enqueue_media();
            }
            wp_enqueue_style( 'quizle-style', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/css/style.min.css', [
                'wp-color-picker',
            ], QUIZLE_VERSION );
            wp_enqueue_script( 'quizle-scripts', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/js/scripts.min.js', [
                'jquery',
                'wp-color-picker',
                'wp-util',
                'editor',
            ], QUIZLE_VERSION, true );
            wp_localize_script( 'quizle-scripts', 'quizle_i18n', [
                'requireResultsOrContacts' => __( 'You should to set the results or contacts for quiz.', QUIZLE_TEXTDOMAIN ),
            ] );
            wp_localize_script( 'quizle-scripts', 'quizleScriptOptions', [
                'videoIcon' => includes_url( 'images/media/video.png' ),
            ] );

            wp_localize_script( 'quizle-scripts', 'quizle_metabox_globals', [
                'enable_mce' => (bool) $this->settings->get_value( 'enable_wp_editor' ),
                'clone_text' => __( '(copy)', 'quizle' ),
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
            wp_localize_script( 'quizle-settings', 'quizle_settings_globals', [
                'storage_key' => 'quizle-settings-tab',
                'actions'     => Settings::ajax_actions(),
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

        if ( 'quizle_page_quizle-templates' == get_current_screen()->id ) {
            wp_enqueue_style( 'quizle-settings', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/css/settings.min.css', [
                'wp-color-picker',
            ], QUIZLE_VERSION );
            wp_enqueue_style( 'quizle-template-styles', plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/css/templates.min.css', [], QUIZLE_VERSION );
            wp_enqueue_script(
                'quizle-templates',
                plugin_dir_url( QUIZLE_FILE ) . 'assets/admin/js/templates.min.js',
                [ 'jquery', 'wp-util' ],
                QUIZLE_VERSION,
                true
            );

            wp_localize_script( 'quizle-templates', 'quizle_template_options', [
                'nonce'    => wp_create_nonce( 'quizle-templates' ),
                'endpoint' => add_query_arg( [
                    'action' => 'quizle_proxy_api',
                ], admin_url( '/' ) ),
            ] );
        }
    }
}
