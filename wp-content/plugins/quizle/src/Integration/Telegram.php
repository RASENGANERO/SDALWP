<?php

namespace Wpshop\Quizle\Integration;

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\QuizleResult;
use function Wpshop\Quizle\get_template_part;
use function Wpshop\Quizle\ob_get_content;

class Telegram {

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
     * @param array        $data
     * @param QuizleResult $result
     *
     * @return void
     */
    public function submit_result( $data, QuizleResult $result ) {
        $token   = $this->settings->get_value( 'integrations.telegram.token' );
        $chat_id = $this->settings->get_value( 'integrations.telegram.chat_id' );

        /**
         * @since 1.4
         */
        $url_tpl = apply_filters( 'quizle/telegram/bot_message_url', 'https://api.telegram.org/bot{token}/sendMessage' );

        $url = str_replace( '{token}', $token, $url_tpl );

        $text = ob_get_content( function () use ( $data, $result ) {
            get_template_part( 'telegram/result', 'markdown', compact( 'data', 'result' ) );
        } );

        /**
         * @since 1.4
         */
        $message = apply_filters( 'quizle/telegram/message', [
            'text'       => $text,
            'chat_id'    => $chat_id,
            'parse_mode' => 'markdown',

            'disable_web_page_preview' => true,
        ] );

        // see https://telegram-bot-sdk.readme.io/reference/sendmessage
        $response = wp_remote_post( $url, [
            'timeout'     => 5,
            'blocking'    => false,
            'redirection' => 5,
            'sslverify'   => false,
            'headers'     => [
                'Content-Type' => 'application/json',
            ],
            'body'        => json_encode( $message ),
        ] );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            // todo handle error
        } else {
            $response_code    = wp_remote_retrieve_response_code( $response );
            $response_message = wp_remote_retrieve_response_message( $response );
            if ( $response_code !== 200 ) {
                // todo handle failed request
            }
        }
    }
}
