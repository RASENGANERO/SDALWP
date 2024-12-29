<?php

namespace Wpshop\Quizle;

use Wpshop\Quizle\Admin\Settings;

class MailService {

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct( Logger $logger ) {
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'quizle/result_handler/updated', [ $this, 'send' ], 10, 2 );
        add_action( 'quizle/mail/send', [ $this, '_send_to_admin' ] );
//        add_action( 'quizle/mail/send', [ $this, '_send_to_user' ] );
    }

    /**
     * @param QuizleResult $result
     * @param string[]     $actions
     *
     * @return void
     */
    public function send( QuizleResult $result, $actions ) {

        /**
         * Allows you to limit the sending of results to email
         *
         * @since 1.3.1
         */
        $submit = apply_filters(
            'quizle/mail/send_mail',
            in_array( QuizleResultHandler::ACTION_UPDATE_CONTACTS, $actions ),
            $result,
            $actions
        );

        if ( ! $submit ) {
            return;
        }

        try {
            do_action( 'quizle/mail/send', $result );
        } catch ( \Exception $e ) {
            $this->logger->error( $e );;
        }
    }

    /**
     * @param QuizleResult $result
     *
     * @return void
     * @throws \Exception
     */
    public function _send_to_admin( $result ) {
//        if ( ! $result->result_id ) {
//            return;
//        }

        if ( ! ( $emails = get_post_meta( $result->quiz_id, 'emails-for-contacts', true ) ) ) {
            return;
        }

        $emails = wp_parse_list( $emails );
        $emails = array_map( 'trim', $emails );

        /**
         * @since 1.4
         */
        $emails = apply_filters( 'quizle/mail/emails', $emails, $result );

        $title   = get_the_title( $result->quiz_id );
        $message = ob_get_content( function () use ( $result ) {
            get_template_part( 'mail/html/result', 'admin', [ 'result' => $result ] );
        } );

        wp_mail(
            $emails,
            __( 'Quizle Contacts', QUIZLE_TEXTDOMAIN ) . ( $title ? ' [' . $title . ']' : '' ),
            $message,
            [ 'content-type: text/html' ]
        );
    }

    /**
     * @param QuizleResult $result
     *
     * @return void
     * @throws \Exception
     */
    public function _send_to_user( $result ) {
        if ( ! is_email( $result->email ) ) {
            return;
        }

        $title   = get_the_title( $result->quiz_id );
        $message = ob_get_content( function () use ( $result ) {
            get_template_part( 'mail/result', 'user', [ 'result' => $result ] );
        } );

        wp_mail(
            $result->email,
            $title ? sprintf( __( 'Your result of quizle "%s"', QUIZLE_TEXTDOMAIN ), $title ) : __( 'Your result of quizle', QUIZLE_TEXTDOMAIN ),
            $message,
            [ 'content-type: text/html' ]
        );
    }
}
