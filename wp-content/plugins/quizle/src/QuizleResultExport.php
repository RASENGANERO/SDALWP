<?php

namespace Wpshop\Quizle;

use AmoCRM\Client\AmoCRMApiClient;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\Integration\AmoCRM;
use Wpshop\Quizle\Integration\Bitrix24;
use Wpshop\Quizle\Integration\Telegram;
use ZipArchive;

class QuizleResultExport {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @param Settings $settings
     * @param Database $db
     */
    public function __construct( Settings $settings, Database $db ) {
        $this->settings = $settings;
        $this->db       = $db;
    }

    /**
     * @return void
     */
    public function init() {
        add_filter( 'quizle/result_export/ignore_row', [ $this, '_ignore_empty_contacts_row' ], 10, 2 );
        add_action( 'quizle/result_handler/updated', [ $this, '_submit_to_telegram' ], 10, 2 );
        add_action( 'quizle/result_handler/updated', [ $this, '_submit_to_bitrix' ], 10, 2 );
        add_action( 'quizle/result_handler/updated', [ $this, '_submit_to_amocrm' ], 10, 2 );
        add_action( 'quizle/result_handler/updated', [ $this, '_submit_to_webhooks' ], 10, 2 );
    }

    /**
     * @param bool  $all_empty
     * @param array $row
     *
     * @return bool
     */
    public function _ignore_empty_contacts_row( $all_empty, $row ) {
        if ( ! $this->settings->get_value( 'integrations.skip_empty_contacts' ) ) {
            return false;
        }

        $fields    = [ 'username', 'email', 'phone' ];
        $all_empty = true;
        foreach ( $fields as $field ) {
            if ( array_key_exists( $field, $row ) ) {
                $value     = trim( $row[ $field ] );
                $all_empty = $all_empty && empty( $value ); // ensure all required fields are empty
            }
        }

        return $all_empty;
    }

    /**
     * @param QuizleResult $result
     * @param string[]     $actions
     *
     * @return void
     */
    public function _submit_to_telegram( QuizleResult $result, $actions ) {

        /**
         * Allows you to limit the sending of results to Bitrix
         *
         * @since 1.4
         */
        $submit = apply_filters(
            'quizle/result_export/submit_to_telegram',
            in_array( QuizleResultHandler::ACTION_UPDATE_CONTACTS, $actions ),
            $result,
            $actions
        );

        if ( ! $submit ) {
            return;
        }

        $result_data = $this->get_result_row( $result );

        if ( ! $result_data ) {
            return;
        }

        container()->get( Telegram::class )->submit_result( $result_data, $result );
    }

    /**
     * @param QuizleResult $result
     * @param array        $actions
     *
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function _submit_to_bitrix( QuizleResult $result, $actions ) {

        /**
         * Allows you to limit the sending of results to Bitrix
         *
         * @since 1.3
         */
        $submit = apply_filters(
            'quizle/result_export/submit_to_bitrix',
            in_array( QuizleResultHandler::ACTION_UPDATE_CONTACTS, $actions ),
            $result,
            $actions
        );

        if ( ! $submit ) {
            return;
        }

        $result_data = $this->get_result_row( $result );

        if ( ! $result_data ) {
            return;
        }

        container()->get( Bitrix24::class )->submit_result( $result_data, $result );
    }

    /**
     * @param QuizleResult $result
     * @param array        $actions
     *
     * @return void
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function _submit_to_amocrm( QuizleResult $result, $actions ) {
        if ( ! $this->settings->get_value( 'integrations.amocrm.enabled' ) ) {
            return;
        }

        /**
         * Allows you to limit the sending of results to AmoCRM
         *
         * @since 1.3
         */
        $submit = apply_filters(
            'quizle/result_export/submit_to_amocrm',
            in_array( QuizleResultHandler::ACTION_UPDATE_CONTACTS, $actions ),
            $result,
            $actions
        );

        if ( ! $submit ) {
            return;
        }

        $result_data = $this->get_result_row( $result );

        if ( ! $result_data ) {
            return;
        }

        container()->get( AmoCRM::class )->submit_result( $result_data, $result );
    }


    /**
     * @param QuizleResult $result
     * @param array        $actions
     *
     * @return void
     */
    public function _submit_to_webhooks( QuizleResult $result, $actions ) {

        /**
         * Allows you to limit the sending of results to the webhook
         *
         * @since 1.3
         */
        $submit = apply_filters(
            'quizle/result_export/submit_to_webhook',
            in_array( QuizleResultHandler::ACTION_UPDATE_CONTACTS, $actions ),
            $result,
            $actions
        );

        if ( ! $submit ) {
            return;
        }

        $urls = $this->settings->get_value( 'integrations.webhook.urls' );
        $urls = wp_parse_list( $urls );

        foreach ( $urls as $url ) {
            $result_data = $this->get_result_row( $result );

            if ( ! $result_data ) {
                continue;
            }

            /**
             * Allows to modify args for wp_remote_post()
             *
             * @since 1.3
             */
            $remote_post_args = apply_filters( 'quizle/result_export/remote_post_args', [
                'timeout'     => 5,
                'blocking'    => false,
                'redirection' => 5,
                'sslverify'   => false,
                'headers'     => [
                    'Content-Type' => 'application/json',
                ],
                'body'        => json_encode( $result_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ),
            ], $result, $url, $result_data );

            if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
                $response = wp_remote_post( $url, $remote_post_args );
            }
        }
    }

    /**
     * @param QuizleResult $result
     *
     * @return array
     */
    public function get_result_row( QuizleResult $result ) {
        foreach ( $this->get_stored_results( [ $result->result_id ], $result->quiz_id, false ) as $row ) {

            /**
             * @since 1.3
             */
            $ignore_row = apply_filters( 'quizle/result_export/ignore_row', false, $row );

            if ( $ignore_row ) {
                continue;
            }

            $row['quizle_name'] = $row['quizle_name'] ?: '__quizle-removed__';

            $additional_data = $row['additional_data'];
            $result_data     = wp_parse_args( \Wpshop\Quizle\json_decode( $row['result_data'], true ), [
                'questions'   => [],
                'result_item' => [],
            ] );

            unset(
                $row['result_data'],
                $row['additional_data']
            );

            if ( ! empty( $result_data['quizle_title'] ) ) {
                $row['quizle_title'] = $result_data['quizle_title'];
            }

            $questions_with_answers = $this->retrieve_questions_with_answers( $result_data['questions'], 'array' );
            $messengers             = $this->retrieve_messengers( $additional_data );

            return array_merge(
                $row,
                $questions_with_answers,
                $messengers,
                [ 'result' => $this->prepare_result( $result_data['result_item'] ?? [] ) ] );
        }

        return [];
    }

    /**
     * @param array    $result_ids
     * @param int|null $quizle_id
     * @param bool     $finished
     *
     * @return void
     */
    public function export_csv( array $result_ids, $quizle_id, $finished ) {

        $file_handlers = [];
        foreach ( $this->get_stored_results( $result_ids, $quizle_id, $finished ) as $row ) {
            $row['quizle_name'] = $row['quizle_name'] ?: '__quizle-removed__';

            $additional_data = $row['additional_data'];
            $result_data     = wp_parse_args( \Wpshop\Quizle\json_decode( $row['result_data'], true ), [
                'questions'   => [],
                'result_item' => [],
            ] );

            unset(
                $row['result_data'],
                $row['additional_data']
            );

            if ( ! empty( $result_data['quizle_title'] ) ) {
                $row['quizle_title'] = $result_data['quizle_title'];
            }

            $questions_with_answers = $this->retrieve_questions_with_answers( $result_data['questions'] );
            $messengers             = $this->retrieve_messengers( $additional_data );
            $main_headers           = array_keys( $row );
            $messenger_headers      = array_keys( $messengers );
            $question_headers       = array_keys( $questions_with_answers );

            // init own file for a quizle
            if ( ! array_key_exists( $row['quizle_name'], $file_handlers ) ) {
                $file = tempnam( get_temp_dir(), 'quizle-results' );
                $fh   = fopen( $file, 'w+' );
                register_shutdown_function( 'unlink', $file );

                $file_handlers[ $row['quizle_name'] ] = [
                    'fh'                => $fh,
                    'file'              => $file,
                    'main_headers'      => $main_headers,
                    'messenger_headers' => $messenger_headers,
                    'question_headers'  => $question_headers,
                ];

                $headers = array_merge(
                    $main_headers,
                    $messenger_headers,
                    $question_headers,
                    [ 'result' ]
                );
                fputcsv( $fh, $headers );
            }

            $file_handler = $file_handlers[ $row['quizle_name'] ];

            $headers = array_merge(
                $file_handler['main_headers'],
                $file_handler['messenger_headers'],
                $file_handler['question_headers'],
                [ 'result' ]
            );

            // copy to new file if there are changes in questions
            if ( $question_headers !== $file_handler['question_headers'] ||
                 $messenger_headers !== $file_handler['messenger_headers']
            ) {
                $additional_question_headers = array_diff( $question_headers, $file_handler['question_headers'] );
                $question_headers            = array_merge( $file_handler['question_headers'], $additional_question_headers );

                $additional_messenger_headers = array_diff( $messenger_headers, $file_handler['messenger_headers'] );
                $messenger_headers            = array_merge( $file_handler['messenger_headers'], $additional_messenger_headers );

                $new_file = tempnam( get_temp_dir(), 'quizle-results' );
                $new_fh   = fopen( $new_file, 'w+' );
                register_shutdown_function( 'unlink', $new_file );

                $headers = array_merge(
                    $main_headers,
                    $messenger_headers,
                    $question_headers,
                    [ 'result' ]
                );
                fputcsv( $new_fh, $headers );

                rewind( $file_handler['fh'] );
                $stored_headers = fgetcsv( $file_handler['fh'] );
                while ( false !== ( $stored_row = fgetcsv( $file_handler['fh'] ) ) ) {
                    $stored_row = array_combine( $stored_headers, $stored_row );
                    $to_csv     = [];
                    foreach ( $headers as $header ) {
                        $to_csv[] = $stored_row[ $header ] ?? '';
                    }
                    fputcsv( $new_fh, $to_csv );
                }
                fclose( $file_handler['fh'] );

                $file_handler = $file_handlers[ $row['quizle_name'] ] = [
                    'fh'                => $new_fh,
                    'file'              => $new_file,
                    'main_headers'      => $main_headers,
                    'messenger_headers' => $messenger_headers,
                    'question_headers'  => $question_headers,
                ];
            }

            $data           = array_merge( $row, $messengers, $questions_with_answers );
            $data['result'] = $this->prepare_result( $result_data['result_item'] );

            $to_csv = [];
            foreach ( $headers as $header ) {
                $to_csv[] = $data[ $header ] ?? '';
            }
            fputcsv( $file_handler['fh'], $to_csv );
        }

        foreach ( $file_handlers as $file_handler ) {
            fclose( $file_handler['fh'] );
        }

        $file = tempnam( get_temp_dir(), 'quizle-results-zip' );
        register_shutdown_function( 'unlink', $file );
        $zip = new ZipArchive();
        $zip->open( $file, ZipArchive::OVERWRITE );
        foreach ( $file_handlers as $name => $file_handler ) {
            $zip->addFile( $file_handler['file'], "{$name}.csv" );
        }
        $zip->close();
        header( 'Content-Type: application/zip' );
        header( 'Content-Length: ' . filesize( $file ) );
        header( 'Content-Disposition: attachment; filename="quizle-results-' . current_datetime()->format( 'Ymd-His' ) . '.zip"' );
        readfile( $file );

        die;
    }

    /**
     * @param array    $result_ids
     * @param int|null $quizle_id
     * @param bool     $only_finished
     *
     * @return array|\Generator
     */
    protected function get_stored_results( array $result_ids, $quizle_id, $only_finished ) {
        $limit  = 1000;
        $offset = 0;

        if ( $result_ids && count( $result_ids ) < $limit ) {
            foreach ( $this->db->get_quizle_results( $result_ids, $quizle_id, $only_finished, $limit ) as $result ) {
                yield $result;
            }
        } else {
            $total_count = $this->db->get_quizle_results_count( $result_ids, $quizle_id, $only_finished );

            if ( ! $total_count ) {
                return [];
            }

            while ( $offset < $total_count ) {
                foreach ( $this->db->get_quizle_results( $result_ids, $quizle_id, $only_finished, $limit, $offset ) as $result ) {
                    yield $result;
                }
                $offset += $limit;
            }
        }
    }

    /**
     * @param array  $questions
     * @param string $format 'simple', 'array'
     *
     * @return array
     */
    protected function retrieve_questions_with_answers( $questions, $format = 'simple' ) {
        $result = [];
        if ( is_array( $questions ) ) {
            if ( $format === 'simple' ) {
                $n = 1;
                foreach ( $questions as $question ) {
                    $answers        = retreive_answers( (array) ( $question['answers'] ?: [] ) );
                    $key            = sprintf( __( 'question%d: ', QUIZLE_TEXTDOMAIN ), $n ++ ) . $question['title'];
                    $result[ $key ] = implode( ', ', $answers );
                }
            } else if ( $format === 'array' ) {
                foreach ( $questions as $question ) {
                    $answers             = retreive_answers( (array) ( $question['answers'] ?: [] ) );
                    $result['answers'][] = [
                        'q' => $question['title'],
                        'a' => implode( ', ', $answers ),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param string $data
     *
     * @return array
     */
    protected function retrieve_messengers( $data ) {
        $result = [];
        if ( $data ) {
            $data = \Wpshop\Quizle\json_decode( $data, true );
            if ( is_array( $data['messengers'] ) ) {
                foreach ( $data['messengers'] as $name => $value ) {
                    $result[ $name ] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $result_item
     *
     * @return string
     */
    protected function prepare_result( $result_item ) {
        if ( ! empty( $result_item['redirect_link'] ) ) {
            return $result_item['redirect_link'];
        }

        return $result_item['title'] ?? '';
    }
}
