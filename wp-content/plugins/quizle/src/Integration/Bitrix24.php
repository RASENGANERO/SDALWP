<?php

namespace Wpshop\Quizle\Integration;

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\QuizleResult;

/**
 * @see https://dev.1c-bitrix.ru/rest_help/rest_sum/index.php
 * @see https://dev.1c-bitrix.ru/rest_help/crm/leads/index.php
 */
class Bitrix24 {

    use IntegrationTrait;

    const METHOD_LEAD_ADD = 'crm.lead.add';

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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function submit_result( $data, QuizleResult $result ) {
        if ( ! $this->settings->get_value( 'integrations.bitrix.enabled' ) ) {
            return;
        }

        if ( ! ( $url = $this->get_url( self::METHOD_LEAD_ADD ) ) ) {
            // todo log error
            return;
        }

        $name_parts = explode( ' ', $data['username'] );
        $name_parts = array_filter( $name_parts );
        if ( count( $name_parts ) < 2 ) {
            $name_parts[] = '';
        }

        $lead = [
            'FIELDS' => [
                'TITLE'     => __( 'User From Quizle', 'quizle' ),
                'NAME'      => $name_parts[0],
                'LAST_NAME' => end( $name_parts ),
                'EMAIL'     => [
                    [
                        'VALUE'      => $data['email'],
                        'VALUE_TYPE' => 'WORK',
                    ],
                ],
                'PHONE'     => [
                    [
                        'VALUE'      => $data['phone'],
                        'VALUE_TYPE' => 'WORK',
                    ],
                ],
                'COMMENTS'  => __( 'Quizle Data', 'quizle' ) . PHP_EOL . PHP_EOL . $this->get_note_text( $result, $data ),
            ],
        ];

        /**
         * Allows to modify bitrix lead data
         *
         * @since 1.3
         */
        $lead = apply_filters( 'quizle/integration/bitrix_lead', $lead, $data, $result );

        $response = wp_remote_post( $url, [
            'headers' => [
                'Content-type' => 'application/json',
            ],
            'body'    => json_encode( $lead ),
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

    /**
     * @param string $api_method
     *
     * @return string|null
     */
    protected function get_url( $api_method ) {
        if ( $endpoint = $this->settings->get_value( 'integrations.bitrix.endpoint' ) ) {
            return rtrim( $endpoint, '/' ) . '/' . $api_method . '.json';
        }

        return null;
    }
}
