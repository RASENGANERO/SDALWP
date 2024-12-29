<?php

namespace Wpshop\ExpertReview;

use WP_Comment;
use WP_Post;
use Wpshop\ExpertReview\Settings\LikeOptions;

class Likes {

    const OPTION_PREFIX = '_expert_review_like_data:';

    const POST_META_LIKES    = 'expert_review_likes';
    const POST_META_DISLIKES = 'expert_review_dislikes';
    const POST_META_ACTIVITY = 'expert_review_activity';
    const POST_META_RATE     = 'expert_review_like_rate';

    const COMMENT_META_LIKES    = 'expert_review_likes';
    const COMMENT_META_DISLIKES = 'expert_review_dislikes';
    const COMMENT_META_ACTIVITY = 'expert_review_activity';
    const COMMENT_META_RATE     = 'expert_review_like_rate';

    /**
     * @var LikeOptions
     */
    protected $like_options;

    /**
     * Likes constructor.
     *
     * @param LikeOptions $like_options
     */
    public function __construct( LikeOptions $like_options ) {
        $this->like_options = $like_options;
    }

    /**
     * @return void
     */
    public function init() {
        $this->setup_ajax();
        add_filter( 'the_content', [ $this, '_add_likes_to_content' ], 11 );
        add_filter( 'the_content', [ $this, '_add_likes_microdata' ], 12 );
        add_filter( 'comment_text', [ $this, '_add_likes_to_comments' ], 10, 3 );
        add_filter( 'expert_review:named_likes_identity', [ $this, '_default_likes_name' ], 10, 2 );

        do_action( __METHOD__, $this );
    }

    /**
     * @param string $content
     *
     * @return string
     * @throws \Exception
     */
    public function _add_likes_microdata( $content ) {
        if ( is_admin() ||
             $this->like_options->microdata_type != 'schema' ||
             ! get_queried_object() instanceof WP_Post
        ) {
            return $content;
        }

        $post_id = get_queried_object_id();

        if ( apply_filters( 'expert_review/likes_microdata/output_likes', $this->like_options->microdata_likes ) &&
             ( $likes = get_post_meta( $post_id, 'expert_review_likes', true ) )
        ) {
            $content .= er_ob_get_content( function () use ( $likes ) {
                ?>
                <div itemprop="interactionStatistic" itemscope itemtype="https://schema.org/InteractionCounter">
                    <meta itemprop="interactionType" content="https://schema.org/LikeAction">
                    <meta itemprop="userInteractionCount" content="<?php echo $likes ?>">
                </div>
                <?php
            } );
        }

        if ( apply_filters( 'expert_review/likes_microdata/output_dislikes', $this->like_options->microdata_dislikes ) &&
             ( $dislikes = get_post_meta( $post_id, 'expert_review_dislikes', true ) )
        ) {
            $content .= er_ob_get_content( function () use ( $dislikes ) {
                ?>
                <div itemprop="interactionStatistic" itemscope itemtype="https://schema.org/InteractionCounter">
                    <meta itemprop="interactionType" content="https://schema.org/DislikeAction">
                    <meta itemprop="userInteractionCount" content="<?php echo $dislikes ?>">
                </div>
                <?php
            } );
        }

        return $content;
    }

    /**
     * @param string $name
     * @param array  $atts
     *
     * @return string
     */
    public function _default_likes_name( $name, $atts ) {
        if ( ! empty( $atts['post_id'] ) ) {
            return "{$name}:{$atts['post_id']}";
        }

        return $name;
    }

    /**
     * @param string     $text
     * @param WP_Comment $comment
     * @param array      $args
     *
     * @return string
     * @see comment_text()
     */
    public function _add_likes_to_comments( $text, WP_Comment $comment = null, $args = [] ) {
        if ( is_admin() ) {
            return $text;
        }

        $likes = '';
        if ( $comment &&
             ( $like_content = $this->like_options->comment_likes_content ) &&
             $this->like_options->likes_for_comment
        ) {
            $shortcode = Shortcodes::SHORTCODE_LIKES;
            $likes     = preg_replace( "@\[$shortcode@", "[$shortcode comment_id=\"{$comment->comment_ID}\"", $like_content );
            $likes     = apply_filters( 'expert_review_comment_likes', do_shortcode( $likes ), $comment, $args );
        }


        return $text . $likes;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function _add_likes_to_content( $content ) {

        if ( ! empty( $GLOBALS['wp_current_filter'] ) && in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'] ) ) {
            return $content;
        }

        global $post;
        if ( ! $post ) {
            $post = get_queried_object();
        }
        if ( $post instanceof WP_Post ) {
            return $this->attach_likes( $content, $post );
        }

        return $content;
    }

    /**
     * @param string  $content
     * @param WP_Post $post
     *
     * @return string
     */
    protected function attach_likes( $content, $post ) {
        $post_types = apply_filters( 'expert_review_like_post_types', [ 'post' ] );

        if ( $this->like_options->likes_content &&
             in_array( $post->post_type, $post_types )
        ) {

            if ( $this->like_options->exclude_post_ids ) {
                $exclude = wp_parse_id_list( $this->like_options->exclude_post_ids );
                if ( in_array( $post->ID, $exclude ) ) {
                    return $content;
                }
            }

            if ( trim( $this->like_options->exclude_post_categories ) ) {
                $exclude_cats = array_filter(
                    array_map(
                        'trim',
                        preg_split( "/[\n,]+/", $this->like_options->exclude_post_categories, - 1, PREG_SPLIT_NO_EMPTY )
                    )
                );
                if ( ! empty( $exclude_cats ) ) {
                    $categories = wp_get_post_categories( $post->ID, [ 'fields' => 'all' ] );
                    if ( is_wp_error( $categories ) ) {
                        // @todo log wp error
                        return $content;
                    } else {
                        $categories = array_map( function ( \WP_Term $item ) {
                            return $item->name;
                        }, $categories );

                        if ( array_intersect( $exclude_cats, $categories ) ) {
                            return $content;
                        }
                    }
                }
            }


            $likes = do_shortcode( $this->like_options->likes_content );
            if ( $this->like_options->likes_before_content ) {
                $content = $likes . $content;
            }
            if ( $this->like_options->likes_after_content ) {
                $content .= $likes;
            }
        }

        return $content;
    }

    /**
     * @return void
     */
    protected function setup_ajax() {
        $save_like_action = 'expert_review_save_like';
        add_action( "wp_ajax_{$save_like_action}", [ $this, 'save_like_ajax' ] );
        add_action( "wp_ajax_nopriv_{$save_like_action}", [ $this, 'save_like_ajax' ] );
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public static function get_named_like_data( $name ) {
        return get_option( self::OPTION_PREFIX . $name, [
            'likes'    => 0,
            'dislikes' => 0,
            'activity' => 0,
            'rate'     => 0,
        ] );
    }

    /**
     * @param string $name
     * @param array  $data
     *
     * @return bool
     */
    public static function update_named_like_data( $name, array $data ) {
        return update_option( self::OPTION_PREFIX . $name, $data );
    }

    /**
     * @return void
     */
    public function save_like_ajax() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( ! isset( $_POST['entity'], $_POST['is_named'], $_POST['id'], $_POST['type'], $_POST['is_new'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to handle request without required data', Plugin::TEXT_DOMAIN ) ] );
        }

        switch ( $_POST['entity'] ) {
            case Shortcodes::LIKE_ENTITY_POSTS:
                $this->save_post_likes();
                break;
            case Shortcodes::LIKE_ENTITY_COMMENTS:
                $this->save_comment_likes();
                break;
            default:
                wp_send_json_error( [ 'message' => __( 'Unable to handle request due to not supported entity', Plugin::TEXT_DOMAIN ) ] );
                break;
        }

    }

    /**
     * @return void
     */
    protected function save_post_likes() {
        if ( $_REQUEST['is_named'] ) {
            $data = static::get_named_like_data( $_REQUEST['id'] );

            list( $likes, $dislikes ) = $this->process_likes( $data['likes'], $data['dislikes'] );

            $data['likes']    = $likes;
            $data['dislikes'] = $dislikes;
            $data['activity'] = $data['likes'] + $data['dislikes'];
            $data['rate']     = $data['likes'] - $data['dislikes'];

            static::update_named_like_data( $_REQUEST['id'], $data );

            $params         = compact( 'likes', 'dislikes' );
            $params['name'] = $_REQUEST['id'];
            do_action( 'expert_review_success_save_named_likes', $params );

            wp_send_json_success( [
                'likes'    => $data['likes'],
                'dislikes' => $data['dislikes'],
                'rate'     => $data['rate'],
                'activity' => $data['activity'],
            ] );

        } else if ( $post_id = $_REQUEST['id'] ) {
            $likes    = (int) get_post_meta( $post_id, self::POST_META_LIKES, true );
            $dislikes = (int) get_post_meta( $post_id, self::POST_META_DISLIKES, true );

            list( $likes, $dislikes ) = $this->process_likes( $likes, $dislikes );

            update_post_meta( $post_id, self::POST_META_LIKES, $likes );
            update_post_meta( $post_id, self::POST_META_DISLIKES, $dislikes );
            update_post_meta( $post_id, self::POST_META_ACTIVITY, $likes + $dislikes );
            update_post_meta( $post_id, self::POST_META_RATE, $likes - $dislikes );

            do_action( 'expert_review_success_save_post_likes', compact( 'post_id', 'likes', 'dislikes' ) );

            wp_send_json_success( [
                'likes'    => $likes,
                'dislikes' => $dislikes,
            ] );
        }

        wp_send_json_error( [ 'message' => __( 'Unable to update post', Plugin::TEXT_DOMAIN ) ] );
    }

    /**
     * @param int $likes
     * @param int $dislikes
     *
     * @return array
     */
    protected function process_likes( $likes, $dislikes ) {
        switch ( $_REQUEST['type'] ) {
            case 'toggle':
                if ( $_REQUEST['click_type'] === 'like' ) {
                    $likes ++;
                    if ( ! $_REQUEST['is_new'] ) {
                        $dislikes --;
                    }
                } elseif ( $_REQUEST['click_type'] === 'dislike' ) {
                    $likes --;
                }
                break;
            case 'like':
                $likes ++;
                if ( ! $_REQUEST['is_new'] ) {
                    $dislikes --;
                }
                break;
            case 'dislike':
                $dislikes ++;
                if ( ! $_REQUEST['is_new'] ) {
                    $likes --;
                }
                break;
            default:
                break;
        }

        return [ $likes, $dislikes ];
    }

    /**
     * @return void
     */
    protected function save_comment_likes() {
        if ( $comment_id = $_POST['id'] ) {
            $likes    = (int) get_comment_meta( $comment_id, self::COMMENT_META_LIKES, true );
            $dislikes = (int) get_comment_meta( $comment_id, self::COMMENT_META_DISLIKES, true );

            list( $likes, $dislikes ) = $this->process_likes( $likes, $dislikes );

            update_comment_meta( $comment_id, self::COMMENT_META_LIKES, $likes );
            update_comment_meta( $comment_id, self::COMMENT_META_DISLIKES, $dislikes );
            update_comment_meta( $comment_id, self::COMMENT_META_ACTIVITY, $likes + $dislikes );
            update_comment_meta( $comment_id, self::COMMENT_META_RATE, $likes - $dislikes );

            do_action( 'expert_review_success_save_comment_likes', compact( 'comment_id', 'likes', 'dislikes' ) );

            wp_send_json_success( [
                'likes'    => $likes,
                'dislikes' => $dislikes,
            ] );
        }

        wp_send_json_error( [ 'message' => __( 'Unable to update comment likes', Plugin::TEXT_DOMAIN ) ] );
    }
}
