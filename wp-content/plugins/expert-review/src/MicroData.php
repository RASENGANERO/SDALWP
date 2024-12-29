<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Settings\AdvancedOptions;

class MicroData {

    /**
     * @var array
     */
    protected $qa = [];

    /**
     * @var AdvancedOptions
     */
    protected $advanced_options;

    /**
     * MicroData constructor.
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
        add_action( 'expert_review_questions_and_answers', [ $this, '_gather_qa' ] );

        if ( $this->advanced_options->use_json_ld_faq_microdata ) {
            add_action( 'wp_head', [ $this, '_output_faq_json_ld' ] );
        } else {
            //add_action( 'wp_footer', [ $this, '_insert_question_micro_data' ] );
        }

        do_action( __METHOD__, $this );
    }



    /**
     * @param int $post_id
     *
     * @return array|null
     */
    protected function get_user_interaction_json_ld( $post_id ) {
        $interaction_statistic = [];
        if ( $likes = get_post_meta( $post_id, 'expert_review_likes', true ) ) {
            $interaction_statistic[] = [
                '@type'                => 'InteractionCounter',
                'interactionType'      => 'https://schema.org/LikeAction',
                'userInteractionCount' => $likes,
            ];
        }
        if ( $dislikes = get_post_meta( $post_id, 'expert_review_dislikes', true ) ) {
            $interaction_statistic[] = [
                '@type'                => 'InteractionCounter',
                'interactionType'      => 'https://schema.org/DislikeAction',
                'userInteractionCount' => $dislikes,
            ];
        }
        if ( $interaction_statistic ) {
            return [
                '@context'             => 'https://schema.org',
                '@type'                => 'Article',
                'interactionStatistic' => $interaction_statistic,
            ];
        }

        return null;
    }

    /**
     * @return void
     */
    public function _output_faq_json_ld() {
        $tags_method_map = [
            'expert_review'     => 'retrieve_qa',
            'expert_review_faq' => 'retrieve_qa',
        ];

        $qa_data = [];
        $this->walk_shortcodes(
            array_keys( $tags_method_map ),
            function ( $tag, $atts, $content = null ) use ( &$qa_data, $tags_method_map ) {
                if ( ! array_key_exists( $tag, $tags_method_map ) ) {
                    // @todo throw exception?
                    return;
                }

                $method = $tags_method_map[ $tag ];
                foreach ( $qa = $this->$method( $atts, $content ) as $key => $item ) {
                    $qa_data[ $key ] = $item;
                }
            }
        );

        if ( ! $qa_data ) {
            return;
        }

        $result = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => [],
        ];
        foreach ( $qa_data as $qa ) {
            $result['mainEntity'][] = [
                '@type'          => 'Question',
                'name'           => wp_strip_all_tags( $qa[0] ),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => strip_tags( $qa[1], '<h1><h2><h3><h4><h5><h6><br><ol><ul><li><a><p><b><strong><i><em>' ),
                ],
            ];
        }

        echo '<script type="application/ld+json">';
        echo json_encode( $result );
        echo '</script>';
    }

    /**
     * @param array $atts
     *
     * @return array
     */
    protected function retrieve_qa( $atts ) {
        if ( empty( $atts['params'] ) ) {
            return [];
        }
        $params = Shortcodes::unserialize_shortcode_params( $atts['params'] );
        if ( empty( $params['qa'] ) ) {
            return [];
        }
        $result = [];
        foreach ( $params['qa'] as $item ) {
            $result[ md5( $item['q'] . $item['a'] ) ] = [ $item['q'], $item['a'] ];
        }

        return $result;
    }

    /**
     * @param array    $tag_names
     * @param callable $callback
     *
     * @see do_shortcode_tag()
     * @see do_shortcode()
     */
    protected function walk_shortcodes( array $tag_names, $callback ) {
        if ( ( $obj = get_queried_object() ) ) {
            if ( $obj instanceof \WP_Post ) {
                $content_to_check = $obj->post_content;
            } else if ( ! ( $content_to_check = apply_filters( 'expert_review:walk_shortcodes_content', '', $obj ) ) ) {
                return;
            }

            $tagnames = $tag_names;
            $pattern  = get_shortcode_regex( $tag_names );
            if ( ! has_filter( 'expert_review:walk_shortcodes_contents' ) ) {
                if ( false === strpos( $content_to_check, '[' ) ) {
                    return;
                }

                preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content_to_check, $matches );
                $tagnames = array_intersect( $tag_names, $matches[1] );

                if ( empty( $tagnames ) ) {
                    return;
                }

                $pattern = get_shortcode_regex( $tagnames );
            }

            $content = do_shortcodes_in_html_tags( $content_to_check, false, $tagnames );

            $content_arr = apply_filters( 'expert_review:walk_shortcodes_contents', [ $content ], $obj );

            foreach ( $content_arr as $content ) {
                preg_replace_callback( "/$pattern/", function ( $m ) use ( $callback ) {
                    // allow [[foo]] syntax for escaping a tag
                    if ( $m[1] == '[' && $m[6] == ']' ) {
                        return substr( $m[0], 1, - 1 );
                    }

                    $tag     = $m[2];
                    $atts    = shortcode_parse_atts( $m[3] );
                    $content = isset( $m[5] ) ? $m[5] : null;

                    return call_user_func( $callback, $tag, $atts, $content );
                }, $content );
            }
        }
    }

    /**
     * @param $item
     */
    public function _gather_qa( $item ) {
        if ( ! is_array( $item ) || count( $item ) < 2 ) {
            return;
        }
        list( $question, $answer ) = $item;

        if ( ! $question || ! $answer ) {
            return;
        }

        $this->qa[ md5( $question . $answer ) ] = $item;
    }

    /**
     * @return void
     * @deprecated
     */
    public function _insert_question_micro_data() {
        if ( ! $this->qa ) {
            return;
        }

        $qa = apply_filters( 'expert_review_microdata_qa_data', $this->qa );

        $output = '<!-- hidden qa microdata -->';
        $output .= '<div style="display: none;" itemscope itemtype="https://schema.org/FAQPage">';
        foreach ( $qa as $item ) {
            list( $question, $answer ) = $item;
            $question = wp_strip_all_tags( $question );
            $answer   = strip_tags( $answer, '<h1><h2><h3><h4><h5><h6><br><ol><ul><li><a><p><b><strong><i><em>' );

            $output .= '<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
            $output .= '    <div itemprop="name">' . $question . '</div>';
            $output .= '    <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
            $output .= '        <span itemprop="text">' . $answer . '</span>';
            $output .= '    </div>';
            $output .= '</div>';
        }

        $output = apply_filters( 'expert_review_generate_qa_microdata_item', $output );

        $output .= '</div><!-- /hidden qa microdata -->';

        echo $output;
    }
}
