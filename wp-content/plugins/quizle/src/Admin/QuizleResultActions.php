<?php

namespace Wpshop\Quizle\Admin;

use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\Quizle;

class QuizleResultActions {

    const REMOVE = 'remove';

    /**
     * @var Database
     */
    protected $database;

    /**
     * @param Database $database
     */
    public function __construct( Database $database ) {
        $this->database = $database;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'current_screen', [ $this, 'handle_request' ] );
        add_filter( 'removable_query_args', function ( $removable_query_args ) {
            $removable_query_args[] = 'quizle-removed';

            return $removable_query_args;
        } );
    }

    /**
     * @param int $code
     *
     * @return string|null
     */
    public static function get_success_message( $code ) {
        switch ( $code ) {
            case 1:
                return __( 'Result successfully removed.', QUIZLE_TEXTDOMAIN );
            case 2:
                return __( 'Results successfully removed.', QUIZLE_TEXTDOMAIN );
            default:
                return null;
        }
    }

    /**
     * @param int $result_id
     *
     * @return string
     */
    public static function get_remove_url( $result_id ) {
        return add_query_arg( [
            'post_type' => Quizle::POST_TYPE,
            'page'      => MenuPage::RESULT_LIST_SLUG,
            'action'    => self::REMOVE,
            'id'        => $result_id,
            '_nonce'    => wp_create_nonce( 'remove_quizle_result' ),
        ], admin_url( 'edit.php' ) );
    }

    /**
     * @return void
     */
    public function handle_request() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $request = wp_parse_args( $_REQUEST, [
            'post_type' => '',
            'page'      => '',
            'action'    => '',
            'id'        => '',
            '_nonce'    => '',
        ] );

        if ( Quizle::POST_TYPE === $request['post_type'] &&
             MenuPage::RESULT_LIST_SLUG === $request['page'] &&
             self::REMOVE === $request['action']
        ) {

            $success_message_code = '';
            if ( ! empty( $_POST['result_id'] ) ) { // bulk action
                check_admin_referer( 'bulk-' . get_current_screen()->base );
                $this->remove_attached_files( $_POST['result_id'] );
                if ( $this->database->remove_quizle_result( $_POST['result_id'] ) ) {
                    $success_message_code = 2;
                }
            } else {
                if ( wp_verify_nonce( $_REQUEST['_nonce'], 'remove_quizle_result' ) ) {
                    $this->remove_attached_files( $request['id'] );
                    if ( $this->database->remove_quizle_result( $request['id'] ) ) {
                        $success_message_code = 1;
                    }
                }
            }

            wp_redirect( add_query_arg( [
                'post_type'      => Quizle::POST_TYPE,
                'page'           => MenuPage::RESULT_LIST_SLUG,
                'quizle-removed' => $success_message_code,
            ], admin_url( 'edit.php' ) ) );
            die;
        }
    }

    /**
     * @param int $result_id
     *
     * @return void
     */
    protected function remove_attached_files( $result_id ) {
        if ( $result = $this->database->get_quizle_result( $result_id ) ) {
            $quizle_attached_files = get_option( '_quizle_attached_files_' . $result->quiz_id, [] );

            $url_file_map = [];
            foreach ( get_option( '_quizle_attached_files_' . $result->quiz_id, [] ) as $item ) {
                $url_file_map[ $this->normilize_url( $item['url'] ) ] = $item['file'];
            }

            $questions = $result->get_result_data_array()['questions'] ?? [];

            foreach ( $questions as $question ) {
                $answers = $question['answers'] ?? [];
                foreach ( $answers as $answer ) {
                    if ( $answer['answer_id'] === '__file__' ) {
                        foreach ( $answer['value'] as $url ) {
                            $url = $this->normilize_url( $url );
                            if ( array_key_exists( $url, $url_file_map ) ) {
                                unlink( $url_file_map[ $url ] );
                                unset( $url_file_map[ $url ] );
                            } else {
                                $this->try_unlink_by_url( $url );
                            }
                        }
                    }
                }
            }

            $quizle_attached_files = array_filter( $quizle_attached_files, function ( $item ) use ( $url_file_map ) {
                return array_key_exists( $this->normilize_url( $item['url'] ), $url_file_map );
            } );

            if ( $quizle_attached_files ) {
                update_option( '_quizle_attached_files_' . $result->quiz_id, $quizle_attached_files, false );
            } else {
                delete_option( '_quizle_attached_files_' . $result->quiz_id );
            }
        }
    }

    /**
     * @param string $url
     *
     * @return void
     */
    protected function try_unlink_by_url( $url ) {

    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function normilize_url( $url ) {
        return str_replace( 'http://', 'https://', $url );
    }
}
