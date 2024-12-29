<?php

namespace Wpshop\PluginMyPopup;

use WP_Post;
use WPShop\Container\Container;
use Wpshop\PluginMyPopup\Settings\PluginOptions;

/**
 * @return Container
 */
function container() {
    static $container;
    if ( ! $container ) {
        $init      = require_once dirname( __DIR__ ) . '/config/container.php';
        $config    = require_once dirname( __DIR__ ) . '/config/config.php';
        $container = new Container( $init( $config ) );
    }

    return $container;
}

function activate() {
    if ( container()->has( PluginOptions::class ) ) {
        $options = container()->get( PluginOptions::class );

        $options->error_log_level = Logger::DISABLED;

        $options->save( PluginOptions::MODE_ADD );
    }
}

function deactivate() {

}

function uninstall() {
    if ( container()->has( PluginOptions::class ) ) {
        container()->get( PluginOptions::class )->destroy();
    }
}

/**
 * @param callable $fn
 * @param mixed    ...$args arguments for callable $fn
 *
 * @return false|string
 * @throws \Exception
 */
function ob_get_content( $fn ) {
    try {
        $ob_level = ob_get_level();
        ob_start();
        ob_implicit_flush( false );

        $args = func_get_args();
        call_user_func_array( $fn, array_slice( $args, 1 ) );

        return ob_get_clean();

    } catch ( \Exception $e ) {
        while ( ob_get_level() > $ob_level ) {
            if ( ! @ob_end_clean() ) {
                ob_clean();
            }
        }
        throw $e;
    }
}

/**
 * @param int $post_id
 *
 * @return void
 */
function output_fb_widget( $post_id ) {
    $data = container()->get( MyPopup::class )->get_post_meta( $post_id, 'my_popup_display_widget_facebook', true );
    if ( ! empty( $data['value'] ) ) {
        ?>
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v4.0"></script>
        <div class="fb-page"
             data-href="<? echo $data['value'] ?>"
             data-width="340"
             data-show-facepile="false"
             data-adapt-container-width="true"></div>
        <?php
    }
}

/**
 * @param int $post_id
 *
 * @return void
 */
function output_vk_widget( $post_id ) {
    $data = container()->get( MyPopup::class )->get_post_meta( $post_id, 'my_popup_display_widget_vkontakte', true );
    if ( ! empty( $data['value'] ) ) {
        ?>
        <script src="https://vk.com/js/api/openapi.js?162"></script>
        <div id="vk_groups"></div>
        <script>
            VK.Widgets.Group("vk_groups", {mode: 1, width: "250"}, '<?php echo esc_js( $data['value'] ) ?>');
        </script>
        <?php
    }
}

/**
 * @param int $post_id
 *
 * @return void
 */
function output_ok_widget( $post_id ) {
    $data = container()->get( MyPopup::class )->get_post_meta( $post_id, 'my_popup_display_widget_odnoklassniki', true );
    if ( ! empty( $data['value'] ) ) {
        ?>
        <div id="ok_group_widget"></div>
        <script>
            !function (d, id, did, st) {
                var js = d.createElement("script");
                js.src = "https://connect.ok.ru/connect.js";
                js.onload = js.onreadystatechange = function () {
                    if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
                        if (!this.executed) {
                            this.executed = true;
                            setTimeout(function () {
                                OK.CONNECT.insertGroupWidget(id, did, st);
                            }, 0);
                        }
                    }
                };
                d.documentElement.appendChild(js);
            }(document, "ok_group_widget", "<?php echo $data['value'] ?>", '{"width":250,"height":135}');
        </script>
        <?php
    }
}

/**
 * @param int $post_id
 *
 * @return void
 */
function output_tw_widget( $post_id ) {
    $data = container()->get( MyPopup::class )->get_post_meta( $post_id, 'my_popup_display_widget_twitter', true );
    if ( ! empty( $data['value'] ) ) {
        ?>
        <a class="twitter-timeline" data-width="300" data-height="400" href="<? echo $data['value'] ?>?ref_src=twsrc%5Etfw"></a>
        <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        <?php
    }
}

/**
 * @param int $post_id
 *
 * @return void
 */
function output_pn_widget( $post_id ) {
    $data = container()->get( MyPopup::class )->get_post_meta( $post_id, 'my_popup_display_widget_pinterest', true );
    if ( ! empty( $data['value'] ) ) {
        ?>
        <a data-pin-do="embedBoard" data-pin-board-width="280" data-pin-scale-height="400" data-pin-scale-width="140" href="<? echo $data['value'] ?>"></a>
        <script async defer src="//assets.pinterest.com/js/pinit.js"></script>
        <?php
    }
}

/**
 * @return bool
 */
function is_preview_mode() {
    return container()->get( MyPopup::class )->is_preview_mode();
}

/**
 * Escape JSON for use on HTML or attribute text nodes.
 *
 * @param string $json JSON to escape.
 * @param bool   $html True if escaping for HTML text node, false for attributes. Determines how quotes are handled.
 *
 * @return string
 */
function esc_json( $json, $html = false ) {
    return _wp_specialchars(
        $json,
        $html ? ENT_NOQUOTES : ENT_QUOTES,
        'UTF-8',
        true
    );
}

/**
 * @param $string
 *
 * @return bool
 */
function is_url( $string ) {
    return false !== filter_var( $string, FILTER_VALIDATE_URL );
}

/**
 * @param string $val
 * @param array  $params
 *
 * @return string
 */
function get_image( $val, array $params = [] ) {
    $params = wp_parse_args( $params, [
        'width'   => '',
        'height'  => '',
        'classes' => '',
        'size'    => 'thumbnail',
    ] );

    if ( is_numeric( $val ) && wp_attachment_is_image( $val ) ) {
        return wp_get_attachment_image(
            $val,
            $params['size'],
            false,
            [ 'class' => $params['classes'] ]
        );
    }

    if ( is_url( $val ) ) {
        $classes    = is_array( $params['classes'] ) ? implode( ' ', $params['classes'] ) : $params['classes'];
        $attributes = array_map( 'esc_attr', (array) apply_filters( 'my_popup:image_attributes', [
            'width'  => $params['width'],
            'height' => $params['height'],
            'src'    => $val,
            'class'  => rtrim( "mypopup-icon__image $classes" ),
        ] ) );

        $html = '<img';
        foreach ( $attributes as $name => $value ) {
            $html .= " {$name}=\"{$value}\"";
        }
        $html .= ' />';

        return $html;
    }

    // assuming direct svg string
    return $val;
}


/**
 * @param string $color   expected format #000000
 * @param int    $opacity 1-100 range
 *
 * @return string
 */
function hex_to_rgb( $color, $opacity = null ) {
    $color = '#' . ltrim( $color, '#' );
    [ $r, $g, $b ] = sscanf( $color, "#%02x%02x%02x" );
    if ( null === $opacity ) {
        return "rgb($r,$g,$b)";
    }
    $opacity = round( ( 100 - max( 0, min( 100, (int) $opacity ) ) ) / 100, 2 );

    return "rgba($r,$g,$b,$opacity)";
}


/**
 * @param string $name
 *
 * @return void
 */
function display_help_link( $name ) {
    $url = "https://support.wpshop.ru/docs/plugins/my-popup/settings/#{$name}";
    printf( '<a href="%s" target="_blank" rel="noopener" class="wpshop-meta-ico-help" title="%s">?</a>', $url, __( 'Help', Plugin::TEXT_DOMAIN ) );
}

/**
 * @param int    $post_id
 * @param string $key
 * @param bool   $single
 *
 * @return mixed
 */
function get_post_meta_with_null( $post_id, $key, $single = false ) {
    add_filter( 'default_post_metadata', '__return_null' );
    $result = get_post_meta( $post_id, $key, $single );
    remove_filter( 'default_post_metadata', '__return_null' );

    return $result;
}

/**
 * @return string|void
 */
function get_settings_page_url() {
    return admin_url( 'edit.php?post_type=my_popup&page=' . AdminMenu::SETTINGS_SLUG );
}

/**
 * @return string
 */
function get_preview_base_url() {
    $url = set_url_scheme( get_option( 'siteurl' ), 'http' );
    $url = str_replace( 'http:', '', $url );

    return trailingslashit( $url );
}

/**
 * @param WP_Post $post
 *
 * @return string|null
 */
function get_popup_id( $post ) {
    if ( $post = get_post( $post ) ) {
        return 'mypopup_' . md5( $post->ID );
    }

    return null;
}
