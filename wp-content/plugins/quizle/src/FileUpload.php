<?php

namespace Wpshop\Quizle;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use Wpshop\Quizle\Admin\Settings;

class FileUpload {

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
        $action = 'quizle_file_upload';
        add_action( "wp_ajax_{$action}", [ $this, '_upload_file' ] );
        add_action( "wp_ajax_nopriv_{$action}", [ $this, '_upload_file' ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _upload_file() {
//        if ( empty( $_FILES['quizle_files']['tmp_name'] ) ) {
//            wp_send_json_error( new WP_Error( 'quizle_file_upload', __( 'Unable to upload file', QUIZLE_TEXTDOMAIN ) ) );
//        }

        if ( ! $this->settings->get_value( 'file_upload.allow_guest' ) && ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'quizle_file_upload', __( 'You are not allowed to upload files', QUIZLE_TEXTDOMAIN ) ) );
        }

        if ( empty( $_POST['quizle'] ) ) {
            wp_send_json_error( new WP_Error( 'quizle_file_upload', __( 'Unable to upload file', QUIZLE_TEXTDOMAIN ) ) );
        }
        if ( ! ( $quizle = get_quizle( $_POST['quizle'] ) ) ) {
            wp_send_json_error( new WP_Error( 'quizle_file_upload', __( 'Quizle not found for the file uploading', QUIZLE_TEXTDOMAIN ) ) );
        }

        $handler = new FileUploadHandler( 'quizle_files', 'quizle_file_upload' );
        $result  = $handler->handle( $quizle->ID, [], [
            'test_form' => false,
            'test_type' => true,
            'mimes'     => $this->get_mime_types_from_settings(),
        ] );


        wp_send_json_success( $result );
    }


    /**
     * Transforms the accept attribute value of the input field into an array of Wordpress mime types
     *
     * @return array
     *
     * @see get_allowed_mime_types()
     * @see wp_get_mime_types()
     * @see wp_check_filetype()
     */
    protected function get_mime_types_from_settings() {

        // expected comma separated values https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
        $accept = $this->settings->get_value( 'file_upload.accept' );
        $accept = explode( ',', $accept );
        $accept = array_map( 'trim', $accept );

        $result = [];
        foreach ( $accept as $accept_item ) {
            $parts = explode( '/', $accept_item );

            // in this case .png or something similar is expected
            if ( count( $parts ) != 2 ) {
                // if acc
                if ( substr( $parts[0], 0, 1 ) === '.' ) {
                    $part_0 = substr( $parts[0], 1 );
                    foreach ( get_allowed_mime_types() as $format => $mime_type ) {
                        $format_parts = explode( '|', $format );
                        foreach ( $format_parts as $format_part ) {
                            if ( $part_0 === $format_part ) {
                                $result[ $format ] = $mime_type;
                                continue 3;
                            }
                        }
                    }
                }
            }

            // in case image/* or something similar given
            if ( $parts[1] === '*' ) {
                foreach ( get_allowed_mime_types() as $format => $mime_type ) {
                    if ( str_starts_with( $mime_type, $parts[0] . '/' ) ) {
                        $result[ $format ] = $mime_type;
                        //continue 2;
                    }
                }
            }

            // in case image/jpg or something similar given
            foreach ( get_allowed_mime_types() as $format => $mime_type ) {
                if ( $mime_type === $accept_item ) {
                    $result[ $format ] = $mime_type;
                    continue 2;
                }
            }

//            if ( $parts[1] === 'jpeg' ) {
//                $result['jpg|jpeg|jpe'] = $accept_item;
//            } else {
//                $result[ $parts[1] ] = $accept_item;
//            }
        }

        return $result;
    }
}
