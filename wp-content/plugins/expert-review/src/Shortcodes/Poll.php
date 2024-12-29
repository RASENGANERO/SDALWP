<?php

namespace Wpshop\ExpertReview\Shortcodes;

use Wpshop\ExpertReview\Plugin;
use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Shortcodes;

class Poll {

    const POST_TYPE = 'expert_review_poll';

    const META_POLL_PARAMS      = 'expert_review_poll_params';
    const META_POLL_ANSWERS     = 'expert_review_poll_answers';
    const META_POLL_TOTAL_VOTES = 'expert_review_poll_total_votes';
    const META_LINK_POLLS       = '_expert_review_poll_ids';
    const META_LINK_POSTS       = '_expert_review_post_ids';
    const META_POLL_DISPLAY     = 'expert_review_poll_display';
    const META_POLL_CAN_VOTE    = 'expert_review_poll_can_vote';
    const META_POLL_RESET       = 'expert_review_poll_reset';

    /**
     * @var AdvancedOptions
     */
    protected $advanced_options;

    /**
     * Poll constructor.
     *
     * @param AdvancedOptions $advanced_options
     */
    public function __construct( AdvancedOptions $advanced_options ) {
        $this->advanced_options = $advanced_options;
    }

    /**
     * @var array
     */
    protected $default_params = [
        'title'      => '',
        'show_title' => 1,
        'show_count' => true,
        'style'      => 'light-2', // light-1 solid-1
        'color'      => 'blue-1',
        'multiple'   => false,
        'answers'    => [],
    ];

    /**
     * @return void
     */
    public function init() {
        $this->setup_ajax();
        $this->register_post_type();
        add_filter( 'wp_insert_post_data', [ $this, '_handle_poll_shortcode' ], 999, 2 );

        do_action( __METHOD__, $this );
    }

    /**
     * @return bool
     */
    protected function do_render_templates() {
        return (bool) apply_filters( 'expert_review_do_render_templates', $this->advanced_options->enable_templates );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function shortcode( $atts, $content, $shortcode ) {
        if ( ! apply_filters( 'expert_review_output_shortcodes', true, $shortcode ) ) {
            return '';
        }

        $atts = shortcode_atts( [ 'id' => '', 'params' => '' ], $atts, $shortcode );

        if ( ! $atts['id'] || ! get_post( $atts['id'] ) ) {
            return '';
        }

        if ( ! get_post_meta( $atts['id'], self::META_POLL_DISPLAY, true ) ) {
            return '';
        }

        $post_params = get_post_meta( $atts['id'], self::META_POLL_PARAMS, true ) ?: [];
        $params      = wp_parse_args( $atts['params'] ? Shortcodes::unserialize_shortcode_params( $atts['params'] ) : [], $this->default_params );
//        $params      = wp_parse_args( $params, $post_params );
        $params = wp_parse_args( $post_params, $params );

        $answers = $this->get_answers_with_percent( $atts['id'] );

        if ( $this->do_render_templates() ) {
            return er_render_template( 'poll.php', compact( 'atts', 'content', 'params', 'answers' ) );
        }

        $data      = [
            'id'       => esc_attr( $atts['id'] ),
            'can_vote' => (int) get_post_meta( $atts['id'], self::META_POLL_CAN_VOTE, true ),
            'r'        => (int) get_post_meta( $atts['id'], self::META_POLL_RESET, true ),
        ];
        $data_attr = [];
        foreach ( $data as $k => $v ) {
            $data_attr[] = "data-{$k}=\"{$v}\"";
        }
        $data_attr = implode( ' ', $data_attr );

        $output = '';
        $output .= '<div class="expert-review-poll js-expert-review-poll expert-review-poll--style-' . esc_attr( $params['style'] ) . ' expert-review-poll--color-' . esc_attr( $params['color'] ) . '" ' . $data_attr . '>';

        if ( $params['title'] && $params['show_title'] ) {
            $output .= '    <div class="expert-review-poll__header">' . $params['title'] . '</div>';
        }

        $total = (int) get_post_meta( $atts['id'], Poll::META_POLL_TOTAL_VOTES, true );

        foreach ( $answers as $item ) {

            $output .= '<div class="expert-review-poll-item js-expert-review-poll-item" data-id="' . $item['id'] . '">';
            $output .= '  <div class="expert-review-poll-item__answer js-expert-review-poll-item-answer">' . $item['text'] . '</div>';
            $output .= '  <div class="expert-review-poll-item__num js-expert-review-poll-item-num">' . $item['percent'] . '%</div>';
            $output .= '  <div class="expert-review-poll-item__progress js-expert-review-poll-item-progress" style="width: ' . ( $item['percent'] ? $item['percent'] . '%' : 0 ) . '"></div>';
            $output .= '</div>';
        }

        $result_show_txt = esc_html( apply_filters( 'expert_review_poll:show_result_text', __( 'Show Results', Plugin::TEXT_DOMAIN ) ) );
        $result_hide_txt = esc_attr( apply_filters( 'expert_review_poll:hide_result_text', __( 'Hide Results', Plugin::TEXT_DOMAIN ) ) );

        if ( ! empty( $params['show_results_button'] ) ) {
            $output .= '<button class="button expert-review-poll__result-button js-expert-review-poll-result-button" style="display:none" data-toggle_txt="' . $result_hide_txt . '">' . $result_show_txt . '</button>';
        }

        if ( $params['show_count'] ) {
            $output .= '    <div class="expert-review-poll__count">';
            $output .= __( 'Voted', Plugin::TEXT_DOMAIN );
            $output .= ': <span class="js-expert-review-poll-count">' . $total . '</span></div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function _handle_poll_shortcode( $data, $postarr ) {
        $post_id   = $postarr['ID'];
        $orig_data = $data;

        if ( $data['post_type'] === self::POST_TYPE ) {
            return $orig_data;
        }
        if ( $data['post_status'] !== 'publish' ) {
            return $orig_data;
        }

        $data    = wp_unslash( $data );
        $content = $data['post_content'];

        $this->clean_poll_post_links( $post_id );

        if ( false === strpos( $content, '[' ) ) {
            return $orig_data;
        }

        $content = $this->update_shortcode( $content, $post_id );

        $orig_data['post_content'] = wp_slash( $content );

        return $orig_data;
    }

    /**
     * @param string $content
     * @param int    $post_id
     * @param bool   $ignore_html
     *
     * @return string|string[]|null
     * @see do_shortcode()
     */
    public function update_shortcode( $content, $post_id, $ignore_html = false ) {

        // Find all registered tag names in $content.
        preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
        $tagnames = array_intersect( [ Shortcodes::SHORTCODE_POLL ], $matches[1] );

        if ( empty( $tagnames ) ) {
            return $content;
        }

        $content = do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames );

        $pattern = get_shortcode_regex( $tagnames );
        $content = preg_replace_callback( "/$pattern/", function ( $m ) use ( $post_id ) {
            $atts = shortcode_parse_atts( $m[3] );

            $params  = get_post_meta( $atts['id'], self::META_POLL_PARAMS, true );
            $answers = get_post_meta( $atts['id'], self::META_POLL_ANSWERS, true ) ?: [];

            if ( ! $params ) {
                return $m[0];
            }

            $answers = array_map( function ( $item ) {
                unset( $item['votes'] );

                return $item;
            }, $answers );

            $params['answers'] = $answers;

            $shortcode = Shortcodes::SHORTCODE_POLL;
            $params    = Shortcodes::serialize_shortcode_params( $params );

            $this->save_poll_post_links( $post_id, $atts['id'] );

            return "[$shortcode id=\"{$atts['id']}\" params=\"$params\"]";
        }, $content );

        // Always restore square braces so we don't break things like <!--[if IE ]>
        $content = unescape_invalid_shortcodes( $content );

        return $content;
    }

    /**
     * @return void
     */
    protected function register_post_type() {
        add_action( 'init', function () {
            $labels = [
                'name'          => __( 'Polls', Plugin::TEXT_DOMAIN ),
                'singular_name' => __( 'Poll', Plugin::TEXT_DOMAIN ),
                'menu_name'     => __( 'Polls', Plugin::TEXT_DOMAIN ),
                'all_items'     => __( 'All Polls', Plugin::TEXT_DOMAIN ),
                'add_new'       => __( 'Add new', Plugin::TEXT_DOMAIN ),
                'add_new_item'  => __( 'Add new Poll', Plugin::TEXT_DOMAIN ),
                'edit_item'     => __( 'Poll', Plugin::TEXT_DOMAIN ),
            ];
            register_post_type( self::POST_TYPE, [
                'label'                 => __( 'Poll', Plugin::TEXT_DOMAIN ),
                'menu_icon'             => 'dashicons-list-view',
                'menu_position'         => 110,
                'labels'                => $labels,
                'description'           => '',
                'public'                => false,
                'publicly_queryable'    => false,
                'show_ui'               => true,
                'delete_with_user'      => false,
                'show_in_rest'          => false,
                'rest_base'             => '',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
                'has_archive'           => self::POST_TYPE,
                //                'show_in_menu'          => 'edit.php?post_type=' . self::POST_TYPE,
                'show_in_menu'          => self::POST_TYPE,
                'show_in_nav_menus'     => true,
                'exclude_from_search'   => true,
                'capability_type'       => 'page',
                'map_meta_cap'          => true,
                'capabilities'          => [
                    'create_posts' => 'do_not_allow',
                ],
                'hierarchical'          => false,
                'rewrite'               => [ 'slug' => 'expert_review_poll', 'with_front' => true ],
                'query_var'             => true,
                'supports'              => [
                    'page-attributes',
                ],
                'feed'                  => false,
            ] );
        } );

        add_action( 'admin_menu', function () {
            remove_meta_box( 'submitdiv', self::POST_TYPE, 'side' );
            remove_meta_box( 'pageparentdiv', self::POST_TYPE, 'side' );
        } );


        $type = self::POST_TYPE;
        add_filter( "manage_{$type}_posts_columns", function ( $cols ) {
            $additional = [
                'info'     => __( 'Info', Plugin::TEXT_DOMAIN ),
                'actions'  => __( 'Actions', Plugin::TEXT_DOMAIN ),
                'in_posts' => __( 'In Posts', Plugin::TEXT_DOMAIN ),
            ];

            return array_slice( $cols, 0, 2, true ) + $additional + array_slice( $cols, 2, null, true );
        } );

        add_filter( "manage_{$type}_posts_custom_column", function ( $column, $id ) {
            switch ( $column ) {
                case 'info':
                    $answers = $this->get_answers_with_percent( $id );
                    $total   = (int) get_post_meta( $id, self::META_POLL_TOTAL_VOTES, true );
                    echo '<ul>';
                    foreach ( $answers as $item ) {
                        $answer_length = 75;
                        $text          = wp_strip_all_tags( $item['text'] );
                        echo '<li>' .
                             '<span style="color: #bbb">' .
                             "[{$item['votes']} / {$item['percent']}%" .
                             ']</span> ';
                        echo mb_strlen( $item['text'], 'UTF-8' ) < $answer_length ? $item['text'] : ( mb_substr( $text, 0, $answer_length, 'UTF-8' ) . '...' );
                        echo '</li>';
                    }
                    echo '</ul>';
                    echo __( 'Total Votes', Plugin::TEXT_DOMAIN ) . ': ' . $total;
                    break;
                case 'actions':
                    $hidden   = ! get_post_meta( $id, self::META_POLL_DISPLAY, true );
                    $can_vote = get_post_meta( $id, self::META_POLL_CAN_VOTE, true );

                    $style = $hidden ? 'style="display: none"' : '';
                    echo '<a href="#" class="js-expert-review-poll" data-action="hide" data-id="' . $id . '" ' . $style . '><span class="dashicons dashicons-hidden"></span> ' . __( 'hide', Plugin::TEXT_DOMAIN ) . '</a>';
                    $style = ! $hidden ? 'style="display: none"' : '';
                    echo '<a href="#" class="js-expert-review-poll" data-action="display" data-id="' . $id . '" ' . $style . '><span class="dashicons dashicons-visibility"></span> ' . __( 'display', Plugin::TEXT_DOMAIN ) . '</a>';
                    echo '<br>';
                    $style = ! $can_vote ? 'style="display: none"' : '';
                    echo '<a href="#" class="js-expert-review-poll" data-action="pause" data-id="' . $id . '" ' . $style . '><span class="dashicons dashicons-controls-pause"></span> ' . __( 'pause voting', Plugin::TEXT_DOMAIN ) . '</a>';
                    $style = $can_vote ? 'style="display: none"' : '';
                    echo '<a href="#" class="js-expert-review-poll" data-action="resume" data-id="' . $id . '" ' . $style . '><span class="dashicons dashicons-controls-play"></span> ' . __( 'resume voting', Plugin::TEXT_DOMAIN ) . '</a>';
                    echo '<br>';
                    echo '<a href="#" class="js-expert-review-poll" data-action="reset" data-id="' . $id . '"><span class="dashicons dashicons-image-rotate"></span> ' . __( 'reset voting', Plugin::TEXT_DOMAIN ) . '</a>';
                    break;
                case 'in_posts':
                    $posts = get_post_meta( $id, self::META_LINK_POSTS, true );
                    $posts = wp_parse_id_list( $posts );
                    $posts = array_map( function ( $id ) {
                        return '<a href="' . get_edit_post_link( $id ) . '">' . $id . '</a>';
                    }, $posts );
                    echo implode( ', ', $posts );
                    break;
                default:
                    break;
            }
        }, 10, 2 );


        // exclude from Appearance > Menu page
        add_filter( 'nav_menu_meta_box_object', function ( $type ) {
            if ( ! wp_doing_ajax() && $type instanceof \WP_Post_Type && $type->name === self::POST_TYPE ) {
                return null;
            }

            return $type;
        } );
    }

    /**
     * @return void
     */
    protected function setup_ajax() {
        $action = 'expert_review_save_poll_vote';
        add_action( "wp_ajax_{$action}", [ $this, 'save_vote_ajax' ] );
        add_action( "wp_ajax_nopriv_{$action}", [ $this, 'save_vote_ajax' ] );

        $action = 'expert_review_save_poll';
        add_action( "wp_ajax_{$action}", [ $this, 'save_poll_ajax' ] );

        $action = 'expert_review_get_poll';
        add_action( "wp_ajax_{$action}", [ $this, 'get_poll_ajax' ] );

        $action = 'expert_review_update_poll_state';
        add_action( "wp_ajax_{$action}", [ $this, 'change_poll_state_ajax' ] );
    }

    /**
     * @return void
     */
    public function change_poll_state_ajax() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( empty( $_REQUEST['act'] ) || empty( $_REQUEST['poll_id'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to update poll.', Plugin::TEXT_DOMAIN ) ] );
        }

        switch ( $_REQUEST['act'] ) {
            case 'hide':
                $updated = update_post_meta( $_REQUEST['poll_id'], self::META_POLL_DISPLAY, 0 );
                break;
            case 'display':
                $updated = update_post_meta( $_REQUEST['poll_id'], self::META_POLL_DISPLAY, 1 );
                break;
            case 'pause':
                $updated = update_post_meta( $_REQUEST['poll_id'], self::META_POLL_CAN_VOTE, 0 );
                break;
            case 'resume':
                $updated = update_post_meta( $_REQUEST['poll_id'], self::META_POLL_CAN_VOTE, 1 );
                break;
            case 'reset':
                $updated = update_post_meta( $_REQUEST['poll_id'], self::META_POLL_RESET, time() );
                break;
            default:
                $updated = false;
                break;
        }

        if ( $updated ) {
            wp_send_json_success();
        }

        wp_send_json_error( [ 'message' => __( 'Poll metadata was not updated.', Plugin::TEXT_DOMAIN ) ] );
    }

    /**
     * @return void
     */
    public function save_poll_ajax() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( empty( $_POST['params'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to save poll without params', Plugin::TEXT_DOMAIN ) ] );
        }

        $params      = $_POST['params'];
        $params      = is_array( $params ) ? $params : [];
        $params      = wp_parse_args( $params, $this->default_params );
        $new_answers = $params['answers'];
        unset( $params['answers'] );

        $update_answers = [];
        foreach ( $new_answers as $item ) {
            if ( empty( $item['votes'] ) ) {
                $item['votes'] = 0;
            }
            $update_answers[] = $item;
        }
        $new_answers = $update_answers;

        $total_votes = array_reduce( $new_answers, function ( $carry, $item ) {
            return $carry + intval( $item['votes'] );
        }, 0 );


        if ( ! empty( $_POST['current'] ) && ( $poll = get_post( $_POST['current'] ) ) ) {
            $poll_id = $poll->ID;
            $answers = get_post_meta( $poll_id, self::META_POLL_ANSWERS, true ) ?: [];
            if ( ! $this->is_answers_equals( $answers, $new_answers ) || ! empty( $_POST['force_update_answers'] ) ) {
                if ( empty( $_POST['confirm_update'] ) ) {
                    wp_send_json_error( new \WP_Error(
                        'same_poll_with_new_answers',
                        sprintf(
                            __( 'Are you sure you want to change answers of poll with id %d? This will change answers in other polls on this page with this id when you update the post.', Plugin::TEXT_DOMAIN ),
                            $poll_id
                        )
                    ) );
                } else {
                    update_post_meta( $poll_id, self::META_POLL_ANSWERS, $new_answers );
                    update_post_meta( $poll_id, self::META_POLL_TOTAL_VOTES, $total_votes );
                    update_post_meta( $poll_id, self::META_POLL_RESET, time() );
                }
            }
            if ( empty( $_POST['update_answers_only'] ) ) {
                update_post_meta( $poll_id, self::META_POLL_PARAMS, $params );
            }
        } else {
            $poll_id = $this->create_post( $params['title'] );
            if ( is_wp_error( $poll_id ) ) {
                wp_send_json_error( [ 'message' => __( 'Unable to save poll', Plugin::TEXT_DOMAIN ) ] );
            }

            update_post_meta( $poll_id, self::META_POLL_PARAMS, $params );
            update_post_meta( $poll_id, self::META_POLL_ANSWERS, $new_answers );
            update_post_meta( $poll_id, self::META_POLL_TOTAL_VOTES, 0 );
        }

        if ( ! empty( $_POST['post_id'] ) ) {
            $this->save_poll_post_links( $_POST['post_id'], $poll_id );
        }

        wp_send_json_success( [ 'post_id' => $poll_id ] );
    }

    /**
     * @param int $post_id
     * @param int $poll_id
     *
     * @return void
     */
    protected function save_poll_post_links( $post_id, $poll_id ) {
        $save_ids = function ( $target, $id, $meta_key ) {
            $ids   = get_post_meta( $target, $meta_key, true ) ?: '';
            $ids   = wp_parse_id_list( $ids );
            $ids[] = $id;
            $ids   = array_unique( $ids );
            update_post_meta( $target, $meta_key, implode( ',', $ids ) );
        };

        $save_ids( $post_id, $poll_id, self::META_LINK_POLLS );
        $save_ids( $poll_id, $post_id, self::META_LINK_POSTS );
    }

    /**
     * @param int $post_id
     *
     * @return void
     */
    protected function clean_poll_post_links( $post_id ) {
        $poll_ids = get_post_meta( $post_id, self::META_LINK_POLLS, true );
        $poll_ids = wp_parse_id_list( $poll_ids );
        foreach ( $poll_ids as $poll_id ) {
            $post_ids = get_post_meta( $poll_id, self::META_LINK_POSTS, true );
            $post_ids = wp_parse_id_list( $post_ids );
            $post_ids = array_filter( $post_ids, function ( $id ) use ( $post_id ) {
                return $id != $post_id;
            } );
            update_post_meta( $poll_id, self::META_LINK_POSTS, implode( ',', $post_ids ) );
        }
        delete_post_meta( $post_id, self::META_LINK_POLLS );
//        update_post_meta( $post_id, self::META_LINK_POLLS, '' );
    }

    /**
     * @param array $current
     * @param array $answers
     * @param bool  $strict
     *
     * @return bool
     */
    protected function is_answers_equals( $current, $answers, $strict = false ) {
        $current = array_map( function ( $item ) {
            $votes = 0;//intval( $item['votes'] );

            return "{$item['text']}:$votes";
        }, $current );
        $answers = array_map( function ( $item ) {
            $votes = 0;//intval( $item['votes'] );

            return "{$item['text']}:$votes";
        }, $answers );

        if ( count( $current ) != count( $answers ) ) {
            return false;
        }

        sort( $current, $strict ? SORT_STRING : SORT_STRING | SORT_FLAG_CASE );
        sort( $answers, $strict ? SORT_STRING : SORT_STRING | SORT_FLAG_CASE );

        for ( $i = 0 ; $i < count( $current ) ; $i ++ ) {
            if ( 0 !== call_user_func( $strict ? 'strcmp' : 'strcasecmp', $current[ $i ], $answers[ $i ] ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return void
     */
    public function save_vote_ajax() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( ! ( $post = get_post( $_POST['question'] ) ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to save poll data', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( ! get_post_meta( $post->ID, self::META_POLL_CAN_VOTE, true ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to save vote, voting is stopped', Plugin::TEXT_DOMAIN ) ] );
        }

        $total   = get_post_meta( $post->ID, self::META_POLL_TOTAL_VOTES, true ) ?: 0;
        $answers = get_post_meta( $post->ID, self::META_POLL_ANSWERS, true ) ?: [];
        $votes   = [];

        $vote_updated = false;
        foreach ( $answers as &$item ) {
            if ( $item['id'] == $_POST['answer'] ) {
                $item['votes'] = $item['votes'] + 1;
                $vote_updated  = true;
            }
            $votes[ $item['id'] ] = $item['votes'];
        }

        if ( $vote_updated ) {
            $total ++;
            update_post_meta( $post->ID, self::META_POLL_TOTAL_VOTES, $total );
            update_post_meta( $post->ID, self::META_POLL_ANSWERS, $answers );
        }

        wp_send_json_success( [
            'votes' => $votes,
            'total' => $total,
        ] );
    }

    /**
     * @return void
     */
    public function get_poll_ajax() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( ! ( $post = get_post( $_POST['id'] ) ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to retrieve poll data', Plugin::TEXT_DOMAIN ) ] );
        }

        $params            = get_post_meta( $post->ID, self::META_POLL_PARAMS, true );
        $params['answers'] = get_post_meta( $post->ID, self::META_POLL_ANSWERS, true ) ?: [];

        wp_send_json_success( [ 'params' => $params ] );
    }

    /**
     * @param string $title
     * @param array  $meta_input
     *
     * @return int|\WP_Error
     */
    protected function create_post( $title, array $meta_input = [] ) {
        $meta_input = wp_parse_args( $meta_input, [
            self::META_POLL_DISPLAY  => 1,
            self::META_POLL_CAN_VOTE => 1,
        ] );

        return wp_insert_post( [
            'post_title'  => $title,
            'post_status' => 'publish',
            'post_type'   => self::POST_TYPE,
            'meta_input'  => $meta_input,
        ] );
    }

    /**
     * @param int $id post id
     *
     * @return array
     */
    public function get_answers_with_percent( $id ) {
        $answers = get_post_meta( $id, self::META_POLL_ANSWERS, true ) ?: [];
        $total   = get_post_meta( $id, self::META_POLL_TOTAL_VOTES, true );
        foreach ( $answers as &$item ) {
            $item['percent'] = round( $item['votes'] / ( $total ? $total : ( $item['votes'] ? $item['votes'] : 1 ) ) * 100, 2 );
        }

        return $answers;
    }
}
