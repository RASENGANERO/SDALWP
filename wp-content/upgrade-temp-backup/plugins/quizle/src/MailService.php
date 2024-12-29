<?php

namespace Wpshop\Quizle;

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
        add_action( 'quizle/mail/send', [ $this, '_send_to_admin' ] );
//        add_action( 'quizle/mail/send', [ $this, '_send_to_user' ] );
    }

    /**
     * @param QuizleResult $result
     *
     * @return void
     */
    public function send( QuizleResult $result ) {
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
        if ( ! ( $emails = get_post_meta( $result->quiz_id, 'emails-for-contacts', true ) ) ) {
            return;
        }

        $emails  = wp_parse_list( $emails );
        $title   = get_the_title( $result->quiz_id );
        $message = ob_get_content( function () use ( $result ) {
            get_template_part( 'mail/result', 'admin', [ 'result' => $result ] );
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
