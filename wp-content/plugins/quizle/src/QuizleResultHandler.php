<?php

namespace Wpshop\Quizle;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use Wpshop\Quizle\Db\Database;

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
     * @param Database    $database
     * @param MailService $mail_service
     */
    public function __construct( Database $database, MailService $mail_service ) {
        $this->database     = $database;
        $this->mail_service = $mail_service;
    }

    /**
     * @return void
     */
    public function init() {
        $action = 'quizle_save_result';
        add_action( "wp_ajax_nopriv_{$action}", [ $this, '_handle' ] );
        add_action( "wp_ajax_{$action}", [ $this, '_handle' ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _handle() {
        if ( ! wp_verify_nonce( $_REQUEST['nonce'] ?? '', 'quizle-nonce' ) ) {
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

        if ( ! ( $quizle = get_quizle( $data['quizle'] ?? null ) ) ) {
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
                $result->name  = $data['contacts']['name'] ?? '';
                $result->email = $data['contacts']['email'] ?? '';
                $result->phone = $data['contacts']['phone'] ?? '';

                if ( ! empty( $data['messengers'] ) ) {
                    $result->add_additional_data( 'messengers', $data['messengers'] );
                    $keys_to_update[] = 'additional_data';
                }

                $this->mail_service->send( $result );

                $to_return['contacts_success_message'] = apply_filters( 'quizle/contacts/message', get_post_meta( $quizle->ID, 'contact-message', true ), $quizle );
                array_push( $keys_to_update, 'name', 'phone', 'email' );
            }

            if ( $this->do_action( self::ACTION_FINISH ) ) {
                $result->set_finished_time();
                $keys_to_update[] = 'finished_at';
            }

            if ( $this->do_action( self::ACTION_RETURN_RESULT_CONTENT ) ) {
                $to_return['result_content'] = $result->get_result_content();
            }

            if ( ! $use_empty_result ) {
                if ( ! $this->database->update_quizle_result( $result, $keys_to_update ) ) {
                    wp_send_json_error( new WP_Error( 'db_error', __( 'Unable to update quizle result', QUIZLE_TEXTDOMAIN ) ) );
                }
            }
        }

        wp_send_json_success( $to_return );
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    protected function do_action( $action ) {
        return boolval( $_REQUEST['data']['_action'][ $action ] ?? 0 );
    }
}
