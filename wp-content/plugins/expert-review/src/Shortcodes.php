<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Settings\ExpertOptions;
use Wpshop\ExpertReview\Settings\QaOptions;
use Wpshop\ExpertReview\Shortcodes\Poll;

class Shortcodes {

    const LIKE_ENTITY_POSTS    = 'posts';
    const LIKE_ENTITY_COMMENTS = 'comments';

    const SHORTCODE_EXPERT_REVIEW = 'expert_review';
    const SHORTCODE_LIKES         = 'expert_review_likes';
    const SHORTCODE_LIKES_RATE    = 'expert_review_likes_rate';
    const SHORTCODE_FAQ           = 'expert_review_faq';
    const SHORTCODE_POLL          = 'expert_review_poll';

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var MicroData
     */
    protected $micro_data;

    /**
     * @var ExpertOptions
     */
    protected $expert_options;

    /**
     * @var QaOptions
     */
    protected $qa_options;

    /**
     * @var AdvancedOptions
     */
    protected $advanced_options;

    /**
     * @var array
     */
    protected $icons = [];

    /**
     * Shortcodes constructor.
     *
     * @param Plugin          $plugin
     * @param MicroData       $micro_data
     * @param ExpertOptions   $expert_options
     * @param QaOptions       $qa_options
     * @param AdvancedOptions $advanced_options
     * @param array           $icons
     */
    public function __construct(
        Plugin $plugin,
        MicroData $micro_data,
        ExpertOptions $expert_options,
        QaOptions $qa_options,
        AdvancedOptions $advanced_options,
        $icons
    ) {
        $this->plugin           = $plugin;
        $this->micro_data       = $micro_data;
        $this->expert_options   = $expert_options;
        $this->qa_options       = $qa_options;
        $this->advanced_options = $advanced_options;
        $this->icons            = $icons;
    }

    /**
     * @return void
     */
    public function init() {
        if ( $this->plugin->verify() ) {
            add_shortcode( self::SHORTCODE_EXPERT_REVIEW, [ $this, '_expert_review' ] );
            add_shortcode( self::SHORTCODE_LIKES, [ $this, '_expert_review_likes' ] );
            add_shortcode( self::SHORTCODE_LIKES_RATE, [ $this, '_expert_review_likes_rate' ] );
            add_shortcode( self::SHORTCODE_FAQ, [ $this, '_expert_review_faq' ] );
            add_shortcode( self::SHORTCODE_POLL, [ container()->get( Poll::class ), 'shortcode' ] );
            $this->micro_data->init();
        }

        do_action( __METHOD__, $this );
    }

    /**
     * @return bool
     */
    protected function do_render_templates() {
        return (bool) apply_filters( 'expert_review_do_render_templates', $this->advanced_options->enable_templates );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function output_popup_template() {
        echo er_render_template( 'question-popup.php' );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _expert_review( $atts, $content, $shortcode ) {
//        add_action( 'wp_footer', [ $this, 'output_popup_template' ] );

        if ( ! apply_filters( 'expert_review_output_shortcodes', true, $shortcode ) ) {
            return '';
        }

        $atts = shortcode_atts( [
            'params' => '',

            'expert_show'                      => 1,
            'color'                            => 'purple-1',
            'expert_type'                      => 'self',
            'expert_id'                        => 0,
            'expert_avatar'                    => '',
            'expert_avatar_alt'                => '',
            'expert_name'                      => '',
            'expert_link'                      => '',
            'expert_description'               => '',
            'expert_text'                      => '',
            'expert_show_button'               => 1, // задать вопрос
            'expert_title'                     => '',
            'expert_show_title'                => 0,
            'qa_data'                          => [],
            'qa_title'                         => __( 'Questions to the expert', Plugin::TEXT_DOMAIN ),
            'qa_show_title'                    => 1,
            'qa_type'                          => 'popup', // legacy support
            'expert_show_button_type'          => 'popup',
            'popup_use_phone'                  => 0,
            'score_data'                       => [],
            'score_summary_text'               => '',
            'score_max'                        => 5, // todo перенести в настройки
            'score_symbol'                     => '',
            'score_summary_average'            => 1,
            'score_title'                      => '',
            'score_show_title'                 => '',
            'pluses_minuses_title'             => __( 'Plus & Minus', Plugin::TEXT_DOMAIN ),
            'pluses_minuses_show_title'        => 1,
            'pluses_minuses_show_title_mobile' => 1,
            'pluses_title'                     => '',
            'minuses_title'                    => '',
            'pluses'                           => [],
            'minuses'                          => [],
            'question_external_link'           => '',

            'expert_question_button_text' => _x( 'Ask Question', 'shortcode', Plugin::TEXT_DOMAIN ),
        ], $atts, $shortcode );

        $is_legacy = true;
        if ( ! empty( $atts['params'] ) ) {
            $is_legacy = false;
            $params    = $this->unserialize_params( $atts['params'] );
            unset( $atts['params'] );
            $atts = wp_parse_args( $params, $atts );
        } else { // legacy support
            $entity_decode = [
                'expert_title',
                'expert_description',
                'expert_text',
                'qa_title',
                'score_summary_text',
                'pluses_minuses_title',
            ];
            array_walk( $atts, function ( &$item, $key, $entity_decode ) {
                if ( in_array( $key, $entity_decode ) ) {
                    $item = html_entity_decode( $item );
                }
            }, $entity_decode );

            $atts = $this->parse_serialized_data( $atts );
        }

        $nl2br = function ( $str ) use ( $is_legacy ) {
            if ( ! $is_legacy ) {
                return nl2br( $str );
            }

            return $str;
        };


        $out = '';

        $expert_name        = '';
        $expert_description = '';
        $expert_avatar      = '';

        switch ( $atts['expert_type'] ) {
            case 'self':
                break;
            case 'user_id':
                if ( $user = get_userdata( $atts['expert_id'] ) ) {
                    $atts['expert_name'] = $user->display_name;
                    $atts['expert_link'] = '';

                    $use_link = $this->expert_options->use_user_expert_links;
                    $use_link = apply_filters( 'expert_review_show_user_expert_link', $use_link, $user );
                    if ( $use_link ) {
                        $atts['expert_link'] = get_author_posts_url( $user->ID, $user->user_nicename );
                    }

                    $atts['expert_avatar'] = apply_filters( 'expert_review:expert_avatar_url', '', $atts );
                    if ( ! $atts['expert_avatar'] ) {
                        $atts['expert_avatar'] = get_avatar_url( $user->ID );
                    }

                    if ( ! $atts['expert_description'] ) {
                        $atts['expert_description'] = $user->user_description;
                    }
                }
                break;
            case 'expert_id':
                $options = new ExpertOptions();
                if ( $item = $options->get_by_id( $atts['expert_id'] ) ) {
                    $atts['expert_avatar'] = $item['avatar'];
                    $atts['expert_name']   = $item['name'];
                    $atts['expert_link']   = $item['link'];
                    if ( ! $atts['expert_description'] ) {
                        $atts['expert_description'] = $item['description'];
                    }
                }
                break;
            default:
                break;
        }

        if ( $this->do_render_templates() ) {
            $atts['_nl2br_fn'] = $nl2br;

            return er_render_template( 'expert.php', [ 'atts' => $atts, 'content' => $content ] );
        }

        $expert_link = apply_filters( 'expert_review:expert_link', $atts['expert_link'], $atts );

        if ( $atts['expert_name'] ) {
            $expert_name = $atts['expert_name'];
            if ( $expert_link ) {
                $expert_name = '<a href="' . $expert_link . '" target="_blank"' . ( 'schema' == $this->advanced_options->expert_microdata_type ? ' itemprop="url"' : '' ) . '>' . $expert_name . '</a>';
            }
        }
        if ( $atts['expert_description'] ) {
            $expert_description = $nl2br( $atts['expert_description'] );
        }
        if ( $atts['expert_avatar'] ) {
            $avatar_alt = ! empty( $atts['expert_avatar_alt'] ) ? $atts['expert_avatar_alt'] : $atts['expert_name'];

            $expert_avatar_atts = apply_filters( 'expert_review:avatar_attributes', [
                'src' => $atts['expert_avatar'],
                'alt' => esc_attr( $avatar_alt ),
            ] );
            if ( 'schema' == $this->advanced_options->expert_microdata_type ) {
                $expert_avatar_atts['itemprop'] = 'image';
            }

            $expert_avatar_atts_str = '';
            foreach ( $expert_avatar_atts as $key => $value ) {
                $expert_avatar_atts_str .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
            }
            $expert_avatar_atts_str = trim( $expert_avatar_atts_str );

            $expert_avatar = '<img ' . $expert_avatar_atts_str . '>';
            if ( $expert_link ) {
                $expert_avatar = '<a href="' . $expert_link . '" target="_blank">' . $expert_avatar . '</a>';
            }
        }

        $expert_name        = apply_filters( 'expert_review:expert_name', $expert_name, $atts );
        $expert_avatar      = apply_filters( 'expert_review:expert_avatar', $expert_avatar, $atts );
        $expert_description = apply_filters( 'expert_review:expert_description', $expert_description, $atts );

        $out = apply_filters( 'expert_review_before', $out, $atts );

        /**
         * Expert
         */
        if ( $atts['expert_show'] && (
                ! empty( $atts['expert_name'] ) ||
                ! empty( $atts['expert_text'] ) ||
                ! empty( $atts['expert_id'] ) ||
                ! empty( $atts['expert_user_id'] ) )
        ) {

            // todo user id, expert id check

            $out = apply_filters( 'expert_review:before_expert', $out, $atts );
            $out .= '<div class="expert-review-expert"' . ( 'schema' == $this->advanced_options->expert_microdata_type ? ' itemscope itemtype="https://schema.org/Person"' : '' ) . '>';

            if ( ! empty( $atts['expert_title'] ) && $atts['expert_show_title'] == 1 ) {
                $out .= '<div class="expert-review-expert-header">' . $atts['expert_title'] . '</div>';
            }

            $out .= '  <div class="expert-review-expert-bio">';
            $out .= '    <div class="expert-review-expert-bio__avatar">';
            if ( ! empty( $expert_avatar ) ) {
                $out .= $expert_avatar;
            }
            $out .= '    </div>';
            $out .= '    <div class="expert-review-expert-bio__body">';
            $out .= '      <div class="expert-review-expert-bio-name"' . ( 'schema' == $this->advanced_options->expert_microdata_type ? ' itemprop="name"' : '' ) . '>' . $expert_name . '</div>';
            $out .= '      <div class="expert-review-expert-bio-description"' . ( 'schema' == $this->advanced_options->expert_microdata_type ? ' itemprop="description"' : '' ) . '>' . $expert_description . '</div>';
            $out .= '    </div>';

            if ( $atts['expert_show_button'] ) {
                $settings         = [
                    'type'       => $atts['expert_show_button_type'],
                    'expertType' => $atts['expert_type'],
                    'expertId'   => $atts['expert_id'],
                    'use_phone'  => $atts['popup_use_phone'],
                    'link'       => $atts['question_external_link'],
                ];
                $settings['sign'] = Utilities::sign_data( $settings, wp_create_nonce( 'button_settings' ) );
                $button_settings  = esc_attr( json_encode( $settings ) );

                $button = apply_filters( 'expert_review:question_button',
                    '<span class="expert-review-button js-expert-review-button" data-settings="' . $button_settings . '">' . $atts['expert_question_button_text'] . '</span>',
                    $atts,
                    $settings
                );

                $out .= '    <div class="expert-review-expert-bio__button">';
                $out .= $button;
                $out .= '    </div>';
            }

            $out .= '  </div>';

            if ( ! empty( $atts['expert_text'] ) ) {
                $out .= '  <div class="expert-review-expert-text">';
                $out .= '  ' . do_shortcode( $nl2br( $atts['expert_text'] ) );
                $out .= '  </div>';
            }

            $out .= '</div>';

            $out = apply_filters( 'expert_review:after_expert', $out, $atts );
        }

        /**
         * Questions and Answers
         */
        if ( ! empty( $atts['qa'] ) ) {
            $out = apply_filters( 'expert_review:before_qa', $out, $atts );

            $out .= '<div class="expert-review-qa">';

            if ( ! empty( $atts['qa_title'] ) && $atts['qa_show_title'] == 1 ) {
                $out .= '<div class="expert-review-qa-header">' . $atts['qa_title'] . '</div>';
            }

            $use_microdata = ! $this->advanced_options->use_json_ld_faq_microdata;

            $q_tag = apply_filters( 'expert_review/qa/question_tag', $this->qa_options->qa_question_tag ?: 'div', $atts );
            $a_tag = apply_filters( 'expert_review/qa/answer_tag', $this->qa_options->qa_answer_tag ?: 'div', $atts );

            foreach ( $atts['qa'] as $qa ) {
                $q = $nl2br( $qa['q'] );
                $a = $nl2br( $qa['a'] );

                $q = apply_filters( 'expert_review_qa:question', $q, $atts );
                $a = apply_filters( 'expert_review_qa:answer', $a, $atts );

                do_action( 'expert_review_questions_and_answers', [ $q, $a ] );

                if ( $use_microdata ) {
                    $out .= '<div class="expert-review-qa-container" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
                    $out .= "  <$q_tag class=\"expert-review-qa__question\" itemprop=\"name\">" . $q . "</$q_tag>";
                    $out .= "  <$a_tag class=\"expert-review-qa__answer\" itemscope itemprop=\"acceptedAnswer\" itemtype=\"https://schema.org/Answer\">";
                    $out .= '    <div class="expert-review-qa__avatar">' . $expert_avatar . '</div>';
                    $out .= '    <div class="expert-review-qa__text" itemprop="text">' . $a . '</div>';
                    $out .= "  </$a_tag>";
                    $out .= '</div>';
                } else {
                    $out .= '<div class="expert-review-qa-container">';
                    $out .= "  <$q_tag class=\"expert-review-qa__question\">" . $q . "</$q_tag>";
                    $out .= "  <$a_tag class=\"expert-review-qa__answer\">";
                    $out .= '    <div class="expert-review-qa__avatar">' . $expert_avatar . '</div>';
                    $out .= '    <div class="expert-review-qa__text">' . $a . '</div>';
                    $out .= "  </$a_tag>";
                    $out .= '</div>';
                }
            }

            $out .= '</div><!--.expert-review-qa-->';

            $out = apply_filters( 'expert_review:after_qa', $out, $atts );
        }

        /**
         * Score (Rating)
         */
        if ( ! empty( $atts['score'] ) ) {
            $score_symbol = $atts['score_symbol'];
            $score_symbol = str_replace( '%%max%%', $atts['score_max'], $score_symbol );


            $out = apply_filters( 'expert_review:before_score', $out, $atts );

            $out .= '<div class="expert-review-score">';

            if ( $atts['score_show_title'] && $atts['score_title'] ) {
                $out .= '<div class="expert-review-score-header">' . $atts['score_title'] . '</div>';
            }

            foreach ( $atts['score'] as $score_item ) {

                $score_percent = ceil( ( floatval( $score_item['s'] ) * 100 ) / floatval( $atts['score_max'] ) ?: 1 );

                $out .= '<div class="expert-review-score-line">';
                $out .= '  <div class="expert-review-score-line__name">' . $score_item['n'] . '</div>';
                $out .= '  <div class="expert-review-score-line__progress"><div class="expert-review-score-line__progress-container"><div class="expert-review-score-line__progress-fill" style="width: ' . $score_percent . '%;"></div></div></div>';
                $out .= '  <div class="expert-review-score-line__score">' . $score_item['s'] . $score_symbol . '</div>';
                $out .= '</div>';
            }

            if ( ! empty( $atts['score_summary_text'] ) || ( $atts['score_summary_average'] && count( $atts['score'] ) > 1 ) ) {
                $score_total = array_reduce( $atts['score'], function ( $sum, $item ) {
                    return $sum + floatval( $item['s'] );
                }, 0 );

                $score_avg = round( $score_total / count( $atts['score'] ), 1 );

                $out .= '<div class="expert-review-score-summary">';
                $out .= '  <div class="expert-review-score-summary__label">' . apply_filters( 'expert_review:score_summary_label', __( 'Result', Plugin::TEXT_DOMAIN ) ) . '</div>';
                $out .= '  <div class="expert-review-score-summary__content">';

                if ( $atts['score_summary_average'] == 1 ) {
                    $out .= '    <div class="expert-review-score-summary__average">' . $score_avg . '</div>';
                }

                if ( ! empty( $atts['score_summary_text'] ) ) {
                    $out .= '    <div class="expert-review-score-summary__text">' . $nl2br( $atts['score_summary_text'] ) . '</div>';
                }

                $out .= '  </div>';
                $out .= '</div>';
            }

            $out .= '</div><!--.expert-review-score-->';

            $out = apply_filters( 'expert_review:before_score', $out, $atts );
        }

        /**
         * Pluses and minuses
         */
        $pluses  = $atts['pluses'];
        $minuses = $atts['minuses'];
        if ( ! empty( $pluses ) || ! empty( $minuses ) ) {

            $out = apply_filters( 'expert_review:before_plus_minus', $out, $atts );

            $out .= '<div class="expert-review-pluses-minuses">';

            $tag = apply_filters( 'expert_review/plus_minus/header_tag', $this->qa_options->pluses_header_tag ?: 'div', $atts );

            $show_general_title = apply_filters( 'expert_review:show_general_title', $atts['pluses_minuses_show_title'], $atts );
            if ( ! empty( $atts['pluses_minuses_title'] ) && $show_general_title ) {
                $out .= "<$tag class=\"expert-review-pluses-minuses-header\">" . $atts['pluses_minuses_title'] . "</$tag>";
            }

            $show_pluses_title = apply_filters( 'expert_review:show_pluses_title', 1, $atts );
            if ( ! empty( $pluses ) ) {
                $out .= '<div class="expert-review-pluses">';
                if ( $atts['pluses_title'] && $show_pluses_title ) {
                    $out .= "<$tag class=\"expert-review-pluses-minuses-header\">" . $atts['pluses_title'] . "</$tag>";
                }
                foreach ( $pluses as $plus ) {
                    $out .= '<div class="expert-review-plus">' . $plus . '</div>';
                }
                $out .= '</div>';
            }

            $show_minuses_title = apply_filters( 'expert_review:show_minuses_title', 1, $atts );
            if ( ! empty( $minuses ) ) {
                $out .= '<div class="expert-review-minuses">';
                if ( $atts['minuses_title'] && $show_minuses_title ) {
                    $out .= "<$tag class=\"expert-review-pluses-minuses-header\">" . $atts['minuses_title'] . "</$tag>";
                }
                foreach ( $minuses as $minus ) {
                    $out .= '<div class="expert-review-minus">' . $minus . '</div>';
                }
                $out .= '</div>';
            }

            $out .= '</div>';

            $out = apply_filters( 'expert_review:after_plus_minus', $out, $atts );
        }


        $classes = [];
        if ( ! empty( $atts['color'] ) ) {
            $classes[] = 'expert-review--color-' . $atts['color'];
        }
        $classes = ' ' . implode( ' ', $classes );

        $out = '<div class="expert-review' . $classes . '">' . $out . '</div>';

        $out = apply_filters( 'expert_review_after', $out, $atts );

        return $out;
    }

    /**
     * @param array  $atts
     * @param string $shortcode
     *
     * @return array
     */
    public static function expert_review_likes_shortcode_atts( $atts, $shortcode ) {
        return shortcode_atts( [
            'style'         => 'button-1-color',
            'size'          => 'm',
            'icons'         => 'thumbs',
            'alignment'     => '',
            'show_icon'     => 1,
            'show_label'    => 1,
            'show_count'    => 1,
            'hide_dislikes' => 0,
            'label_like'    => _x( 'Like', 'expert_review_likes', Plugin::TEXT_DOMAIN ),
            'label_dislike' => _x( 'Dislike', 'expert_review_likes', Plugin::TEXT_DOMAIN ),
            'name'          => '',
            'link'          => '',
            'post_id'       => '',
            'comment_id'    => '',
            'entity_type'   => self::LIKE_ENTITY_POSTS,
        ], $atts, $shortcode );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _expert_review_likes( $atts, $content, $shortcode ) {
        if ( ! apply_filters( 'expert_review_output_shortcodes', true, $shortcode ) ) {
            return '';
        }

        if ( is_feed() || apply_filters( 'expert_review_likes_prevent_render', false ) ) {
            return '';
        }

        $atts = static::expert_review_likes_shortcode_atts( $atts, $shortcode );

        $post_id = get_the_ID() ?: - 1;

        $likes = $dislikes = 0;
        if ( $atts['entity_type'] === self::LIKE_ENTITY_POSTS ) {
            if ( $atts['name'] ) {
                $identity  = apply_filters( 'expert_review:named_likes_identity', $atts['name'], $atts );
                $like_data = Likes::get_named_like_data( md5( $identity ) );
                $likes     = (int) $like_data['likes'];
                $dislikes  = (int) $like_data['dislikes'];
            } else {
                $post_id = $atts['post_id'] ? $atts['post_id'] : ( get_the_ID() ?: - 1 );

//            if ( ! get_post( $post_id ) ) {
//                return '<!-- unable to find post for likes shortcode -->';
//            }

                $likes    = (int) get_post_meta( $post_id, Likes::POST_META_LIKES, true );
                $dislikes = (int) get_post_meta( $post_id, Likes::POST_META_DISLIKES, true );
            }
        } elseif ( $atts['entity_type'] === self::LIKE_ENTITY_COMMENTS ) {
            $likes    = (int) get_comment_meta( $atts['comment_id'], Likes::COMMENT_META_LIKES, true );
            $dislikes = (int) get_comment_meta( $atts['comment_id'], Likes::COMMENT_META_DISLIKES, true );
        }

        $icons = $this->icons;

        if ( $this->do_render_templates() ) {
            $params = compact( 'atts', 'icons', 'likes', 'dislikes', 'content', 'post_id' );

            $params['instance'] = $this;

            return er_render_template( 'likes.php', $params );
        }


        $out = apply_filters( 'expert_review_likes_before', '', $atts );

        $type = $atts['hide_dislikes'] ? 'toggle' : 'like';
        $out  .= '<button class="expert-review-likes__button expert-review-likes__button--like js-expert-review-likes-button" data-type="' . $type . '">';
        if ( $atts['show_icon'] ) {
            $out .= '<span class="expert-review-likes__icon">';
            $out .= $icons[ $atts['icons'] ]['like'];
            $out .= '</span>';
        }
        if ( $atts['show_label'] ) {
            $out .= '<span class="expert-review-likes__label">';
            $out .= $atts['label_like'];
            $out .= '</span>';
        }
        if ( $atts['show_count'] ) {
            $out .= '<span class="expert-review-likes__count js-expert-review-likes-count" data-count="' . $likes . '">';
            if ( $likes > 0 ) {
                $out .= $this->rounded_number( $likes );
            } elseif ( $likes < 0 ) {
                $out .= '-' . $this->rounded_number( abs( $likes ) );
            }
            $out .= '</span>';
        }
        $out .= '</button>';

        if ( ! $atts['hide_dislikes'] ) {
            $out .= '<button class="expert-review-likes__button expert-review-likes__button--dislike js-expert-review-likes-button" data-type="dislike">';
            if ( $atts['show_icon'] ) {
                $out .= '<span class="expert-review-likes__icon">';
                $out .= $icons[ $atts['icons'] ]['dislike'];
                $out .= '</span>';
            }
            if ( $atts['show_label'] ) {
                $out .= '<span class="expert-review-likes__label">';
                $out .= $atts['label_dislike'];
                $out .= '</span>';
            }
            if ( $atts['show_count'] ) {
                $out .= '<span class="expert-review-likes__count js-expert-review-dislikes-count" data-count="' . $dislikes . '">';
                if ( $dislikes > 0 ) {
                    $out .= $this->rounded_number( $dislikes );
                } elseif ( $dislikes < 0 ) {
                    $out .= '-' . $this->rounded_number( abs( $dislikes ) );
                }
                $out .= '</span>';
            }
            $out .= '</button>';
        }

        $classes = [];

        if ( ! empty( $atts['style'] ) ) {
            $classes[] = 'expert-review-likes--style-' . $atts['style'];
        }

        if ( ! empty( $atts['size'] ) ) {
            $classes[] = 'expert-review-likes--size-' . $atts['size'];
        }

        if ( $atts['alignment'] ) {
            $classes[] = 'expert-review-likes--alignment-' . $atts['alignment'];
        }

        $classes = ' ' . implode( ' ', $classes );

        $data_attr = [];
        if ( $atts['entity_type'] === self::LIKE_ENTITY_POSTS ) {
            if ( $atts['name'] ) {
                $identity          = apply_filters( 'expert_review:named_likes_identity', $atts['name'], $atts );
                $data_attr['name'] = esc_attr( md5( $identity ) );
            } else {
                $data_attr['post_id'] = $post_id;
            }
        } elseif ( $atts['entity_type'] === self::LIKE_ENTITY_COMMENTS ) {
            $data_attr['entity_type'] = self::LIKE_ENTITY_COMMENTS;
            $data_attr['comment_id']  = $atts['comment_id'];
        }

        $data_attr = array_map( function ( $item, $key ) {
            return "data-{$key}=\"$item\"";
        }, $data_attr, array_keys( $data_attr ) );

        $data_attr = implode( ' ', $data_attr );


        $out = '<div class="expert-review-likes' . $classes . ' js-expert-review-likes-button-container" ' . $data_attr . '>' . $out . '</div>';

        $out = apply_filters( 'expert_review_likes_after', $out, $atts );

        return $out;
    }

    /**
     * @param string|array $atts
     * @param string       $content
     * @param string       $shortcode
     *
     * @return string
     */
    public function _expert_review_likes_rate( $atts, $content, $shortcode ) {
        if ( ! apply_filters( 'expert_review_output_shortcodes', true, $shortcode ) ) {
            return '';
        }

        $atts = shortcode_atts( [
            'title'              => '',
            'show_title'         => 1,
            'style'              => '',
            'output_total_score' => 0,
            'order'              => 'desc',
            'post_ids'           => '',
            'include_likes'      => '',
            'limit'              => '',
        ], $atts, $shortcode );

        $include_likes = [];
        if ( trim( $atts['include_likes'] ) ) {
            $include_likes = explode( ',', $atts['include_likes'] );
            $include_likes = array_map( 'trim', $include_likes );
            $include_likes = array_filter( $include_likes );
        }

        $output = '';

        $named_likes = $this->gather_named_likes( $atts['post_ids'], $include_likes );

        usort( $named_likes, function ( $a, $b ) use ( $atts ) {
            switch ( $atts['order'] ) {
                case 'desc_likes':
                    return $a['likes'] < $b['likes'] ? 1 : ( $a['likes'] > $b['likes'] ? - 1 : 0 );
                case 'asc_likes':
                    return $a['likes'] > $b['likes'] ? 1 : ( $a['likes'] < $b['likes'] ? - 1 : 0 );
                case 'desc_dislikes':
                    return $a['dislikes'] < $b['dislikes'] ? 1 : ( $a['dislikes'] > $b['dislikes'] ? - 1 : 0 );
                case 'asc_dislikes':
                    return $a['dislikes'] > $b['dislikes'] ? 1 : ( $a['dislikes'] < $b['dislikes'] ? - 1 : 0 );
                case 'desc':
                    return $a['rate'] < $b['rate'] ? 1 : ( $a['rate'] > $b['rate'] ? - 1 : 0 );
                case 'asc':
                default:
                    return $a['rate'] > $b['rate'] ? 1 : ( $a['rate'] < $b['rate'] ? - 1 : 0 );
            }
        } );

        if ( $limit = absint( $atts['limit'] ) ) {
            $named_likes = array_slice( $named_likes, 0, $limit, true );
        }

        if ( $this->do_render_templates() ) {
            $params = compact( 'atts', 'named_likes' );

            return er_render_template( 'likes-rate.php', $params );
        }

        if ( $named_likes ) {

            $output = apply_filters( 'expert_review_like_rate_before', $output, $atts );

            $output .= '<div class="expert-review-like-rating expert-review-like-rating--' . esc_attr( $atts['style'] ) . '">';
            if ( ! empty( $atts['title'] ) ) {
                $output .= '    <div class="expert-review-like-rating__header">' . $atts['title'] . '</div>';
            }
            $output .= '    <div class="expert-review-like-rating__list">';
            foreach ( $named_likes as $item ) {
                $identity = md5( $item['name'] );
                $text     = esc_html( $item['text'] );
                if ( ! empty( $item['link'] ) ) { // check empty because of legacy
                    $text = '<a href="' . $item['link'] . '">' . $text . '</a>';
                }
                $text   = apply_filters( 'expert_review_like_rate:item_text', $text, $item, $atts );
                $output .= '<div class="expert-review-like-rating-item">';
                $output .= '<div class="expert-review-like-rating-item__position"></div>';
                $output .= '<div class="expert-review-like-rating-item__text">' . $text . '</div>';
                $output .= '<div class="expert-review-like-rating-item__count"><span class="js-expert-review-like-rate" data-name="' . $identity . '">' . $item['rate'] . '</span>';
                if ( $atts['output_total_score'] ) {
                    $output .= '/<span class="js-expert-review-like-activity" data-name="' . $identity . '">' . $item['activity'] . '</span>';
                }
                $output .= '</div>';
                $output .= '</div>';
            }
            $output .= '    </div>';
            $output .= '</div><!--.expert-review-like-rate-->';

            $output = apply_filters( 'expert_review_like_rate_after', $output, $atts );
        }

        return $output;
    }

    /**
     * @param string $post_ids
     *
     * @return array
     * @see do_shortcode_tag()
     */
    protected function gather_named_likes( $post_ids = '', $include_likes = [] ) {
        $posts = [];

        if ( $post_ids ) {
            $posts = get_posts( apply_filters( 'expert_review:like_rate_post_types', [
                'numberposts' => - 1,
                'post__in'    => wp_parse_id_list( $post_ids ),
                'post_type'   => 'any',
            ] ) );
        } elseif ( ( $post = get_queried_object() ) && $post instanceof \WP_Post ) {
            $posts = [ $post ];
        }

        $named_likes = [];

        foreach ( $posts as $post ) {

//            if ( false === strpos( $post->post_content, '[' ) ) {
//                continue;
//            }

            $content_parts = apply_filters( 'expert_review:gather_named_likes', [ $post->post_content ], $post );

            foreach ( $content_parts as $content_part ) {
                preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content_part, $matches );
                $tagnames = array_intersect( [ 'expert_review_likes' ], $matches[1] );

                if ( empty( $tagnames ) ) {
                    continue;
                }

                $content = do_shortcodes_in_html_tags( $content_part, false, $tagnames );
                $pattern = get_shortcode_regex( $tagnames );

                preg_replace_callback( "/$pattern/", function ( $m ) use ( &$named_likes ) {
                    // allow [[foo]] syntax for escaping a tag
                    if ( $m[1] == '[' && $m[6] == ']' ) {
                        return substr( $m[0], 1, - 1 );
                    }

                    $atts = shortcode_parse_atts( $m[3] );

                    if ( ! empty( $atts['name'] ) ) {
                        $name = apply_filters( 'expert_review:named_likes_identity', $atts['name'], $atts );

                        $named_likes[ $name ] = $atts;
                    }

                    return '';
                }, $content );
            }
        }

        if ( $include_likes ) {
            $named_likes = array_filter( $named_likes, function ( $item ) use ( $include_likes ) {
                return in_array( $item['name'], $include_likes );
            } );
        }

        $named_likes = array_map( function ( $item ) {
            $name           = apply_filters( 'expert_review:named_likes_identity', $item['name'], $item );
            $params         = Likes::get_named_like_data( md5( $name ) );
            $params['name'] = $name;
            $params['text'] = $item['name'];
            $params['link'] = $item['link'];
            $params['atts'] = $item;

            return $params;
        }, $named_likes );

        return apply_filters( 'expert_review:name_likes', $named_likes );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _expert_review_faq( $atts, $content, $shortcode ) {
        if ( ! apply_filters( 'expert_review_output_shortcodes', true, $shortcode ) ) {
            return '';
        }

        $atts = shortcode_atts( [ 'params' => '' ], $atts, $shortcode );
        $atts = wp_parse_args( $atts['params'] ? $this->unserialize_params( $atts['params'] ) : [], [
            'title'      => '',
            'show_title' => 1,
            'style'      => 'simple-1',
            'color'      => 'blue-1',
            'qa'         => [],
        ] );

        if ( $this->do_render_templates() ) {
            $params = compact( 'atts', 'content' );

            return er_render_template( 'faq.php', $params );
        }


        $output = '';
        $output .= '<div class="expert-review-faq expert-review-faq--style-' . esc_attr( $atts['style'] ) . ' expert-review-faq--color-' . esc_attr( $atts['color'] ) . '">';

        if ( $atts['title'] && $atts['show_title'] ) {
            if ( has_filter( 'expert_review_faq:wrap_header' ) ) {
                $output .= apply_filters( 'expert_review_faq:wrap_header', $atts['title'], $atts );
            } else {
                $output .= '    <div class="expert-review-faq__header">' . $atts['title'] . '</div>';
            }
        }

        $use_microdata = ! $this->advanced_options->use_json_ld_faq_microdata;

        $q_tag = apply_filters( 'expert_review/faq/question_tag', $this->qa_options->qa_question_tag ?: 'div', $atts );
        $a_tag = apply_filters( 'expert_review/faq/answer_tag', $this->qa_options->qa_answer_tag ?: 'div', $atts );

        foreach ( $atts['qa'] as $item ) {
            $q = nl2br( $item['q'] );
            $a = nl2br( $item['a'] );

            $q = apply_filters( 'expert_review_faq_answers:question', $q, $atts );
            $a = apply_filters( 'expert_review_faq_answers:answer', $a, $atts );

            do_action( 'expert_review_questions_and_answers', [ $q, $a ] );

            $a_style = '';
            $expand  = ' expand';
            if ( empty( $atts['expanded'] ) ) {
                $expand  = '';
                $a_style = ' style="display:none"';
            }

            if ( $use_microdata ) {
                $output .= "<div class=\"expert-review-faq-item{$expand}\" itemscope itemprop=\"mainEntity\" itemtype=\"https://schema.org/Question\">";
                $output .= "  <$q_tag class=\"expert-review-faq-item__question js-expert-review-faq-item-question\" itemprop=\"name\">" . $q . "</$q_tag>";
                $output .= "  <$a_tag class=\"expert-review-faq-item__answer js-expert-review-faq-item-answer\"{$a_style} itemscope itemprop=\"acceptedAnswer\" itemtype=\"https://schema.org/Answer\"><span itemprop=\"text\">" . $a . "</span></$a_tag>";
                $output .= '</div>';
            } else {
                $output .= "<div class=\"expert-review-faq-item{$expand}\">";
                $output .= "  <$q_tag class=\"expert-review-faq-item__question js-expert-review-faq-item-question\">" . $q . "</$q_tag>";
                $output .= "  <$a_tag class=\"expert-review-faq-item__answer js-expert-review-faq-item-answer\"{$a_style}>" . $a . "</$a_tag>";
                $output .= '</div>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Round number, ex. for views
     *
     * @param int $num
     *
     * @return int|string
     */
    public function rounded_number( $num = 0 ) {
        if ( $num > 1000000 ) {
            return round( ( $num / 1000000 ), 1 ) . '&nbsp;' . __( 'm.', 'count number', Plugin::TEXT_DOMAIN );
        }
        if ( $num > 100000 ) {
            return round( ( $num / 1000 ) ) . '&nbsp;' . __( 'k.', 'count number', Plugin::TEXT_DOMAIN );
        }
        if ( $num > 1000 ) {
            return round( ( $num / 1000 ), 1 ) . '&nbsp;' . __( 'k.', 'count number', Plugin::TEXT_DOMAIN );
        }

        return $num;
    }

    /**
     * To support old version
     *
     * @param array $atts
     *
     * @return array
     */
    protected function parse_serialized_data( $atts ) {
        $atts['expert_show_button_type'] = $atts['qa_type'];
        unset( $atts['qa_type'] );

        $atts['qa'] = [];
        if ( ! empty( $atts['qa_data'] ) ) {
            $data = explode( ';;', $atts['qa_data'] );
            foreach ( $data as $line ) {
                if ( strpos( $line, '::' ) ) {
                    $qa = explode( '::', $line );

                    $atts['qa'][] = [
                        'q' => html_entity_decode( $qa[0] ),
                        'a' => html_entity_decode( ( ! empty( $qa[1] ) ) ? $qa[1] : '' ),
                    ];
                }
            }
            unset( $atts['qa_data'] );
        }

        $atts['score'] = [];
        if ( ! empty( $atts['score_data'] ) ) {
            $data = explode( ';;', $atts['score_data'] );
            foreach ( $data as $line ) {
                if ( strpos( $line, '::' ) ) {
                    $score = explode( '::', $line );
                    if ( ! empty( $score[1] ) ) {
                        $score[1]        = str_replace( ',', '.', $score[1] );
                        $atts['score'][] = [
                            'n' => html_entity_decode( trim( $score[0] ) ),
                            's' => (float) $score[1],
                        ];
                    }
                }
            }
            unset( $atts['score_data'] );
        }

        $pluses = [];
        if ( ! empty( $atts['pluses'] ) ) {
            $prepare_pluses = explode( ';;', $atts['pluses'] );
            foreach ( $prepare_pluses as $prepare_plus ) {
                $prepare_plus = trim( $prepare_plus );
                if ( ! empty( $prepare_plus ) ) {
                    $pluses[] = html_entity_decode( $prepare_plus );
                }
            }
        }
        $atts['pluses'] = $pluses;

        $minuses = [];
        if ( ! empty( $atts['minuses'] ) ) {
            $prepare_minuses = explode( ';;', $atts['minuses'] );
            foreach ( $prepare_minuses as $prepare_minus ) {
                $prepare_minus = trim( $prepare_minus );
                if ( ! empty( $prepare_minus ) ) {
                    $minuses[] = html_entity_decode( $prepare_minus );
                }
            }
        }
        $atts['minuses'] = $minuses;

        return $atts;
    }

    /**
     * @param string $params
     *
     * @return array
     */
    protected function unserialize_params( $params ) {
        return static::unserialize_shortcode_params( $params );
    }

    /**
     * @param string $str
     *
     * @return array
     */
    public static function unserialize_shortcode_params( $str ) {
        $params = base64_decode( $str );
        $params = urldecode( $params );
        $params = json_decode( $params, true );

        return $params;
    }

    /**
     * @param array $params
     *
     * @return false|string
     */
    public static function serialize_shortcode_params( $params ) {
        $result = json_encode( $params, JSON_UNESCAPED_UNICODE );
        $result = Utilities::encodeURIComponent( $result );
        $result = base64_encode( $result );

        return $result;
    }
}
