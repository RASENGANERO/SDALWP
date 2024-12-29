<?php

namespace Wpshop\Quizle\Admin;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use Wpshop\Quizle\PluginContainer;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\generate_quizle_name;

class Templates {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var string
     */
    protected $template_api_url;

    /**
     * @param Settings $settings
     * @param string   $template_api_url
     */
    public function __construct( Settings $settings, $template_api_url ) {
        $this->settings         = $settings;
        $this->template_api_url = $template_api_url;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'quizle_create_from_template';
            add_action( "wp_ajax_{$action}", [ $this, '_create_from_template' ] );
        }

        $action = 'quizle_proxy_api';
        add_action( "admin_action_{$action}", [ $this, '_proxy_api_request' ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _create_from_template() {
        $data = wp_parse_args( $_POST, [
            'name'  => generate_quizle_name(),
            'meta'  => '',
            'title' => '',
        ] );
        $data = map_deep( $data, 'wp_unslash' );

        if ( empty( $data['meta'] ) ) {
            wp_send_json_error( new WP_Error( 'create_from_template', __( 'Unable to create new quizle without meta data', QUIZLE_TEXTDOMAIN ) ) );
        }

        $post_id = container()->get( ImportExport::class )->import( $data );

        $result = wp_remote_post( $this->template_api_url, [
            'headers' => [
                'X-License-Hash' => md5( $this->settings->get_reg_option()['license'] ),
            ],
            'body'    => [
                'action' => 'update_download_counter',
                'id'     => $_REQUEST['template_id'],
            ],
            'timeout' => 30,
        ] );

        $update_counter = false;
        if ( ! is_wp_error( $result ) ) {
            $body = wp_remote_retrieve_body( $result );
            $body = \Wpshop\Quizle\json_decode( $body );
            if ( $body && $body->success ) {
                $update_counter = true;
            }
        }

        wp_send_json_success( [
            'redirect_url'   => get_edit_post_link( $post_id, '' ),
            'update_counter' => $update_counter,
        ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _proxy_api_request() {

        /**
         * Allows to change quizle templates locale
         *
         * @since 1.2
         */
        $templates_locale = apply_filters( 'quizle/templates/locale', 'ru_RU' );

        $body = wp_parse_args( $_GET['query'] ?? '', [
            'action' => '',
            'locale' => $templates_locale,
        ] );

        $response = wp_remote_get( $this->template_api_url, [
            'headers' => [
                'X-License-Hash' => md5( $this->settings->get_reg_option()['license'] ),
            ],
            'body'    => $body,
        ] );
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response );
        }
        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            wp_send_json_error( new WP_Error( 'api_failed', __( 'Unable to retrieve templates from api server', QUIZLE_TEXTDOMAIN ) ) );
        }

        $body = wp_remote_retrieve_body( $response );

        header( 'Content-Type: application/json' );
        echo $body;
        die;
    }
}
