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
                if ( $this->database->remove_quizle_result( $_POST['result_id'] ) ) {
                    $success_message_code = 2;
                }
            } else {
                if ( wp_verify_nonce( $_REQUEST['_nonce'], 'remove_quizle_result' ) ) {
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
}
