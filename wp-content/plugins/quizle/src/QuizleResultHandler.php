<?php

namespace Wpshop\Quizle;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WP_Post;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\Integration\ReCaptcha;

class QuizleResultHandler {

    const ACTION_INIT_RESULT           = 'init_result';
    const ACTION_RETURN_RESULT_CONTENT = 'get_result_content';
    const ACTION_UPDATE_ANSWERS        = 'update_answers';
    const ACTION_UPDATE_CONTACTS       = 'update_contacts';
    const ACTION_FINISH                = 'update_finished';

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var MailService
     */
    protected $mail_service;

    /**
     * @var ReCaptcha
     */
    protected $captcha;

    /**
     * @param Database    $database
     * @param MailService $mail_service
     * @param ReCaptcha   $captcha
     */
    public function __construct(
        Database $database,
        MailService $mail_service,
        ReCaptcha $captcha
    ) {
        $this->database     = $database;
        $this->mail_service = $mail_service;
        $this->captcha      = $captcha;
    }

    /**
     * @return void
     */
    public function init() {
        //add_filter( 'quizle/result_handler/verify_nonce', [ $this, '_verify_nonce' ] );

        $action = 'quizle_save_result';
        add_action( "wp_ajax_nopriv_{$action}", [ $this, '_handle' ] );
        add_action( "wp_ajax_{$action}", [ $this, '_handle' ] );
    }

    /**
     * @return bool
     */
    public function _verify_nonce() {
        return container()->get( Settings::class )->get_value( 'verify_nonce' );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _handle() {

        /**
         * @since 1.3
         */
        $verify_nonce = apply_filters( 'quizle/result_handler/verify_nonce', false );
        if ( $verify_nonce && ! wp_verify_nonce( $_REQUEST['nonce'] ?? '', 'quizle-nonce' ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Unable to handle request', QUIZLE_TEXTDOMAIN ) ) );
        }

        $result  = new QuizleResult();
        $request = map_deep( $_REQUEST, 'wp_unslash' );
        $data    = $request['data'];

        $context = '';
        if ( ! empty( $data['context'] ) ) {
            if ( is_wp_error( $context = Context::createFromParams( $data['context'] ) ) ) {
                $context = '';
            }
        }
        $quizle = get_quizle(
            $data['quizle'] ?? null,
            $context && $context->is_preview ? 'any' : 'publish'
        );

        if ( ! $quizle ) {
            wp_send_json_error( new WP_Error( 'quizle_not_found', __( 'Unable to find quizle', QUIZLE_TEXTDOMAIN ) ) );
        }

        $to_return = [];
        if ( $this->do_action( self::ACTION_INIT_RESULT ) ) {
            $result->populate( [
                'quiz_id'     => $quizle->ID,
                'user_id'     => get_current_user_id(),
                'user_cookie' => $_COOKIE[ COOKIE_UID ] ?? ( $data['cookie'] ?? null ),
                'context'     => json_encode( $context ),
            ] );

            if ( ! ( $result = $this->database->insert_quizle_result( $result ) ) ) {
                wp_send_json_error( new WP_Error( 'db_error', __( 'Unable to insert quizle result', QUIZLE_TEXTDOMAIN ) ) );
            }

            $to_return['result'] = [
                'id'    => $result->result_id,
                'token' => $result->token,
                '_mac'  => $result->get_mac(),
            ];
        } else {
            $use_empty_result = $data['_use_empty_result'] ?? false;

            if ( $use_empty_result ) {
                $result = new QuizleResult();
                $result->populate( [
                    'quiz_id' => $quizle->ID,
                    'context' => json_encode( $context ),
                ] );
                $result->prepare_result_data( (array) json_decode( $data['answers'] ?? '[]', true ), $quizle->ID );
            } elseif ( ! ( $result = $this->database->get_quizle_result( $data['result']['id'] ?? null ) ) ) {
                wp_send_json_error( new WP_Error( 'result_not_found', __( 'Unable to retrieve result for update', QUIZLE_TEXTDOMAIN ) ) );
            }

            if ( ! ( $quizle = get_post( $result->quiz_id ) ) ) {
                wp_send_json_error( new WP_Error( 'quizle_not_found', __( 'Unable to update result without related quizle', QUIZLE_TEXTDOMAIN ) ) );
            }
            if ( ! $use_empty_result && ! $result->verify( $data['result']['_mac'] ?? '' ) ) {
                wp_send_json_error( new WP_Error( 'wrong_result', __( 'Unable to update invalid quizle result', QUIZLE_TEXTDOMAIN ) ) );
            }

            $to_return['result'] = [
                'id'    => $result->result_id,
                'token' => $result->token,
                '_mac'  => $result->get_mac(),
            ];

            $keys_to_update = [];

            if ( $this->do_action( self::ACTION_UPDATE_ANSWERS ) ) {
                $result->prepare_result_data( (array) json_decode( $data['answers'] ?? '[]', true ) );

                $keys_to_update[] = 'result_data';
            }

            if ( $this->do_action( self::ACTION_UPDATE_CONTACTS ) ) {

                if ( $this->captcha->enabled() && ! $this->captcha->verify( $data['gr-token'] ?? '' ) ) {
                    wp_send_json_error( new WP_Error(
                        'captcha_error',
                        __( 'Unable to handle contacts data: captcha validation failed', QUIZLE_TEXTDOMAIN )
                    ) );
                }

                $result->name  = $data['contacts']['name'] ?? '';
                $result->email = $data['contacts']['email'] ?? '';
                $result->phone = $data['contacts']['phone'] ?? '';

                if ( ! empty( $data['messengers'] ) ) {
                    $result->add_additional_data( 'messengers', $data['messengers'] );
                    $keys_to_update[] = 'additional_data';
                }

                $to_return['contacts_success_message'] = $this->get_contacts_success_message( $quizle );
                array_push( $keys_to_update, 'name', 'phone', 'email' );

                $to_return['contacts_redirect']         = get_post_meta( $quizle->ID, 'contact-redirect-link', true ) ?: null;
                $to_return['contacts_redirect_timeout'] = (int) get_post_meta( $quizle->ID, 'contact-redirect-timeout', true );
            }

            if ( $this->do_action( self::ACTION_FINISH ) ) {
                $result->set_finished_time();
                $keys_to_update[] = 'finished_at';
            }

            if ( $this->do_action( self::ACTION_RETURN_RESULT_CONTENT ) ) {
                [ $result_content, $redirect_link ] = $result->get_result_content();

                $to_return['result_content']  = $result_content;
                $to_return['result_redirect'] = $redirect_link;
            }

            if ( ! $use_empty_result ) {
                if ( ! $this->database->update_quizle_result( $result, $keys_to_update ) ) {
                    wp_send_json_error( new WP_Error( 'db_error', __( 'Unable to update quizle result', QUIZLE_TEXTDOMAIN ) ) );
                }

                /**
                 * Allows to hook up to a quizle result update
                 *
                 * @since 1.3
                 */
                do_action( 'quizle/result_handler/updated', $result, $this->get_actions(), $keys_to_update );
            } else {
                // todo submit contacts if $use_empty_result == true
                do_action( 'quizle/result_handler/updated', $result, $this->get_actions(), $keys_to_update );
            }
        }

        wp_send_json_success( $to_return );
    }

    /**
     * @param WP_Post $quizle
     *
     * @return string
     * @throws \Exception
     */
    protected function get_contacts_success_message( $quizle ) {
        return ob_get_content( function () use ( $quizle ) {

            $image          = get_post_meta( $quizle->ID, 'finish-img', true );
            $image_position = get_post_meta( $quizle->ID, 'finish-img-position', true );
            $title          = get_post_meta( $quizle->ID, 'finish-title', true );

            /**
             * Allows to change success message on contact submitted
             *
             * @since 1.0
             */
            $text = apply_filters( 'quizle/contacts/message', get_post_meta( $quizle->ID, 'contact-message', true ), $quizle );

            get_template_part( 'finish-screen', '', compact( 'quizle', 'image', 'image_position', 'title', 'text' ) );
        } );
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    protected function do_action( $action ) {
        return boolval( $_REQUEST['data']['_action'][ $action ] ?? 0 );
    }

    /**
     * @return array
     */
    protected function get_actions() {
        return array_keys( (array) $_REQUEST['data']['_action'] ?? [] );
    }
}
