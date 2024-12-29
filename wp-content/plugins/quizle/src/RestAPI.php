<?php

namespace Wpshop\Quizle;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use Wpshop\Quizle\Db\Database;

class RestAPI {

    const ROUTES_NAMESPACE = 'wpshop/quizle/v1';

    /**
     * @var Database
     */
    protected $db;

    /**
     * @param Database $database
     */
    public function __construct( Database $database ) {
        $this->db = $database;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'rest_api_init', [ $this, '_register_routes' ] );
    }

    /**
     * @return void
     */
    public function _register_routes() {
        register_rest_route( self::ROUTES_NAMESPACE, '/quizle-result/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [ $this, '_get_result' ],
            'permission_callback' => [ $this, '_permission_callback' ],

        ] );
        register_rest_route( self::ROUTES_NAMESPACE, '/quizle-results', [
            'methods'             => 'GET',
            'callback'            => [ $this, '_get_results' ],
            'permission_callback' => [ $this, '_permission_callback' ],
            'args'                => [
                'page'      => [
                    'description'       => '',
                    'type'              => 'integer',
                    'default'           => 1,
                    'minimum'           => 1,
                    'required'          => false,
                    'validate_callback' => function ( $param ) {
                        return is_numeric( $param );
                    },
                    'sanitize_callback' => function ( $param ) {
                        return absint( $param );
                    },
                ],
                'per_page'  => [
                    'description'       => '',
                    'type'              => 'integer',
                    'default'           => 10,
                    'minimum'           => 1,
                    'maximum'           => 100,
                    'required'          => false,
                    'validate_callback' => function ( $param ) {
                        return is_numeric( $param );
                    },
                    'sanitize_callback' => function ( $param ) {
                        return min( max( 1, absint( $param ) ), 100 );
                    },

                ],
                'offset'    => [
                    'description'       => '',
                    'type'              => 'integer',
                    'default'           => 0,
                    'minimum'           => 0,
                    'required'          => false,
                    'validate_callback' => function ( $param ) {
                        return is_numeric( $param );
                    },
                    'sanitize_callback' => function ( $param ) {
                        return absint( $param );
                    },

                ],
                'order'     => [
                    'description'       => '',
                    'type'              => 'string',
                    'default'           => 'desc',
                    'enum'              => [
                        'asc',
                        'desc',
                    ],
                    'required'          => false,
                    'sanitize_callback' => function ( $param ) {
                        return in_array( $param, [ 'asc', 'desc', ] ) ? $param : 'desc';
                    },
                ],
                'orderby'   => [
                    'description'       => '',
                    'type'              => 'string',
                    'default'           => 'created_at',
                    'enum'              => [
                        'id',
                        'created_at',
                    ],
                    'required'          => false,
                    'sanitize_callback' => function ( $param ) {
                        return in_array( $param, [ 'id', 'created_at' ] ) ? $param : 'created_at';
                    },
                ],
                'quizle_id' => [
                    'description'       => '',
                    'type'              => 'integer',
                    'default'           => 0,
                    'required'          => false,
                    'validate_callback' => function ( $param ) {
                        return is_numeric( $param );
                    },
                    'sanitize_callback' => function ( $param ) {
                        return absint( $param );
                    },

                ],
                'date_from' => [
                    'type'     => 'string',
                    'default'  => '',
                    'required' => false,
                ],
            ],
        ] );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function _get_result( $request ) {
        if ( isset( $request['id'] ) ) {

            if ( $result = $this->db->get_quizle_result( $request['id'] ) ) {
                $response = new WP_REST_Response( container()->get( QuizleResultExport::class )->get_result_row( $result ) );
                $response->add_link( 'collection', rest_url( self::ROUTES_NAMESPACE . '/quizle-results' ) );

                return $response;
            }

            return new WP_Error( 'rest_result_invalid', esc_html__( 'The result does not exists', 'quizle' ) );
        }

        return new WP_Error( 'rest_api_sad', esc_html__( 'Something went wrong', 'quizle' ) );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function _get_results( $request ) {
        $per_page = $request->get_param( 'per_page' );

        $results = $this->db->get_quizle_results(
            [],
            $request->get_param( 'quizle_id' ) ?: null,
            true,
            $request->get_param( 'per_page' ),
            ( $request->get_param( 'page' ) - 1 ) * $per_page,
            $request->get_param( 'orderby' ),
            $request->get_param( 'order' )
        );

        $results = array_map( function ( $result ) {
            return ( new QuizleResult() )->populate( $result )->to_array();
        }, $results );

        $total = $this->db->get_quizle_results_count( [], $request->get_param( 'quizle_id' ) ?: null );
        $pages = ceil( $total / ( $per_page ?: 1 ) );

        $response = new WP_REST_Response( $results );
        $response->header( 'X-WP-Total', $total );
        $response->header( 'X-WP-TotalPages', $pages );

        return $response;
    }

    /**
     * @return bool
     */
    public function _permission_callback() {

        /**
         * @since 1.3
         */
        $capability = apply_filters( 'quizle/rest_api/permission_capability', 'edit_posts' );
        if ( is_bool( $capability ) ) {
            return $capability;
        }

        return current_user_can( $capability );
    }
}
