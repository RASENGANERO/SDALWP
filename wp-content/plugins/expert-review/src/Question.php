<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Settings\ExpertOptions;

class Question {

    /**
     * @var AdvancedOptions
     */
    protected $advanced_options;

    /**
     * Question constructor.
     *
     * @param AdvancedOptions $advanced_options
     */
    public function __construct( AdvancedOptions $advanced_options ) {
        $this->advanced_options = $advanced_options;
    }

    /**
     * @return void
     */
    public function init() {
        $this->setup_ajax();

        add_filter( 'expert_review_question_message', [ $this, 'default_message_filters' ] );

        do_action( __METHOD__, $this );
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function default_message_filters( $message ) {
        $message = wpautop( $message );

        return $message;
    }

    /**
     * @return void
     */
    protected function setup_ajax() {
        $action = 'expert_review_submit_question';
        add_action( "wp_ajax_$action", [ $this, '_submit_question' ] );
        add_action( "wp_ajax_nopriv_$action", [ $this, '_submit_question' ] );
    }

    /**
     * @return void
     */
    public function _submit_question() {
        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Forbidden' ] );
        }

        if ( isset( $_REQUEST['data'] ) ) {

            if ( ! apply_filters( 'expert_review:submit_question', true ) ) {
                return wp_send_json_success();
            }

            $data = map_deep( $_REQUEST['data'], 'trim' );
            $data = wp_parse_args( $data, [
                '_a'       => '',
                '_p'       => '',
                'text'     => '',
                'settings' => '',
            ] );

            if ( $this->advanced_options->enable_consent_checkbox && empty( $data['consent'] ) ) {
                wp_send_json_error( new \WP_Error(
                    'no_consent',
                    __( 'Cannot handle question without consent to the processing of personal data', Plugin::TEXT_DOMAIN )
                ) );
            }

            if ( $this->advanced_options->email_to_expert ) {
                if ( ! $data['settings'] ) {
                    wp_send_json_error( new \WP_Error(
                            'legacy_form',
                            sprintf( __(
                                'Warning! Looks like custom form used not properly according new version. More at %s',
                                Plugin::TEXT_DOMAIN
                            ), 'https://support.wpshop.ru/faq/kak-pomenyat-formu-zadat-vopros/' )
                        )
                    );
                }
            }

            $settings = wp_unslash( $data['settings'] );
            $settings = json_decode( $settings, true );
            $sign     = ! empty( $settings['sign'] ) ? $settings['sign'] : '';
            unset( $settings['sign'] );

            if ( ! Utilities::verify_data( $sign, $settings, wp_create_nonce( 'button_settings' ) ) ) {
                wp_send_json_error( new \WP_Error( 'broken_settings', __(
                    'The settings passed through the form are invalid',
                    Plugin::TEXT_DOMAIN
                ) ) );
            }

            $data['settings'] = $settings;

            if ( ! empty( $data['email'] ) ) { // bot detected
                wp_send_json_error( [ 'message' => 'Forbidden' ] );
            }

            if ( empty( $data['name'] ) || ( empty( $data['_a'] ) && empty( $data['_p'] ) ) ) {
                wp_send_json_error( [ 'message' => __( 'Unable to send email without mail or name data', Plugin::TEXT_DOMAIN ) ] );
            }

            if ( ! empty( $data['_a'] ) && ! is_email( $data['_a'] ) ) {
                wp_send_json_error( [ 'message' => __( 'Please, fill email field', Plugin::TEXT_DOMAIN ) ] );
            }

            $message = wp_strip_all_tags( $data['text'] );
            $message = esc_html( sprintf(
                    __( "Question from: %s\n\nEmail: %s\n\nPage: %s\n\n", Plugin::TEXT_DOMAIN ),
                    $data['name'],
                    $data['_a'] ? "<{$data['_a']}>" : "Tel: {$data['_p']}",
                    $_SERVER['HTTP_REFERER']
                ) ) . $message;

            $message = apply_filters( 'expert_review_question_message', $message, $data );

            $headers = [ 'Content-Type: text/html' ];
            if ( $data['_a'] ) {
                $headers[] = "Reply-To: {$data['name']} <{$data['_a']}>";
            }
            $headers = $this->append_email_copy_headers( $headers, $data );
            $headers = apply_filters( 'expert_review_question_mail_headers', $headers, $data );

            $email_to = trim( $this->advanced_options->email_to ) ?: get_option( 'admin_email' );
            if ( wp_mail(
                apply_filters( 'expert_review_question_mail_to', $email_to, $data ),
                sprintf( __( 'Question to Expert from %s', Plugin::TEXT_DOMAIN ), $data['name'] ),
                $message,
                $headers
            ) ) {
                do_action( 'expert_review:send_question_success', $message, $data );
                wp_send_json_success();
            } else {
                wp_send_json_error( [ 'message' => __( 'Unable to send email', Plugin::TEXT_DOMAIN ) ] );
            }
        }
        wp_send_json_error( [ 'message' => 'Unable to handle request without data' ] );
    }

    /**
     * @param array $headers
     * @param array $data
     *
     * @return array
     */
    protected function append_email_copy_headers( array $headers, array $data ) {
        $prepare = function ( $emails ) {
            return array_filter(
                array_map(
                    function ( $item ) {
                        if ( false !== mb_strpos( $item, '<', 0, 'UTF-8' ) ) {
                            $result    = explode( '<', $item, 2 );
                            $result    = array_map( 'trim', $result );
                            $result[1] = str_replace( '>', '', $result[1] );

                            return $result;
                        }

                        return $item;
                    },
                    preg_split( "/[\n,]+/", $emails, - 1, PREG_SPLIT_NO_EMPTY )
                )
            );
        };

        $cc  = apply_filters( 'expert_review_question_mail_cc', $prepare( $this->advanced_options->email_cc ) );
        $bcc = apply_filters( 'expert_review_question_mail_bcc', $prepare( $this->advanced_options->email_bcc ) );

        foreach ( $cc as $item ) {
            if ( is_array( $item ) && isset( $item[0], $item[1] ) ) {
                $item = "{$item[0]} <{$item[1]}>";
            }
            $headers[] = "Cc: $item";
        }

        foreach ( $bcc as $item ) {
            if ( is_array( $item ) && isset( $item[0], $item[1] ) ) {
                $item = "{$item[0]} <{$item[1]}>";
            }
            $headers[] = "Bcc: $item";
        }


        if ( $this->advanced_options->email_to_expert ) {
            $settings = $data['settings'];
            if ( $settings['expertType'] === 'expert_id' &&
                 ! $settings['use_phone'] &&
                 ( $expert = container()->get( ExpertOptions::class )->get_by_id( $settings['expertId'] ) )
            ) {
                $type = $this->advanced_options->email_to_expert === 'cc' ? 'Cc' : 'Bcc';
                if ( ! empty( $expert['email'] ) ) {
                    $item      = "{$expert['name']} <{$expert['email']}>";
                    $headers[] = "{$type}: $item";
                }
            }
        }

        return $headers;
    }
}
