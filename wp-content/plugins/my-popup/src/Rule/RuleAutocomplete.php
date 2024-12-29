<?php

namespace Wpshop\PluginMyPopup\Rule;

use WP_Query;

class RuleAutocomplete {

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * RuleAutocomplete constructor.
     *
     * @param \wpdb $wpdb
     */
    public function __construct( \wpdb $wpdb ) {
        $this->wpdb = $wpdb;
    }

    /**
     * @return void
     */
    public function init() {
        $this->setup_ajax();
    }

    /**
     * @return void
     */
    protected function setup_ajax() {
        if ( is_admin() ) {
            add_action( 'my_popup:admin_enqueue_scripts', [ $this, '_localize_script' ] );
            $value_action = 'my_popup_autocomplete_search_value';
            add_action( "wp_ajax_{$value_action}", [ $this, '_autocomplete_search_ajax' ] );
            $subtype_action = 'my_popup_autocomplete_search_subtype';
            add_action( "wp_ajax_{$subtype_action}", [ $this, '_autocomplete_search_subtype_ajax' ] );
        }
    }

    /**
     * @return void
     */
    public function _localize_script() {
        wp_localize_script( 'my-popup-scripts', 'my_popup_rules_globals', [
            'nonce'                   => wp_create_nonce( 'my_popup_autocomplete' ),
            'options_without_value'   => apply_filters( 'my_popup:rule_options_without_value', [
                'all',
                'home',
                'search',
                '404',
            ] ),
            'options_without_subtype' => apply_filters( 'my_popup:rule_options_without_subtype', [
                'all',
                'home',
                'posts',
                'posts_cat',
                'posts_tag',
                'pages',
                'categories',
                'tags',
                'search',
                '404',
                'url_match',
            ] ),
        ] );
    }

    /**
     * @return void
     */
    public function _autocomplete_search_ajax() {

        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'my_popup_autocomplete' ) ) {
            wp_send_json_error( [ 'message' => 'Forbidden' ] );
        }

        if ( ! isset( $_REQUEST['query'], $_REQUEST['type'] ) ) {
            wp_send_json_error( [ 'message' => 'Unable to handle request without query or type' ] );
        }

        switch ( $_REQUEST['type'] ?? null ) {
            case 'posts':
                $this->send_posts( 'post' );
                break;
            case 'pages':
                $this->send_posts( 'page' );
                break;
            case 'post_types':
                if ( $post_types = wp_parse_list( $_REQUEST['subtypes'] ?? '' ) ) {
                    $this->send_posts( $post_types );
                }
                break;
            case 'posts_cat':
            case 'categories':
                $this->send_terms( 'category' );
                break;
            case 'tags':
            case 'posts_tag':
                $this->send_terms( 'post_tag' );
                break;
            case 'taxonomies':
            case 'posts_taxonomies':
                if ( $taxonomies = wp_parse_list( $_REQUEST['subtypes'] ?? '' ) ) {
                    $this->send_terms( $taxonomies );
                }
                break;
            case 'url_match':
//                $pattern = apply_filters(
//                    'my_popup_validate_url_match_pattern',
//                    '#' . ( $_REQUEST['query'] ?? '' ) . '#u'
//                );
//                @preg_match( $pattern, '' );
//                if ( PREG_NO_ERROR !== preg_last_error() ) {
//                    wp_send_json_success( [] );
//                }
                wp_send_json_success( [ [ 'label' => $_REQUEST['query'], 'value' => $_REQUEST['query'] ] ] );
                break;
            default:
                do_action( 'my_popup:rule_autocomplete_action' );
                break;
        }

        wp_send_json_error( [ 'message' => 'Unable to handle request' ] );
    }

    /**
     * @return void
     */
    public function _autocomplete_search_subtype_ajax() {

        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'my_popup_autocomplete' ) ) {
            wp_send_json_error( [ 'message' => 'Forbidden' ] );
        }

        if ( ! isset( $_REQUEST['query'], $_REQUEST['type'] ) ) {
            wp_send_json_error( [ 'message' => 'Unable to handle request without query or type' ] );
        }

        $search = $_REQUEST['query'] ?? '';

        switch ( $_REQUEST['type'] ?? null ) {
            case 'post_types':
                $subtypes = wp_parse_list( $_REQUEST['subtypes'] ?? [] );

                $results = (array) $this->wpdb->get_results(
                    $this->wpdb->prepare( "SELECT DISTINCT post_type FROM {$this->wpdb->posts} WHERE post_type LIKE '%s'", "%{$search}%" ),
                    ARRAY_A
                );
                $results = array_filter( $results, function ( $row ) use ( $subtypes ) {
                    return ! in_array( $row['post_type'], $subtypes );
                } );

                wp_send_json_success( array_map( function ( $row ) {
                    return [ 'label' => $row['post_type'], 'value' => $row['post_type'] ];
                }, $results ) );
                break;
            case 'taxonomies':
            case 'posts_taxonomies':
                $subtypes = wp_parse_list( $_REQUEST['subtypes'] ?? [] );

                $query = $this->wpdb->prepare( "SELECT DISTINCT taxonomy
FROM {$this->wpdb->terms} LEFT JOIN {$this->wpdb->term_taxonomy} wtt ON {$this->wpdb->terms}.term_id = wtt.term_id
WHERE wtt.taxonomy LIKE '%s'", "%{$search}%" );

                $results = (array) $this->wpdb->get_results( $query, ARRAY_A );
                $results = array_filter( $results, function ( $row ) use ( $subtypes ) {
                    return ! in_array( $row['taxonomy'], $subtypes );
                } );

                wp_send_json_success( array_map( function ( $row ) {
                    return [ 'label' => $row['taxonomy'], 'value' => $row['taxonomy'] ];
                }, $results ) );
                break;
            default:
                break;
        }

        wp_send_json_error( [ 'message' => 'Unable to handle request' ] );
    }

    /**
     * @param string|string[] $type
     *
     * @return void
     */
    protected function send_posts( $type ) {

        $query = [
            'post_type'              => $type,
            'suppress_filters'       => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'post_status'            => 'publish',
            'posts_per_page'         => 30,
            'orderby'                => 'title date',
            'order'                  => 'DESC',
        ];

        if ( ! empty( $_REQUEST['values'] ) ) {
            $query['post__not_in'] = wp_parse_id_list( $_REQUEST['values'] );
        }

        if ( isset( $_REQUEST['term'] ) ) {
            $query['s'] = wp_unslash( $_REQUEST['term'] );
        }

        $get_posts = new WP_Query;
        $posts     = $get_posts->query( $query );

        $return = [];
        foreach ( $posts as $post ) {
            $return[] = [
                'label' => sprintf( '%1$s', $post->post_title ),
                'value' => $post->ID,
            ];
        }

        wp_send_json_success( $return );
    }

    /**
     * @param string|string[] $taxonomy
     *
     * @return void
     */
    protected function send_terms( $taxonomy ) {
        $args = [
            'taxonomy'   => $taxonomy,
            'name__like' => $_REQUEST['query'],
            'hide_empty' => false,
        ];
        if ( ! empty( $_REQUEST['values'] ) ) {
            $exclude         = wp_parse_id_list( $_REQUEST['values'] );
            $args['exclude'] = implode( ',', $exclude );
        }

        /** @var \WP_Term[] $terms */
        $terms = get_terms( $args );

        if ( $terms instanceof \WP_Error ) {
            wp_send_json_error( $terms );
        }

        $result = [];
        foreach ( $terms as $term ) {
            $result[] = [
                'label' => esc_html( $term->name ),
                'value' => $term->term_id,
            ];
        }

        wp_send_json_success( $result );
    }

    /**
     * Result example:
     * <pre>
     * array (size=2)
     *  'posts' =>
     *      array (size=1)
     *          1 => string 'Привет, мир!' (length=21)
     *  'terms' =>
     *      array (size=1)
     *          1 => string 'Без рубрики' (length=21)
     * </pre>
     *
     * @param array $rules
     *
     * @return array
     */
    public function gather_rules_values( array $rules ) {
        $result = [
            'posts'     => [],
            'terms'     => [],
            'url_match' => [],
        ];

        $post_ids = [];
        $term_ids = [];
        foreach ( $rules as $rule ) {
            switch ( $rule['type'] ) {
                case 'posts':
                case 'pages':
                case 'post_types':
                    $post_ids = array_unique( array_merge( $post_ids, wp_parse_id_list( $rule['value'] ) ) );
                    break;
                case 'categories':
                case 'tags':
                case 'taxonomies':
                case 'posts_cat':
                case 'posts_tag':
                case 'posts_taxonomies':
                    $term_ids = array_unique( array_merge( $term_ids, wp_parse_id_list( $rule['value'] ) ) );
                    break;
                case 'url_match':
                    break;
                default:
                    break;
            }
        }

        $wpdb = $this->wpdb;

        if ( $post_ids ) {
            $post_ids = implode( ',', $post_ids );
            $items    = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID IN ($post_ids)" );
            foreach ( $items as $item ) {
                $result['posts'][ $item->ID ] = $item->post_title;
            }
        }

        if ( $term_ids ) {
            $term_ids = implode( ',', $term_ids );
            $items    = $wpdb->get_results( "SELECT term_id, name FROM {$wpdb->terms} WHERE term_id IN ($term_ids)" );
            foreach ( $items as $item ) {
                $result['terms'][ $item->term_id ] = $item->name;
            }
        }

        return apply_filters( 'my_popup:rule_gather_values', $result );
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    public function gather_rule_subtypes( array $rules ) {
        $post_types = [];
        $taxonomies = [];
        foreach ( $rules as $rule ) {
            switch ( $rule['type'] ) {
                case 'post_types':
                    $post_types = array_unique( array_merge( $post_types, wp_parse_list( $rules['subtype'] ?? [] ) ) );
                    break;
                case 'taxonomies':
                case 'posts_taxonomies':
                    $taxonomies = array_unique( array_merge( $taxonomies, wp_parse_list( $rules['subtype'] ?? [] ) ) );
                    break;
                default:
                    break;
            }
        }

        return apply_filters( 'my_popup:rule_gather_subtypes', [
            'posts' => array_combine( $post_types, $post_types ),
            'terms' => array_combine( $taxonomies, $taxonomies ),
        ] );
    }
}
