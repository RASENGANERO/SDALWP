<?php

namespace Wpshop\PluginMyPopup;

use Puc_v4_Factory;
use Wpshop\PluginMyPopup\Settings\PluginOptions;

class Plugin {

    const TEXT_DOMAIN = MY_POPUP_TEXTDOMAIN;

    public static $DEFAULTS = [
        'my_popup_enable'                    => true,
        'my_popup_position'                  => [
            'value'        => 'center_center',
            'width'        => '',
            'width_units'  => 'px',
            'height'       => '',
            'height_units' => 'px',
        ],
        'my_popup_cookies_type'              => [
            'is_enabled' => false,
            'value'      => '0',
            'mode'       => 'minutes',
        ],
        'my_popup_enable_overlay'            => [
            'is_enabled' => true,
        ],
        'my_popup_show_close_button'         => [
            'is_enabled' => true,
            'value'      => '0',
        ],
        'my_popup_display_desktop'           => [
            'is_enabled' => true,
        ],
        'my_popup_display_tablet'            => [
            'is_enabled' => true,
        ],
        'my_popup_background_color'          => [
            'color'   => '#ffffff',
            'opacity' => 0,
        ],
        'my_popup_appearance'                => [
            'padding'      => '20',
            'padding_unit' => 'px',
        ],
        'my_popup_background_image'          => [
            'position' => 'center_center',
            'repeat'   => 'no-repeat',
            'size'     => 'auto',
        ],
        'my_popup_bg_image_overlay'          => [
            'is_enabled' => false,
            'color'      => '#000000',
            'opacity'    => '50',
        ],
        'my_popup_border'                    => [
            'width'  => '0',
            'color'  => '#000000',
            'radius' => '0',
        ],
        'my_popup_shadow'                    => [
            'x'       => '0',
            'y'       => '0',
            'blur'    => '0',
            'spread'  => '0',
            'color'   => '#000000',
            'opacity' => '50',
        ],
        'my_popup_animation'                 => [
            'value' => 'fadeInDown',
        ],
        'my_popup_content_color'             => [
            'color' => '#333333',
        ],
        'my_popup_overlay'                   => [
            'color'   => '#000000',
            'opacity' => '50',
        ],
        'my_popup_icon'                      => [
            'image'       => '',
            'position'    => 'top-center',
            'width'       => '',
            'width_unit'  => 'px',
            'height'      => '',
            'height_unit' => 'px',
            'gap'         => '',
            'gap_unit'    => 'px',
        ],
        'my_popup_close_button_location'     => [
            'value'    => 'outside',
            'gap'      => '20',
            'gap_unit' => 'px',
        ],
        'my_popup_close_button_size'         => [
            'value' => '20',
        ],
        'my_popup_close_button_color'        => [
            'color'   => '#ffffff',
            'opacity' => '0',
        ],
        'my_popup_close_button_icon'         => [
            'value' => 'times-regular',
        ],
        'my_popup_social_buttons_width'      => [
            'value' => '35',
        ],
        'my_popup_social_buttons_height'     => [
            'value' => '35',
        ],
        'my_popup_social_buttons_align'      => [
            'value' => 'center',
        ],
        'my_popup_show_on_time'              => [
            'is_enabled' => true,
            'value'      => '0',
        ],
        'my_popup_social_buttons_indent'     => [
            'value' => '8',
        ],
        'my_popup_output_posts_title'        => [
            'value' => '',
        ],
        'my_popup_output_posts_count'        => [
            'value' => '3',
        ],
        'my_popup_output_posts_include'      => [
            'value' => '',
        ],
        'my_popup_output_posts_exclude'      => [
            'value' => '',
        ],
        'my_popup_output_categories_include' => [
            'value' => '',
        ],
        'my_popup_output_categories_exclude' => [
            'value' => '',
        ],
        'my_popup_output_posts_add_sorting'  => [
            'is_enabled' => true,
        ],
        'my_popup_output_posts_open_new_tab' => [
            'is_enabled' => false,
        ],
        'my_popup_output_post_show_thumb'    => [
            'is_enabled' => true,
        ],
        'my_popup_output_post_style'         => [
            'value' => '',
        ],
    ];

    /**
     * @var null|bool
     */
    protected $_verify;

    /**
     * @var string
     */
    public $name = 'my-popup';

    /**
     * @var string
     * @deprecated
     */
    public $version;

    /**
     * @var string
     */
    protected $plugin_file;

    /**
     * @var string
     */
    protected $verify_url;

    /**
     * @var string
     */
    protected $update_url;

    /**
     * @var string
     */
    protected $update_slug;

    /**
     * @var string
     */
    protected $update_check_period;

    /**
     * @var string
     */
    protected $update_option_name;

    /**
     * @var PluginOptions
     */
    protected $options;

    /**
     * Plugin constructor.
     *
     * @param array         $config
     * @param PluginOptions $options
     */
    public function __construct( array $config, PluginOptions $options ) {
        $this->configure( $config );
        $this->options = $options;
    }

    /**
     * @param array $cnf
     *
     * @return void
     */
    protected function configure( array $cnf ) {
        $update_cnf = $cnf['update'];

        $this->verify_url          = $cnf['verify_url'];
        $this->update_url          = $update_cnf['url'];
        $this->update_slug         = $update_cnf['slug'];
        $this->update_check_period = $update_cnf['check_period'];
        $this->update_option_name  = $update_cnf['opt_name'];
    }

    /**
     * @param string $plugin_file
     *
     * @return bool
     */
    public function init( $plugin_file ) {
        $this->plugin_file = $plugin_file;

        $this->load_languages();
        $this->init_metadata();
        add_action( 'admin_notices', function () {
            $this->license_notice();
        } );
        $this->enqueue_resources( 20220914 );
        $this->init_updates();

        add_action( 'init', [ $this, 'handle_activation' ] );

        return true;
    }

    /**
     * @return void
     */
    protected function load_languages() {
        load_plugin_textdomain( static::TEXT_DOMAIN, false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' );
    }

    /**
     * @param string $version
     *
     * @return void
     */
    protected function enqueue_resources( $version ) {
        add_action( 'admin_enqueue_scripts', function () use ( $version ) {
            wp_enqueue_style( 'my-popup-style', plugins_url() . '/my-popup/assets/admin/css/style.min.css', [], $version );
            wp_enqueue_script( 'my-popup-scripts', plugins_url() . '/my-popup/assets/admin/js/scripts.min.js', [ 'jquery' ], $version, true );

//            wp_enqueue_script( 'jquery-ui-resizable' );

            do_action( 'my_popup:admin_enqueue_scripts' );
        } );
    }

    /**
     * @return void
     * @deprecated
     */
    protected function init_metadata() {
//		add_action( 'plugins_loaded', function () {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $data          = get_plugin_data( $this->plugin_file, false, false );
        $this->version = $data['Version'];
//		} );
    }

    /**
     * @return array
     */
    protected function get_metadata() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        return get_plugin_data( $this->plugin_file, false, false );
    }

    /**
     * @return void
     */
    public function init_updates() {
        if ( ! $this->verify() ) {
            return;
        }
        Puc_v4_Factory::buildUpdateChecker(
            $this->update_url,
            $this->plugin_file,
            $this->update_slug,
            $this->update_check_period,
            $this->update_option_name
        )->addQueryArgFilter( function ( $queryArgs ) {
            if ( $licence = $this->options->license ) {
                $queryArgs['license_key'] = $licence;
            }

            return $queryArgs;
        } );
    }

    /**
     * @return void
     */
    protected function license_notice() {
        if ( ! $this->verify() ) {
            echo '<div class="notice notice-error">';
            echo '<h2>' . __( 'Attention!', static::TEXT_DOMAIN ) . '</h2>';
            echo '<p>' . sprintf( __( 'To activate plugin you need to enter the license key on <a href="%s">this page</a>.', static::TEXT_DOMAIN ), get_settings_page_url() ) . '</p>';
            echo '</div>';
        }
    }

    /**
     * @return bool
     */
    public function verify() {
        if ( null === $this->_verify ) {
            $license        = $this->options->license;
            $license_verify = $this->options->license_verify;
            $license_error  = $this->options->license_error;

            if ( ! empty( $license ) && ! empty( $license_verify ) && empty( $license_error ) ) {
                $this->_verify = true;
            } else {
                $this->_verify = false;
            }
        }

        return $this->_verify;
    }

    /**
     * @param string $license
     *
     * @return bool|\WP_Error
     */
    public function activate( $license ) {
        $url = trim( $this->verify_url );

        if ( ! $url ) {
            $this->options->license_verify = '';
            $this->options->license_error  = __( 'Unable to check license without activation url', static::TEXT_DOMAIN );

            return new \WP_Error( 'activation_failed', __( 'Unable to check license without activation url', static::TEXT_DOMAIN ) );
        }

        $args = [
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => [
                'action'    => 'activate_license',
                'license'   => $license,
                'item_name' => $this->name,
                'version'   => $this->get_metadata()['Version'],
                'type'      => 'plugin',
                'url'       => home_url(),
                'ip'        => Utilities::get_ip(),
            ],
        ];

        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            $response = wp_remote_post( str_replace( "https", "http", $url ), $args );
        }

        if ( is_wp_error( $response ) ) {
            $this->options->license_verify = '';
            $this->options->license_error  = __( 'Can\'t get response from license server', $this->options->text_domain );

            return new \WP_Error( 'activation_failed', __( 'Can\'t get response from license server', static::TEXT_DOMAIN ) );
        }

        $body = wp_remote_retrieve_body( $response );

        if ( mb_substr( $body, 0, 2 ) == 'ok' ) {
            $this->options->license        = $license;
            $this->options->license_verify = time() + ( WEEK_IN_SECONDS * 4 );
            $this->options->license_error  = '';

            return true;
        }

        $this->options->license_verify = '';
        $this->options->license_error  = $body;

        return new \WP_Error( 'activation_failed', __( 'Unable to check license without activation url', static::TEXT_DOMAIN ) );
    }

    /**
     * @return void
     */
    public function handle_activation() {
        if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) !== 'POST' ) {
            return;
        }

        if ( is_admin() &&
             isset( $_POST['my_popup_nonce'] ) && wp_verify_nonce( $_POST['my_popup_nonce'], 'my-popup-activate' )
        ) {
            $this->activate( isset( $_POST['license'] ) ? $_POST['license'] : '' );
            wp_redirect( get_settings_page_url(), 303 );
            die;
        }
    }
}
