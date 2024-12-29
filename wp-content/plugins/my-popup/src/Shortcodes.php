<?php

namespace Wpshop\PluginMyPopup;

use DateTime;

class Shortcodes {

    /**
     * @var array
     */
    protected static $countdowns = [];

    /**
     * @return void
     */
    public function init() {
        add_shortcode( 'mypopup_social_buttons', [ $this, '_social_buttons' ] );
        add_shortcode( 'mypopup_social_widget', [ $this, '_social_widget' ] );
        add_shortcode( 'mypopup_output_posts', [ $this, '_output_posts' ] );
        add_shortcode( 'mypopup_html', [ $this, '_html_code' ] );
        add_shortcode( 'mypopup_button', [ $this, '_button' ] );
        add_shortcode( 'mypopup_post_data', [ $this, '_post_data' ] );
        add_shortcode( 'mypopup_countdown', [ $this, '_countdown' ] );
        add_shortcode( 'mypopup_form_options', [ $this, '_form_options' ] );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return false|string
     * @throws \Exception
     */
    public function _form_options( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'close-time' => 0,
        ], $atts, $shortcode );

        return ob_get_content( function () use ( $atts ) {
            ?>
            <?php do_action( 'my_popup:form_hidden' ) ?>
            <input type="hidden" name="my_popup_form_action" value="my_popup_form_action"
                   data-name="my-popup-form-options"
                   data-close_time="<?php echo intval( $atts['close-time'] ) ?>">
            <?php wp_nonce_field( 'my_popup_form' ) ?>
            <?php
        } );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     * @throws \Exception
     */
    public function _social_widget( $atts, $content, $shortcode ) {
        global $id; // rely on setup_postdata() call

        $atts = shortcode_atts( [
            'type'    => '',
            'post_id' => $id,
        ], $atts, $shortcode );

        $result = '';
        switch ( $atts['type'] ) {
            case 'fb':
            case 'facebook':
                $result = ob_get_content( 'Wpshop\PluginMyPopup\output_fb_widget', $atts['post_id'] );
                break;
            case 'vk':
            case 'vkontakte':
                $result = ob_get_content( 'Wpshop\PluginMyPopup\output_vk_widget', $atts['post_id'] );
                break;
            case 'ok':
            case 'odnoklassniki':
                $result = ob_get_content( 'Wpshop\PluginMyPopup\output_ok_widget', $atts['post_id'] );
                break;
            case 'tw':
            case 'twitter':
                $result = ob_get_content( 'Wpshop\PluginMyPopup\output_tw_widget', $atts['post_id'] );
                break;
            case 'pn':
            case 'pinterest':
                $result = ob_get_content( 'Wpshop\PluginMyPopup\output_pn_widget', $atts['post_id'] );
                break;
            default:
                break;
        }

        return apply_filters( 'my_popup:shortcode_result', $result );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return false|string
     * @throws \Exception
     */
    public function _social_buttons( $atts, $content, $shortcode ) {
        global $id; // rely on setup_postdata() call
        $atts = shortcode_atts( [
            'post_id' => $id,
        ], $atts, $shortcode );

        $post = get_post( $atts['post_id'] );
        if ( ! $post ) {
            return '';
        }

        $popup = container()->get( MyPopup::class );

        $profiles_list = [
            'facebook',
            'vkontakte',
            'twitter',
            'odnoklassniki',
            'telegram',
            'youtube',
            'instagram',
            'linkedin',
            'whatsapp',
            'viber',
            'pinterest',
            'yandexzen',
            'github',
            'discord',
            'rutube',
            'yappy',
            'pikabu',
            'yandex',
        ];

        $profiles = [];
        foreach ( $profiles_list as $profile ) {
            $social_link = $popup->get_post_meta( $post->ID, 'my_popup_social_' . $profile, true );
            $social_link = $social_link ?: [ 'value' => '' ];

            if ( $social_link['value'] ) {
                $profiles[ $profile ] = $social_link['value'];
            }
        }

        if ( $profiles ) {
            $classes = [];
            $styles  = [];

            $type       = $popup->get_post_meta( $post->ID, 'my_popup_type_social_buttons', true );
            $align      = $popup->get_post_meta( $post->ID, 'my_popup_social_buttons_align', true );
            $width      = $popup->get_post_meta( $post->ID, 'my_popup_social_buttons_width', true );
            $height     = $popup->get_post_meta( $post->ID, 'my_popup_social_buttons_height', true );
            $indent     = $popup->get_post_meta( $post->ID, 'my_popup_social_buttons_indent', true );
            $hide_links = $popup->get_post_meta( $post->ID, 'my_popup_hide_social_links', true );

            if ( $type['value'] == 'round' ) {
                $classes[] = 'mypopup-social-buttons--circle';
            } else {
                $classes[] = 'mypopup-social-buttons--square';
            }

            $classes[] = 'mypopup-social-buttons--align-' . $align['value'];

            if ( ! empty( $width['value'] ) ) {
                $styles[] = 'width: ' . $width['value'] . 'px;';
            }
            if ( ! empty( $height['value'] ) ) {
                $styles[] = 'height: ' . $height['value'] . 'px;';
            }
            if ( ! empty( $indent['value'] ) ) {
                $styles[] = 'margin-right: ' . ( $indent['value'] - 6 ) . 'px;';
            }

            $styles = (array) apply_filters( 'my_popup:social_buttons_styles', $styles );

            $result = ob_get_content( function ( $profiles, $classes, $styles, $hide_links ) {
                $classes = trim( is_array( $classes ) ? ' ' . implode( ' ', $classes ) : $classes );
                ?>
                <div class="mypopup-social-links">
                    <div class="mypopup-social-buttons <?php echo $classes ?>">
                        <?php foreach ( $profiles as $profile => $social_profile ): ?>
                            <?php
                            $link = apply_filters( 'my_popup:social_profile_link', $social_profile, $profile );
                            ?>
                            <?php if ( $hide_links ): ?>
                                <span data-href="<?php echo base64_encode( $link ) ?>" class="mypopup-social-button mypopup-social-button--<?php echo $profile ?> js-mypopup-link" style="<?php echo implode( ' ', $styles ) ?>"></span>
                            <?php else: ?>
                                <a href="<?php echo esc_attr( $link ) ?>" class="mypopup-social-button mypopup-social-button--<?php echo $profile ?>" target="_blank" style="<?php echo implode( ' ', $styles ) ?>"></a>
                            <?php endif; ?>
                        <?php endforeach ?>
                    </div>
                </div>
                <?php
            }, $profiles, $classes, $styles, $hide_links['is_enabled'] );

            return apply_filters( 'my_popup:shortcode_result', $result );
        }

        return '';
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return false|string
     * @throws \Exception
     */
    public function _output_posts( $atts, $content, $shortcode ) {
        global $id; // rely on setup_postdata() call
        $atts = shortcode_atts( [
            'post_id' => $id,
        ], $atts, $shortcode );

        $post = get_post( $atts['post_id'] );
        if ( ! $post ) {
            return '';
        }

        $popup = container()->get( MyPopup::class );

        $title              = $popup->get_post_meta( $post->ID, 'my_popup_output_posts_title', true );
        $count              = $popup->get_post_meta( $post->ID, 'my_popup_output_posts_count', true );
        $order              = $popup->get_post_meta( $post->ID, 'my_popup_output_posts_order', true ) ?: [ 'value' => '' ];
        $posts_include      = $popup->get_post_meta( $post->ID, 'my_popup_output_posts_include', true );
        $posts_exclude      = $popup->get_post_meta( $post->ID, 'my_popup_output_posts_exclude', true );
        $categories_include = $popup->get_post_meta( $post->ID, 'my_popup_output_categories_include', true );
        $categories_exclude = $popup->get_post_meta( $post->ID, 'my_popup_output_categories_exclude', true );
        $posts_add_sorting  = $popup->get_post_meta( $post->ID, 'my_popup_output_posts_add_sorting', true );
        $open_new_tab       = $popup->get_post_meta( $post->ID, 'my_popup_output_posts_open_new_tab', true );
        $show_thumb         = $popup->get_post_meta( $post->ID, 'my_popup_output_post_show_thumb', true );
        $style              = $popup->get_post_meta( $post->ID, 'my_popup_output_post_style', true );

        $title['value']                  = $title['value'] ?? '';
        $count['value']                  = $count['value'] ?? '';
        $posts_include['value']          = $posts_include['value'] ?? '';
        $posts_exclude['value']          = $posts_exclude['value'] ?? '';
        $categories_include['value']     = $categories_include['value'] ?? '';
        $categories_exclude['value']     = $categories_exclude['value'] ?? '';
        $posts_add_sorting['is_enabled'] = ! empty( $posts_add_sorting['is_enabled'] );
        $open_new_tab['is_enabled']      = ! empty( $open_new_tab['is_enabled'] );
        $show_thumb['is_enabled']        = ! empty( $show_thumb['is_enabled'] );
        $style['value']                  = $style['value'] ?? '';

        $classes      = [];
        $output_posts = [];
        $posts_delta  = $count['value'];
        $meta_key     = '';
        $orderby      = 'rand';
        $post__not_in = [];

        if ( is_single() ) {
            $post_id = get_the_ID();

            // убираем текущий открытый пост
            $post__not_in = [ $post_id ];
        }

        switch ( $order['value'] ?? '' ) {
            case 'views':
                $orderby = 'meta_value_num';
                break;
            case 'comments':
                $orderby = 'comment_count';
                break;
            case 'new':
                $orderby = 'date';
                break;
            case 'rand':
            default:
                $orderby = 'rand';
                break;
        }

        if ( $style['value'] == 'horizontal' ) {
            $classes[] = 'mypopup-output-posts-horizontal';
        }

        if ( ! empty( $posts_exclude['value'] ) ) {
            $posts_exclude_exp = explode( ',', $posts_exclude['value'] );

            if ( is_array( $posts_exclude_exp ) ) {
                $posts_exclude = array_map( 'trim', $posts_exclude_exp );

                foreach ( $posts_exclude as $key => $value ) {
                    $post__not_in[] = $posts_exclude[ $key ];
                }
            } else {
                $posts_exclude = [ $posts_exclude ];
            }
        }

        if ( ! empty( $categories_exclude['value'] ) ) {
            $categories_exclude = explode( ',', $categories_exclude['value'] );

            foreach ( $categories_exclude as $key => $value ) {
                $categories_exclude[ $key ] = '-' . $categories_exclude[ $key ];
            }
        }

        if ( ! empty( $posts_include['value'] ) ) {
            $posts_include_exp = explode( ',', $posts_include['value'] );

            if ( is_array( $posts_include_exp ) ) {
                $posts_include = array_map( 'trim', $posts_include_exp );
            } else {
                $posts_include = [ $posts_include ];
            }

            if ( ! empty( $posts_exclude ) ) {
                $posts_include = array_diff( $posts_include, $posts_exclude );
            }

            if ( ! empty( $posts_include ) ) {
                $output_posts = get_posts( [
                    'posts_per_page' => $posts_delta,
                    'post__in'       => $posts_include,
                    'category'       => $categories_exclude,
                    'meta_key'       => $meta_key,
                    'orderby'        => $orderby,
                    'post__not_in'   => $post__not_in,
                ] );
            }
        }

        // если не хватило, добираем из категории
        if ( count( $output_posts ) < $posts_delta ) {
            // сколько осталось постов
            $posts_delta -= count( $output_posts );

            // добавляем все найденные посты в исключения
            foreach ( $output_posts as $output_post ) {
                $post__not_in[] = $output_post->ID;
            }

            if ( ! empty( $categories_include['value'] ) ) {
                $categories_include_exp = explode( ',', $categories_include['value'] );

                if ( is_array( $categories_include_exp ) ) {
                    $categories_include = array_map( 'trim', $categories_include_exp );
                } else {
                    $categories_include = [ $categories_include ];
                }
            }

            if ( ! empty( $categories_exclude ) ) {
                $categories_include = array_merge( $categories_include, $categories_exclude );
            }

            if ( ! empty( $categories_include ) ) {
                $posts_categories = get_posts( [
                    'posts_per_page' => $posts_delta,
                    'category'       => $categories_include,
                    'meta_key'       => $meta_key,
                    'orderby'        => $orderby,
                    'post__not_in'   => $post__not_in,
                ] );
            }
        }

        // если нашлись посты — добавляем в общий массив
        if ( ! empty( $posts_categories ) ) {
            $output_posts = array_merge( $output_posts, $posts_categories );
            $posts_delta  -= count( $posts_categories );

            // добавляем все найденные посты в исключения
            foreach ( $posts_categories as $post_categories ) {
                $post__not_in[] = $post_categories->ID;
            }
        }

        // если не хватило — добираем согласно сортировке
        if ( $posts_add_sorting['is_enabled'] && $posts_delta > 0 ) {

            $posts_sorting = get_posts( [
                'posts_per_page' => $posts_delta,
                'category'       => $categories_exclude,
                'meta_key'       => $meta_key,
                'orderby'        => $orderby,
                'post__not_in'   => $post__not_in,
            ] );

            // если нашлись посты — добавляем в общий массив
            if ( ! empty( $posts_sorting ) ) {
                $output_posts = array_merge( $output_posts, $posts_sorting );
                $posts_delta  -= count( $posts_sorting );

                // добавляем все найденные посты в исключения
                foreach ( $posts_sorting as $post_sorting ) {
                    $post__not_in[] = $post_sorting->ID;
                }
            }
        }

        if ( ! empty( $output_posts ) ) {
            return ob_get_content( function ( $title, $classes, $output_posts, $show_thumb, $link_target_blank ) {
                $classes = trim( is_array( $classes ) ? ' ' . implode( ' ', $classes ) : $classes );
                ?>
                <div class="mypopup-output-posts">
                    <?php if ( ! empty( $title ) ): ?>
                        <div class="mypopup-output-posts__header"><?php echo esc_html( $title ) ?></div>
                    <?php endif ?>
                    <div class="mypopup-output-posts-inner <?php echo $classes ?>">
                        <?php foreach ( $output_posts as $output_post ): ?>
                            <div class="mypopup-output-post">
                                <?php $thumb = get_the_post_thumbnail( $output_post->ID, apply_filters(
                                    'my_popup:output_posts_thumbnail', [ 60, 60 ]
                                ) );
                                if ( $show_thumb && ! empty( $thumb ) ): ?>
                                    <div class="mypopup-output-post__thumb">
                                        <?php echo $thumb ?>
                                    </div>
                                <?php endif ?>
                                <div class="mypopup-output-post__body">
                                    <div class="mypopup-output-post__title">
                                        <a href="<?php echo get_the_permalink( $output_post ) ?>"<?php echo $link_target_blank ? ' target="_blank"' : '' ?>>
                                            <?php echo get_the_title( $output_post ) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <?php
            }, $title['value'], $classes, $output_posts, $show_thumb['is_enabled'], $open_new_tab['is_enabled'] );
        }

        return '';
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _html_code( $atts, $content, $shortcode ) {
        global $id; // rely on setup_postdata() call
        $atts = shortcode_atts( [
            'post_id' => $id,
            'id'      => 1,
        ], $atts, $shortcode );

        $post = get_post( $atts['post_id'] );
        if ( ! $post ) {
            return '';
        }

        $input_id = $atts['id'] - 1;
        $value    = container()->get( MyPopup::class )->get_post_meta( $post->ID, 'my_popup_prepared_code', true );

        $result = array_key_exists( $input_id, $value ) ? $value[ $input_id ] : '';

        global $shortcode_tags;
        $shortcode_tags_orig = $shortcode_tags;
        unset( $shortcode_tags['mypopup_html'] );

        $result = do_shortcode( $result );

        $shortcode_tags = $shortcode_tags_orig;

        return apply_filters( 'my_popup:shortcode_result', $result );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _button( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'tag'           => 'button',
            'href'          => null,
            'class'         => 'js-mypopup-modal-close',
            'background'    => '',
            'color'         => '',
            'border'        => '',
            'padding'       => '',
            'border-radius' => '',
            'target'        => null,
            'close'         => 0,
        ], $atts, $shortcode );

        $classes = [ 'mypopup-button' ];
        if ( $atts['class'] ) {
            $atts_classes = explode( ' ', $atts['class'] );
            $classes      = array_merge( $classes, $atts_classes );
        }

        $styles = [];
        if ( $atts['background'] ) {
            $styles[] = 'background:' . $atts['background'];
        }
        if ( $atts['color'] ) {
            $styles[] = 'color:' . $atts['color'];
        }
        if ( $atts['border'] ) {
            $styles[] = 'border:' . $atts['border'];
        }
        if ( $atts['padding'] ) {
            $styles[] = 'padding:' . $atts['padding'];
        }
        if ( $atts['border-radius'] ) {
            $styles[] = 'border-radius:' . $atts['border-radius'];
        }

        $target = null;
        $rel    = null;
        if ( in_array( $atts['target'], [ '_blank', 'blank' ] ) ) {
            $target = '_blank';
            $rel    = 'noopener';
        }

        $attributes = apply_filters( 'my_popup:button_attributes', [
            'href'    => $atts['href'],
            'class'   => implode( ' ', $classes ),
            'style'   => implode( ';', $styles ),
            'target'  => $target,
            'rel'     => $rel,
            'onclick' => $atts['close'] ? 'myPopupClose(this)' : null,
        ], $atts );

        array_walk( $attributes, function ( &$v, $k ) {
            if ( null !== $v ) {
                $v = $k . '="' . $v . '"';
            }
        } );

        $attributes = array_filter( $attributes );
        $attributes = implode( ' ', $attributes );

        return apply_filters(
            'my_popup:shortcode_result',
            "<{$atts['tag']} $attributes>{$content}</{$atts['tag']}>"
        );
    }

    /**
     * Shortcode [mypopup_countdown]
     *
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _countdown( $atts, $content, $shortcode ) {
        global $id; // rely on setup_postdata() call
        if ( ! ( $post = get_post( $id ) ) ) {
            return '';
        }

        if ( ! isset( static::$countdowns[ $id ] ) ) {
            static::$countdowns[ $id ] = - 1;
        }
        static::$countdowns[ $id ] ++;

        $atts = shortcode_atts( [
            'locale'              => substr( get_locale(), 0, 2 ),
            'seconds'             => 0,
            'minutes'             => 0,
            'hours'               => 0,
            'days'                => 0,
            'weeks'               => 0,
            'months'              => 0,
            'years'               => 0,
            'expire_date'         => '',
            'hide_empty_counters' => true,
            'style'               => 1,
            'background'          => '',
            'color'               => '',
        ], $atts, $shortcode );

        $time = 0;
        if ( $atts['expire_date'] ) {
            $current = new DateTime( 'now', wp_timezone() );
            if ( $date = DateTime::createFromFormat( 'Y-m-d H:i:s', $atts['expire_date'], wp_timezone() ) ) {
                if ( $date > $current ) {
                    $time = $date->getTimestamp() - $current->getTimestamp();
                }
            }
        } else {
            $time = $atts['seconds'] +
                    $atts['minutes'] * MINUTE_IN_SECONDS +
                    $atts['hours'] * HOUR_IN_SECONDS +
                    $atts['days'] * DAY_IN_SECONDS +
                    $atts['weeks'] * WEEK_IN_SECONDS +
                    $atts['months'] * MONTH_IN_SECONDS +
                    $atts['years'] * YEAR_IN_SECONDS;
        }

        $options = [
            'locale'          => $atts['locale'],
            'identity'        => get_popup_id( $post ) . '.countdown.' . static::$countdowns[ $id ],
            'time'            => $time,
            'start_with_zero' => true,
            'class_prefix'    => 'mypopup-',
            'hide_empty'      => (bool) $atts['hide_empty_counters'],
        ];

        $styles = '';
        if ( ! empty( $atts['background'] ) ) {
            $styles .= '--mypopup-countdown-background: ' . $atts['background'] . ';';
        }
        if ( ! empty( $atts['color'] ) ) {
            $styles .= '--mypopup-countdown-color: ' . $atts['color'] . ';';
        }

        if ( ! empty( $styles ) ) {
            $styles = ' style="' . $styles . '"';
        }

        return apply_filters(
            'my_popup:shortcode_result',
            '<div class="mypopup-countdown mypopup-countdown--style-' . $atts['style'] . ' js-mypopup-countdown" data-options="' . esc_json( json_encode( $options ) ) . '"' . $styles . '></div>'
        );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _post_data( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'post_id' => '',
            'key'     => '',
        ], $atts, $shortcode );
        $atts = array_map( 'trim', $atts );

        if ( ! $atts['post_id'] ) {
            if ( is_single() ) {
                $atts['post_id'] = get_the_ID();
            } elseif ( is_preview_mode() ) {
                return apply_filters( 'my_popup:shortcode_post_data:preview', $atts['key'] );
            }
        }

        $post = get_post( $atts['post_id'] );
        if ( ! $post ) {
            return '';
        }

        switch ( $atts['key'] ) {
            case 'ID':
            case 'post_content':
            case 'post_title':
            case 'post_excerpt':
            case 'post_status':
            case 'post_name':
                return apply_filters(
                    'my_popup:shortcode_post_data:post_key',
                    $post->{$atts['key']},
                    $atts['key'],
                    $post
                );
            case 'post_date':
            case 'post_modified':
                return apply_filters(
                    'my_popup:shortcode_post_data:post_date',
                    get_the_date( '', $post ),
                    $atts['key'],
                    $post
                );
            case 'post_date_gmt':
            case 'post_modified_gmt':
                return apply_filters(
                    'my_popup:shortcode_post_data:post_date_gmt',
                    get_post_time( get_option( 'date_format' ), true, $post, true ),
                    $atts['key'],
                    $post
                );
            case 'post_author':
                if ( $user = get_userdata( $post->post_author ) ) {
                    return apply_filters(
                        'my_popup:shortcode_post_data:post_author',
                        $user->display_name,
                        $user,
                        $post
                    );
                }

                return '';
            default:
                break;
        }

        if ( $atts['key'] ) {
            $meta_value = get_post_meta( $post->ID, $atts['key'], true );

            return apply_filters( 'my_popup:shortcode_post_data:meta_data',
                is_string( $meta_value ) ? $meta_value : '',
                [ $atts['key'] => $meta_value ],
                $post
            );
        }

        return '';
    }

    /**
     * @return bool
     */
    protected function do_output_shortcode() {
        return (bool) apply_filters( 'my_popup:do_output_shortcode', doing_action( 'my_popup:output_popup' ) );
    }
}
