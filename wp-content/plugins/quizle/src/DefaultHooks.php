<?php

namespace Wpshop\Quizle;

use Wpshop\Quizle\Admin\Settings;

class DefaultHooks {

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
     * @return void
     */
    public function init() {
        add_filter( 'quizle/shortcode/do_output', [ $this, '_disable_shortcode_in_feed' ] );
        add_filter( 'quizle/shortcode/do_output', [ $this, '_check_verify' ], PHP_INT_MAX );
        add_action( 'save_post', [ $this, '_generate_name' ], 10, 2 );

        add_filter( 'quizle/welcome/description', 'nl2br' );

        add_filter( 'quizle/contacts/description', 'nl2br' );
        add_filter( 'quizle/contacts/message', 'nl2br' );
        add_filter( 'quizle/contacts/message', 'do_shortcode' );

        add_filter( 'quizle/question/title', 'nl2br' );
        add_filter( 'quizle/question/description', 'nl2br' );
        add_filter( 'quizle/question/right_answer_description', 'nl2br' );
        add_filter( 'quizle/answer/description', 'nl2br' );

        add_filter( 'quizle/result/title', 'wp_kses_post' );
        add_filter( 'quizle/result/description', 'wp_kses_post' );

//        add_action( 'wp_head', [ $this, '_add_result_page_meta' ] );

        $this->setup_quizle_templates();
    }

    /**
     * @return void
     */
    public function _add_result_page_meta() {
        if ( ! is_quizle_result_page() ) {
            return;
        }

        if ( ( $result = get_result_by_token( $_REQUEST[ RESULT_REQUEST_VAR ] ) ) &&
             ( $result_item = $result->get_result_item() )
        ) {
            $title_parts = [];
            if ( $quizle = get_post( $result->quiz_id ) ) {
                $title_parts[] = get_the_title( $quizle );
            } else {
                $title_parts[] = __( 'Quizle Result', QUIZLE_TEXTDOMAIN );
            }
            $title_parts[] = $result_item['title'];
            $title_parts[] = get_option( 'blogname' );
            $title_parts   = array_filter( array_map( 'trim', $title_parts ) );
            $title_parts   = (array) apply_filters( 'quizle/result_page/title_parts', $title_parts );

            if ( $title_parts ) {
                echo '<meta property="og:title" content="' . implode( ' — ', $title_parts ) . '"/>';
            }
            if ( $result_item['image'] ) {
                echo '<meta property="og:image" content="' . esc_attr( $result_item['image'] ) . '"/>';
            }
        }
    }

    /**
     * @return void
     * @see get_query_template()
     */
    protected function setup_quizle_templates() {
        // todo check verification?

        $quizle_template = dirname( QUIZLE_FILE ) . '/template-parts/single-quizle.php';

        add_filter( 'single_template', function ( $template ) use ( $quizle_template ) {
            global $post;

            if ( Quizle::POST_TYPE === $post->post_type ) {
                // in case when quizle-single.php or quizle/single-quizle.php not exists in a theme
                // or used block template
                if ( \locate_template( 'single.php' ) === $template ||
                     str_replace( ABSPATH . WPINC, '', $template ) === '/template-canvas.php'
                ) {
                    $template = $quizle_template;
                }
            }

            return $template;
        } );

        add_filter( 'single_template_hierarchy', function ( $templates ) {
            global $post;

            if ( Quizle::POST_TYPE === $post->post_type ) {
                array_unshift( $templates, 'quizle/single-quizle.php' );
            }

            return $templates;
        } );

        add_filter( 'template_include', function ( $template ) use ( $quizle_template ) {
            if ( is_quizle_result_page() && ( $located_template = locate_template( 'quizle-result.php' ) ) ) {
                $template = $located_template;
            }

            return $template;
        } );
    }

    /**
     * @param bool $result
     *
     * @return bool
     */
    public function _disable_shortcode_in_feed( $result ) {
        if ( is_feed() ) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param bool $result
     *
     * @return bool
     */
    public function _check_verify( $result ) {
        if ( ! $this->settings->verify() ) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int      $post_id
     * @param \WP_Post $post
     *
     * @return void
     */
    public function _generate_name( $post_id, $post ) {
        if ( $post->post_type !== Quizle::POST_TYPE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! $post->post_name && ( $name = generate_quizle_name() ) ) {
            remove_action( 'save_post', [ $this, '_generate_name' ] );
            wp_update_post( [
                'ID'        => $post_id,
                'post_name' => $name,
            ] );
            add_action( 'save_post', [ $this, '_generate_name' ], 10, 2 );
        }
    }
}
