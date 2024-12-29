<?php

namespace Wpshop\PluginMyPopup;

class FormHandler {

    /**
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, '_handle_request' ] );
        add_filter( 'my_popup:send_mail', [ $this, '_send_mail' ], 10, 2 );
    }

    /**
     * @return void
     */
    public function _handle_request() {
        if ( 'POST' !== strtoupper( ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
            return;
        }

        $data = wp_parse_args( $_POST, [
            'name'                 => null,
            'email'                => null,
            'phone'                => null,
            'my_popup_form_action' => '',
            '_wpnonce'             => '',
        ] );

        if ( 'my_popup_form_action' !== $data['my_popup_form_action'] ) {
            return;
        }

        if ( ! wp_verify_nonce( $data['_wpnonce'], 'my_popup_form' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', MY_POPUP_TEXTDOMAIN ) ] );
        }

        $data = array_filter( $data, function ( $val, $key ) {
            return null !== $val &&
                   ! in_array( $key, [ 'my_popup_form_action', '_wpnonce', '_wp_http_referer' ] );
        }, ARRAY_FILTER_USE_BOTH );

        if ( apply_filters( 'my_popup:send_mail', true, $data ) ) {
            wp_send_json_success( [
                'message' => apply_filters( 'my_popup:send_mail_success_message', __( 'Great!', MY_POPUP_TEXTDOMAIN ) ),
            ] );
        }

        wp_send_json_error( [ 'message' => __( 'Unable to handle request', MY_POPUP_TEXTDOMAIN ) ] );
    }

    /**
     * @param bool  $result
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function _send_mail( $result, $data ) {
        $message = apply_filters( 'my_popup:mail_message', ob_get_content( function () use ( $data ) {
            ?>
            <h1><?php esc_html_e( 'New data of MyPopup form:', MY_POPUP_TEXTDOMAIN ); ?></h1>
            <?php foreach ( $data as $key => $value ): ?>
                <p><?php esc_html_e( $key ); ?>: <?php esc_html_e( $value ); ?></p>
            <?php endforeach;
        } ) );

        $mail_to_list = wp_parse_list( apply_filters( 'my_popup:mail_to_list', get_option( 'admin_email' ) ) );
        $mail_to_list = array_map( 'trim', $mail_to_list );

        return wp_mail(
            $mail_to_list,
            apply_filters( 'my_popup:mail_subject', __( 'New Request from My Popup Form', MY_POPUP_TEXTDOMAIN ) ),
            $message,
            [ 'content-type: text/html' ]
        );
    }
}
