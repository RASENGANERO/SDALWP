<?php

namespace Wpshop\PluginMyPopup;

use WP_Post;
use Wpshop\PluginMyPopup\Css\CssBuilder;
use Wpshop\PluginMyPopup\Rule\PageContext;
use Wpshop\PluginMyPopup\Rule\RuleValidation;
use Wpshop\PluginMyPopup\Settings\PluginOptions;

class MyPopup {

    const POST_TYPE = 'my_popup';

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var PluginOptions
     */
    protected $options;

    /**
     * @var Icons
     */
    protected $icons;

    /**
     * @var WP_Post[]|null
     */
    protected $_popups_for_output;

    /**
     * @var RuleValidation
     */
    protected $validation;

    /**
     * @var bool
     */
    protected $is_preview_mode = false;

    /**
     * @var array
     */
    protected $hidden_by_cookie = [];

    /**
     * @var array
     */
    protected $preview_defaults = [
        'my_popup_close_time' => [
            'is_enabled' => false,
            'value'      => '',
        ],

        'my_popup_show_on_time'       => [
            'is_enabled' => true,
            'value'      => 0,
        ],
        'my_popup_show_on_scroll'     => [
            'is_enabled' => false,
            'value'      => '',
        ],
        'my_popup_show_on_element'    => [
            'is_enabled' => false,
            'value'      => '',
        ],
        'my_popup_show_inactive_time' => [
            'is_enabled' => false,
            'value'      => '',
        ],
        'my_popup_show_leaves_page'   => [
            'is_enabled' => false,
        ],

        'my_popup_display_desktop' => [
            'is_enabled' => false,
        ],
        'my_popup_display_tablet'  => [
            'is_enabled' => false,
        ],
        'my_popup_display_mobile'  => [
            'is_enabled' => false,
        ],
    ];

    /**
     * @var array
     */
    public $cookies = [];

    /**
     * MyPopup constructor.
     *
     * @param Plugin         $plugin
     * @param PluginOptions  $options
     * @param RuleValidation $validation
     */
    public function __construct(
        Plugin $plugin,
        PluginOptions $options,
        RuleValidation $validation,
        Icons $icons
    ) {
        $this->plugin     = $plugin;
        $this->options    = $options;
        $this->validation = $validation;
        $this->icons      = $icons;

        add_action( 'my_popup:output_popup', function ( $post ) {
            $this->_output_popup( $post );
        } );
    }

    /**
     * @return bool
     */
    public function is_preview_mode() {
        return $this->is_preview_mode;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function set_preview_mode( $flag ) {
        $this->is_preview_mode = (bool) $flag;

        return $this;
    }

    /**
     * @return void
     */
    public function init() {
        $this->registerMyPopupType();

        if ( $this->plugin->verify() ) {

            add_action( 'init', [ $this, 'init_and_update_cookies' ] );

            add_action( 'wp_footer', [ $this, 'output_all_popups' ] );

            add_action( 'wp_enqueue_scripts', function () {
                wp_enqueue_style( 'my-popup-style', plugin_dir_url( __DIR__ ) . 'assets/public/css/styles.min.css', [], $this->plugin->version );
                wp_enqueue_script( 'my-popup-scripts', plugin_dir_url( __DIR__ ) . 'assets/public/js/scripts.min.js', [ 'jquery' ], '20221202', true );

                $params = [
                    'url'    => admin_url( 'admin-ajax.php' ),
                    'nonce'  => wp_create_nonce( 'my-popup-nonce' ),
                    'action' => 'my-popup',
                ];
                if ( $this->is_preview_mode() ) {
                    $params['is_preview'] = true;
                }
                wp_localize_script( 'my-popup-scripts', 'my_popup_ajax', $params );

                if ( ! $this->do_direct_ouput() ) {
                    $context = PageContext::createFromWpQuery();
                    wp_add_inline_script( 'my-popup-scripts', 'var myPopupInitData = {params:"' . $context . '"};', 'before' );
                }
            } );

            add_filter( 'script_loader_tag', [ $this, '_defer_scripts' ], 10, 2 );

            if ( wp_doing_ajax() ) {
                $action = 'my_popup_init_data';
                add_action( "wp_ajax_{$action}", [ $this, '_init_popups_ajax' ] );
                add_action( "wp_ajax_nopriv_{$action}", [ $this, '_init_popups_ajax' ] );
            }
        }
    }

    /**
     * @param string $tag
     * @param string $handle
     *
     * @return string
     */
    public function _defer_scripts( $tag, $handle ) {
        if ( $this->do_defer_scripts() ) {
            if ( 'my-popup-scripts' === $handle ) {
                return str_replace( ' src', ' defer="defer" src', $tag );
            }
        }

        return $tag;
    }

    /**
     * @return void
     */
    public function _init_popups_ajax() {
        if ( ! $this->plugin->verify() ) {
            wp_send_json_error();
        }

        $items = [];
        foreach ( $this->get_popups_for_output() as $post ) {
            ob_start();
            $this->output_popup( $post );
            if ( $result = ob_get_clean() ) {
                $items[] = $result;
            }
        }

        wp_send_json_success( [
            'items' => $items,
        ] );
    }

    /**
     * @param WP_Post $post
     * @param string  $styles
     *
     * @return string
     */
    protected function replace_popup_id( $post, $styles ) {
        $id = $this->get_popup_id( $post );

        return strtr( $styles, [
            '{{id}}' => $id,
        ] );
    }

    /**
     * @return void
     */
    protected function registerMyPopupType() {
        add_action( 'init', function () {
            $labels = [
                'name'          => __( 'My Popup', Plugin::TEXT_DOMAIN ),
                'singular_name' => __( 'My Popup', Plugin::TEXT_DOMAIN ),
                'menu_name'     => __( 'My Popup', Plugin::TEXT_DOMAIN ),
                'all_items'     => __( 'All My Popups', Plugin::TEXT_DOMAIN ),
                'add_new'       => __( 'Add new', Plugin::TEXT_DOMAIN ),
                'add_new_item'  => __( 'Add new Popup', Plugin::TEXT_DOMAIN ),
                'edit_item'     => __( 'Edit Popup', Plugin::TEXT_DOMAIN ),
            ];
            register_post_type( MyPopup::POST_TYPE, [
                'label'                 => __( 'My Popup', Plugin::TEXT_DOMAIN ),
                'menu_icon'             => 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%" style="fill:#a0a5aa"><path d="M375 253h38v169H51V132h241v38H89v214h286V253zm38-121V87h-38v45h-45v38h45v45h38v-45h45v-38h-45z"/></svg>' ),
                'menu_position'         => 99,
                'labels'                => $labels,
                'description'           => '',
                'public'                => false,
                'publicly_queryable'    => false,
                'query_var'             => false,
                'show_ui'               => $this->plugin->verify(),
                'delete_with_user'      => false,
                'show_in_rest'          => false,
                'rest_base'             => '',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
                'has_archive'           => false,
                'show_in_nav_menus'     => true,
                'exclude_from_search'   => true,
                'capability_type'       => 'page',
                'map_meta_cap'          => true,
                'hierarchical'          => false,
                'rewrite'               => [ 'slug' => 'my_popup', 'with_front' => true ],
                'supports'              => [
                    'title',
                    //'editor',
                    'page-attributes',
                ],
            ] );
        } );

        add_action( 'save_post', function ( $post_id ) {
            if ( isset( $_POST['my_popup_editor_content'] ) ) {
                update_post_meta( $post_id, 'my_popup_editor_content', $_POST['my_popup_editor_content'] );
            }
        } );
    }

    /**
     * @return void
     */
    public function init_and_update_cookies() {
        if ( ! $this->plugin->verify() ) {
            return;
        }

        $hide_cookie = [];
        if ( ! empty( $_COOKIE['my_popup_hide'] ) ) {
            $hide_cookie = json_decode( stripslashes( $_COOKIE['my_popup_hide'] ), true );
        }

        $new_cookie = [];

        foreach ( $hide_cookie as $item ) {
            if ( ! isset( $item['time'] ) ) {
                $item['time'] = time();
            }

            $do_update = $item['time'] > time();

            foreach ( $this->get_all_popups() as $popup ) {
                if ( $this->get_popup_id( $popup ) === $item['id'] ) {
                    $cookies = wp_parse_args(
                        get_post_meta( $popup->ID, 'my_popup_cookies_type', true ),
                        Plugin::$DEFAULTS['my_popup_cookies_type']
                    );

                    // reset hide expiration if option was enabled
                    if ( $item['show_on'] != $cookies['is_enabled'] && $cookies['is_enabled'] ) {
                        $item['show_on'] = true;
                        $item['time']    = Utilities::hide_cookie_timestamp( $cookies['value'], $cookies['mode'] );
                        $do_update       = true;
                    }
                }
            }

            if ( $do_update ) {
                $new_cookie[] = $item;
            }

            $this->hidden_by_cookie[ $item['id'] ] = $item['time'] > time();
        }

        setcookie( 'my_popup_hide', json_encode( $new_cookie ), time() + 60 * 60 * 24 * 365, '/' );

        $this->cookies = $new_cookie;
    }

    /**
     * @return void
     */
    public function output_all_popups() {
        if ( ! $this->plugin->verify() ) {
            return;
        }

        if ( $this->is_preview_mode() ) {
            return;
        }

        if ( ! $this->do_direct_ouput() ) {
            return;
        }

        foreach ( $this->get_popups_for_output() as $popup_post ) {
            $this->output_popup( $popup_post );
        }
        wp_reset_postdata();
    }

    /**
     * @return bool
     */
    public function do_direct_ouput() {
        if ( $this->is_preview_mode ) {
            return true;
        }

        return apply_filters( 'my_popup:do_direct_output', false );
    }

    /**
     * @return bool
     */
    public function do_defer_scripts() {
        if ( $this->is_preview_mode ) {
            return false;
        }

        return apply_filters( 'my_popup:defer_scripts', true );
    }

    /**
     * @return WP_Post[]
     */
    protected function get_popups_for_output() {
        if ( null === $this->_popups_for_output ) {
            $this->_popups_for_output = [];

            $posts = get_posts( apply_filters( 'my_popup:get_popups_for_output_args', [
                'post_type'        => MyPopup::POST_TYPE,
                'post_status'      => 'publish',
                'posts_per_page'   => - 1,
                'suppress_filters' => true,
                'meta_key'         => 'my_popup_enable',
                'meta_value'       => 1,
            ] ) );

            $context = wp_doing_ajax()
                ? PageContext::createFromParams( $_REQUEST['params'] ?? '' )
                : PageContext::createFromWpQuery();

            if ( is_wp_error( $context ) ) {
                // @todo log error?
            } else {
                foreach ( $posts as $popup_post ) {
                    if ( ! $this->validation->can_output( $popup_post, $context ) ) {
                        continue;
                    }
                    $this->_popups_for_output[] = $popup_post;
                }
            }

        }

        return $this->_popups_for_output;
    }

    /**
     * @param string $status
     *
     * @return int[]|WP_Post[]
     */
    protected function get_all_popups( $status = 'publish' ) {
        return get_posts( [
            'post_type'   => MyPopup::POST_TYPE,
            'post_status' => $status,
        ] );
    }

    /**
     * @param WP_Post|int $post
     *
     * @return string
     * @deprecated
     */
    public function get_popup_id( $post ) {
        return get_popup_id( $post );
    }

    /**
     * @param int $id post id
     *
     * @return bool
     */
    protected function hide_by_cookie( $id ) {
        return array_key_exists( $id, $this->hidden_by_cookie ) && $this->hidden_by_cookie[ $id ];
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function output_popup( WP_Post $post ) {
        do_action( 'my_popup:output_popup', $post );
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    protected function _output_popup( $post ) {
        setup_postdata( $post );

        $popup_id = $this->get_popup_id( $post );

        $cookies = wp_parse_args(
            get_post_meta( $post->ID, 'my_popup_cookies_type', true ),
            Plugin::$DEFAULTS['my_popup_cookies_type']
        );

        $show_on_click = $this->get_post_meta( $post->ID, 'my_popup_show_on_click', true );

        $show_on_click['is_enabled'] = ! empty( $show_on_click['is_enabled'] );

        $show_my_popup = $this->is_preview_mode ||
                         $show_on_click['is_enabled'] ||
                         ( ! $this->hide_by_cookie( $popup_id ) && $this->do_show_by_device_type( $post ) );

        if ( ! $show_my_popup ) {
            return;
        }

        $show_on_time       = $this->get_post_meta( $post->ID, 'my_popup_show_on_time', true );
        $show_on_scroll     = $this->get_post_meta( $post->ID, 'my_popup_show_on_scroll', true );
        $show_on_element    = $this->get_post_meta( $post->ID, 'my_popup_show_on_element', true );
        $show_inactive_time = $this->get_post_meta( $post->ID, 'my_popup_show_inactive_time', true );
        $show_leaves_page   = $this->get_post_meta( $post->ID, 'my_popup_show_leaves_page', true );

        $show_on_time['is_enabled']       = ! empty( $show_on_time['is_enabled'] );
        $show_on_scroll['is_enabled']     = ! empty( $show_on_scroll['is_enabled'] );
        $show_on_element['is_enabled']    = ! empty( $show_on_element['is_enabled'] );
        $show_inactive_time['is_enabled'] = ! empty( $show_inactive_time['is_enabled'] );
        $show_leaves_page['is_enabled']   = ! empty( $show_leaves_page['is_enabled'] );

        if ( ! empty( $show_on_time['value'] ) && is_numeric( $show_on_time['value'] ) ) {
            $show_on_time['value'] = (int) $show_on_time['value'] * 1000;
        } else {
            $show_on_time['value'] = 0;
        }

        $classes = $this->classes( $post );

        $position          = $this->get_post_meta( $post->ID, 'my_popup_position', true );
        $enable_overlay    = $this->get_post_meta( $post->ID, 'my_popup_enable_overlay', true );
        $show_close_button = $this->get_post_meta( $post->ID, 'my_popup_show_close_button', true ) ?: Plugin::$DEFAULTS['my_popup_show_close_button'];
        $close_overlay     = get_post_meta( $post->ID, 'my_popup_close_overlay', true ) ?: [];
        $close_esc         = get_post_meta( $post->ID, 'my_popup_close_esc', true ) ?: [];
        $page_scrolling    = get_post_meta( $post->ID, 'my_popup_page_scrolling', true ) ?: [];
        $close             = $this->get_post_meta( $post->ID, 'my_popup_close_time', true );

        $icon = wp_parse_args( $this->get_post_meta( $post->ID, 'my_popup_icon', true ), Plugin::$DEFAULTS['my_popup_icon'] );

        $position['value'] = ( ! empty( $position['value'] ) ) ? $position['value'] : '';

        $enable_overlay['is_enabled']    = ! empty( $enable_overlay['is_enabled'] );
        $show_close_button['is_enabled'] = ! empty( $show_close_button['is_enabled'] );
        $close_overlay['is_enabled']     = ! empty( $close_overlay['is_enabled'] );
        $close_esc['is_enabled']         = ! empty( $close_esc['is_enabled'] );
        $page_scrolling['is_enabled']    = ! empty( $page_scrolling['is_enabled'] );
        $close['is_enabled']             = ! empty( $close['is_enabled'] );

        if ( ! empty( $show_close_button['value'] ) && is_numeric( $show_close_button['value'] ) ) {
            $show_close_button['value'] = (int) $show_close_button['value'] * 1000;
        } else {
            $show_close_button['value'] = 0;
        }

        if ( ! empty( $close['value'] ) && is_numeric( $close['value'] ) ) {
            $close['value'] = (int) $close['value'] * 1000;
        } else {
            $close['value'] = 0;
        }

        $overlay       = $this->get_post_meta( $post->ID, 'my_popup_overlay', true );
        $overlay_color = ( ! empty( $overlay['color'] ) && $overlay['color'] != '#000000' ) ? $overlay['color'] : '';

        if ( ! empty( $overlay['opacity'] ) ) {
            $overlay_opacity_percent = 100 - $overlay['opacity'];
            $overlay_opacity         = round( $overlay_opacity_percent / 100, 2 );
        } else {
            $overlay_opacity = '';
        }

        //$my_popup_animation = get_post_meta( $popup_post->ID, 'my_popup_animation', true );

        $options = [
            'popup_position'               => $position['value'],
            'cookies_enabled'              => $cookies['is_enabled'],
            'cookies_type'                 => $cookies['mode'],
            'cookies_value'                => $cookies['value'],
            'enable_overlay'               => $enable_overlay['is_enabled'],
            'close_button_enabled'         => $show_close_button['is_enabled'],
            'close_button_time'            => $show_close_button['value'],
            'close_overlay'                => $close_overlay['is_enabled'],
            'close_esc'                    => $close_esc['is_enabled'],
            'scroll_lock'                  => $page_scrolling['is_enabled'],
            'close_popup_enabled'          => $close['is_enabled'],
            'close_popup_time'             => $close['value'],
            //'popup_animation'             => $my_popup_animation['value'],
            'overlay_color'                => $overlay_color,
            'overlay_opacity'              => $overlay_opacity,
            'show_popup_time_enabled'      => $this->is_preview_mode() ? 1 : $show_on_time['is_enabled'],
            'show_popup_time_value'        => $this->is_preview_mode() ? 10 : $show_on_time['value'],
            'show_popup_scroll_enabled'    => $this->is_preview_mode() ? false : $show_on_scroll['is_enabled'],
            'show_popup_scroll_value'      => $show_on_scroll['value'],
            'show_popup_element_enabled'   => $this->is_preview_mode() ? false : $show_on_element['is_enabled'],
            'show_popup_element_value'     => $show_on_element['value'],
            'show_popup_inactive_enabled'  => $this->is_preview_mode() ? false : $show_inactive_time['is_enabled'],
            'show_popup_inactive_value'    => $show_inactive_time['value'],
            'show_popup_leaves_page'       => $this->is_preview_mode() ? false : $show_leaves_page['is_enabled'],
            'show_popup_on_click'          => $show_on_click['is_enabled'],
            'show_popup_on_click_selector' => $show_on_click['selector'] ?? '',
        ];

        $content_appearance = wp_parse_args( $this->get_post_meta( $post->ID, 'my_popup_appearance', true ), Plugin::$DEFAULTS['my_popup_appearance'] );
        $content_color      = $this->get_post_meta( $post->ID, 'my_popup_content_color', true );

        $close_btn_size     = wp_parse_args( $this->get_post_meta( $post->ID, 'my_popup_close_button_size', true ), Plugin::$DEFAULTS['my_popup_close_button_size'] );
        $close_btn_location = wp_parse_args( $this->get_post_meta( $post->ID, 'my_popup_close_button_location', true ), Plugin::$DEFAULTS['my_popup_close_button_location'] );
        $close_btn_color    = $this->get_post_meta( $post->ID, 'my_popup_close_button_color', true );
        $close_btn_icon     = wp_parse_args( $this->get_post_meta( $post->ID, 'my_popup_close_button_icon', true ), Plugin::$DEFAULTS['my_popup_close_button_icon'] );

        $blackout_bg = $this->get_post_meta( $post->ID, 'my_popup_bg_image_overlay', true );

        $popup_styles = new CssBuilder( '#' . $this->get_popup_id( $post ) );

        $this->append_styles( $post, '.mypopup-modal', $popup_styles );

        if ( $content_color ) {
            $popup_styles->add( '.mypopup-modal-content', [
                'color' => $content_color['color'],
            ] );
        }

        $popup_styles->add( '.mypopup-modal-close', [
            'color'   => ! empty( $close_btn_color['color'] ) && $close_btn_color['color'] != '#ffffff' ? $close_btn_color['color'] : null,
            'opacity' => round( ( 100 - $close_btn_color['opacity'] ) / 100, 2 ),
        ] );
        if ( 'inside' == $close_btn_location['value'] ) {
            $close_btn_gap = $close_btn_location['gap'];
            $popup_styles->add( '.mypopup-modal-close', [
                'top'   => "{$close_btn_gap}{$close_btn_location['gap_unit']}",
                'right' => "{$close_btn_gap}{$close_btn_location['gap_unit']}",
            ] );
        } else {
            $close_btn_gap = - 1 * ( $close_btn_size['value'] + $close_btn_location['gap'] );
            $popup_styles->add( '.mypopup-modal-close', [
                'top'   => "{$close_btn_gap}{$close_btn_location['gap_unit']}",
                'right' => "{$close_btn_gap}{$close_btn_location['gap_unit']}",
            ] );
        }

        $popup_styles->add( '.mypopup-modal-close svg', [
            'width'  => intval( $close_btn_size['value'] ) . 'px',
            'height' => intval( $close_btn_size['value'] ) . 'px',
        ] );

        if ( $content_appearance['padding'] ) {
            $popup_styles->add( '.mypopup-modal-content', [ 'padding' => "{$content_appearance['padding']}{$content_appearance['padding_unit']}" ] );
        } else {
            $popup_styles->add( '.mypopup-modal-content', [ 'padding' => "0" ] );
        }

        if ( $blackout_bg['is_enabled'] ) {
            $border = $this->get_post_meta( $post->ID, 'my_popup_border', true );
            $popup_styles->add( '.mypopup-modal:before', [
                'content'       => '""',
                'position'      => 'absolute',
                'top'           => '0',
                'left'          => '0',
                'right'         => '0',
                'bottom'        => '0',
                'background'    => hex_to_rgb( $blackout_bg['color'], $blackout_bg['opacity'] ),
                'border-radius' => $border['radius'] ? "{$border['radius']}px" : null,
            ] );
        }

        $content_classes_classes = [
            'mypopup-modal-content__wrap',
        ];

        $icon_content = '';
        if ( $icon['image'] ) {
            $content_classes_classes[] = 'mypopup-modal-content__wrap--icon-' . $icon['position'];

            $icon_gap = absint( $icon['gap'] );

            $popup_styles->add( ".mypopup-modal-content__wrap", [
                'gap' => $icon_gap ? "{$icon_gap}{$icon['gap_unit']}" : null,
            ] );
            $popup_styles->add( '.mypopup-icon', [
                'width'  => $icon['width'] ? "{$icon['width']}{$icon['width_unit']}" : null,
                'height' => $icon['height'] ? "{$icon['height']}{$icon['height_unit']}" : null,
                'flex'   => $icon['width'] ? "0 0 {$icon['width']}{$icon['width_unit']}" : null,
            ] );

            $icon_content .= '<div class="mypopup-icon">';
            $icon_content .= get_image( $icon['image'], [ 'width' => $icon['width'], 'height' => $icon['height'] ] );
            $icon_content .= '</div>';
        }

        $editor_content = $this->get_post_meta( $post->ID, 'my_popup_editor_content', true );

        $content = '<div class="mypopup-modal ' . implode( " ", $classes ) . '" id="modal_' . $popup_id . '">';
        $content .= '<div class="mypopup-modal-content" id="modal_content_' . $popup_id . '">';
        $content .= '<div class="mypopup-modal-close mypopup-button-close--location-' . $close_btn_location['value'] . ' js-mypopup-modal-close">';
        $content .= $this->icons->get_icon( $close_btn_icon['value'] );
        $content .= '</div>'; // /.mypopup-modal-close
        $content .= '<div class="' . implode( ' ', $content_classes_classes ) . '">';
        $content .= $icon_content;
        $content .= '<div class="mypopup-body">';
        $content .= apply_filters( 'my_popup:content', $editor_content, $post );
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';

        echo '<div class="mypopup-modal-container js-mypopup-container" data-content="' . base64_encode( Utilities::encodeURIComponent( $content ) ) . '" data-options="' . esc_json( json_encode( $options ) ) . '" tabindex="-1" role="dialog" aria-hidden="true" id="' . $popup_id . '">';

        $popup_styles = apply_filters( 'my_popup:styles', $popup_styles, $post );
        if ( $user_popup_styles = $this->replace_popup_id( $post, $this->get_post_meta( $post->ID, 'my_popup_inline_styles' ) ) ) {
            $popup_styles = trim( $popup_styles . "\n/* user popup styles */\n" . $user_popup_styles );
        }
        if ( $popup_styles = (string) $popup_styles ) {
            echo "<style>" . $popup_styles . ( WP_DEBUG ? "\n" : '' ) . "</style>";
        }

        echo '</div>';
    }

    /**
     * @param WP_Post $popup_post
     *
     * @return bool
     */
    public function do_show_by_device_type( $popup_post ) {

        $display_on_desktop = $this->get_post_meta( $popup_post->ID, 'my_popup_display_desktop' );
        $display_on_tablet  = $this->get_post_meta( $popup_post->ID, 'my_popup_display_tablet' );
        $display_on_mobile  = $this->get_post_meta( $popup_post->ID, 'my_popup_display_mobile' );

        $display_on_desktop['is_enabled'] = ! empty( $display_on_desktop['is_enabled'] );
        $display_on_tablet['is_enabled']  = ! empty( $display_on_tablet['is_enabled'] );
        $display_on_mobile['is_enabled']  = ! empty( $display_on_mobile['is_enabled'] );

        $is_tablet  = Utilities::is_tabled();
        $is_mobile  = wp_is_mobile() && ! $is_tablet;
        $is_desktop = ! $is_mobile && ! $is_tablet;

        if ( $display_on_desktop['is_enabled'] && $is_desktop ) {
            return true;
        }

        if ( $display_on_tablet['is_enabled'] && $is_tablet ) {
            return true;
        }

        if ( $display_on_mobile['is_enabled'] && $is_mobile ) {
            return true;
        }

        return false;
    }

    /**
     * @param WP_Post $popup_post
     *
     * @return array
     */
    protected function classes( $popup_post ) {

        $classes = [];

        $position  = $this->get_post_meta( $popup_post->ID, 'my_popup_position', true );
        $animation = $this->get_post_meta( $popup_post->ID, 'my_popup_animation', true );

        if ( ! empty( $position['value'] ) ) {

            if ( $position['value'] == 'top_left' ) {
                $classes[] = 'mypopup-modal_position_top-left';
            } else if ( $position['value'] == 'top_center' ) {
                $classes[] = 'mypopup-modal_position_top-center';
            } else if ( $position['value'] == 'top_right' ) {
                $classes[] = 'mypopup-modal_position_top-right';
            } else if ( $position['value'] == 'center_left' ) {
                $classes[] = 'mypopup-modal_position_center-left';
            } else if ( $position['value'] == 'center_center' ) {
                $classes[] = 'mypopup-modal_position_center-center';
            } else if ( $position['value'] == 'center_right' ) {
                $classes[] = 'mypopup-modal_position_center-right';
            } else if ( $position['value'] == 'bottom_left' ) {
                $classes[] = 'mypopup-modal_position_bottom-left';
            } else if ( $position['value'] == 'bottom_center' ) {
                $classes[] = 'mypopup-modal_position_bottom-center';
            } else if ( $position['value'] == 'bottom_right' ) {
                $classes[] = 'mypopup-modal_position_bottom-right';
            }

        }

        $classes[] .= 'mypopup--animation-' . $animation['value'];

        return $classes;
    }

    /**
     * @param WP_Post    $popup_post
     * @param string     $selector
     * @param CssBuilder $styles
     *
     * @return void
     */
    public function append_styles( $popup_post, $selector, CssBuilder $styles ) {
        $position           = $this->get_post_meta( $popup_post->ID, 'my_popup_position', true );
        $vertical_alignment = $this->get_post_meta( $popup_post->ID, 'my_popup_content_vertical_alignment', true );
        $bg_color           = $this->get_post_meta( $popup_post->ID, 'my_popup_background_color', true );
        $bg_image           = $this->get_post_meta( $popup_post->ID, 'my_popup_background_image', true );
        $border             = $this->get_post_meta( $popup_post->ID, 'my_popup_border', true );
        $shadow             = $this->get_post_meta( $popup_post->ID, 'my_popup_shadow', true );

        $styles->add( $selector, [
            'width'            => $position['width'] ? "{$position['width']}{$position['width_units']}" : null,
            'height'           => $position['height'] ? "{$position['height']}{$position['height_units']}" : null,
            'align-items'      => ! empty( $vertical_alignment['value'] ) && $vertical_alignment['value'] != 'flex-start' ? $vertical_alignment['value'] : null,
            'background-color' => ! empty( $bg_color['color'] ) ? hex_to_rgb( $bg_color['color'], $bg_color['opacity'] ) : null,
            'border-radius'    => $border['radius'] ? "{$border['radius']}px" : null,
        ] );

        if ( ! empty( $bg_image['image'] ) ) {
            $styles->add( $selector, [
                'background-image'    => "url({$bg_image['image']})",
                'background-position' => $bg_image['position'] ? str_replace( '_', ' ', $bg_image['position'] ) : null,
                'background-repeat'   => $bg_image['repeat'] ?: null,
                'background-size'     => $bg_image['size'] ?: null,
            ] );
        }

        if ( ! empty( $border['width'] ) ) {
            $styles->add( $selector, [
                'border-width' => $border['width'] . 'px',
                'border-style' => $border['style'] ?: null,
                'border-color' => $border['color'] ?: null,
            ] );
        }

        $shadow = wp_parse_args( $shadow, [
            'x'           => '',
            'x_unit'      => 'px',
            'y'           => '',
            'y_unit'      => 'px',
            'blur'        => '',
            'blur_unit'   => 'px',
            'spread'      => '',
            'spread_unit' => 'px',
            'color'       => '#000000',
            'opacity'     => 0,
            'inset'       => false,
        ] );

        if ( $shadow['x'] || $shadow['y'] || $shadow['blur'] || $shadow['spread'] ) {
            $x_offset = $shadow['x'] ? " {$shadow['x']}{$shadow['x_unit']}" : ' 0';
            $y_offset = $shadow['y'] ? " {$shadow['y']}{$shadow['y_unit']}" : ' 0';

            $blur   = $shadow['blur'] ? "{$shadow['blur']}{$shadow['blur_unit']}" : '0';
            $spread = $shadow['spread'] ? "{$shadow['spread']}{$shadow['spread_unit']}" : '0';

            $blur_with_spread = ' ';
            if ( $blur || $spread ) {
                $blur_with_spread = ! $spread ? " $blur" : " $blur $spread";
            }

            $color = ' ' . hex_to_rgb( $shadow['color'], $shadow['opacity'] );

            $styles->add( $selector, [ 'box-shadow' => "{$x_offset}{$y_offset}{$blur_with_spread}{$color}" ] );
        }
    }

    /**
     * @param int    $post_id
     * @param string $key
     * @param bool   $single
     *
     * @return mixed
     * @see MyPopupPreview::_render_preview_page()
     */
    public function get_post_meta( $post_id, $key, $single = true ) {
        $result = get_post_meta( $post_id, $key, $single );

        $result = apply_filters( 'my_popup:get_metadata', $result, $key, $post_id, $single );

        if ( ! $result && isset( Plugin::$DEFAULTS[ $key ] ) ) {
            $result = Plugin::$DEFAULTS[ $key ];
        }

        if ( $this->is_preview_mode && array_key_exists( $key, $this->preview_defaults ) ) {
            $result = $this->preview_defaults[ $key ];
        }

        $retrieve_from_request = function ( $key, &$result ) {
            if ( ! isset( $_REQUEST[ $key ] ) ) {
                return;
            }

            $value = map_deep( $_REQUEST[ $key ], 'stripslashes' );

            if ( false !== ( $pos = strpos( $key, ':' ) ) ) {
                $key = substr( $key, $pos + 1 );
                if ( is_array( $result ) ) {
                    $result[ $key ] = $value;
                } else {
                    $result = $value;
                }
            } else {
                $result = $value;
            }
//            if ( is_array( $result ) ) {
//                $result[ $key ] = $value;
//            } else {
//                $result = $value;
//            }
        };

//        if ( $this->is_preview_mode && array_key_exists( $key, $this->preview_params_map ) ) {
//            if ( is_array( $this->preview_params_map[ $key ] ) ) {
//                if ( ! is_array( $result ) ) {
//                    throw new \RuntimeException( 'Unable to map "' . $key . '"' );
//                }
//
//                foreach ( $this->preview_params_map[ $key ] as $item ) {
//                    $retrieve_from_request( $item, $result );
//                }
//            } else {
//                $retrieve_from_request( $this->preview_params_map[ $key ], $result );
//            }
//        }

        if ( $this->is_preview_mode ) {
            $preview_params = map_deep( $_REQUEST['display_box'] ?? [], 'stripslashes' );
            if ( array_key_exists( $key, $preview_params ) ) {
                $result = $preview_params[ $key ];
            }
        }

        return $result;
    }
}
