<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Wpshop\Quizle;

use Psr\Container\ContainerInterface;
use WP_Post;
use WPShop\Container\Container;
use Wpshop\Quizle\Admin\MenuPage;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Db\Database;
use function WPShop\WPCommunity\get_setting;

const COOKIE_UID  = 'quizle-uid';
const COOKIE_SALT = 'quizle-salt';

const RESULT_REQUEST_VAR = 'quizle-result';

const CACHE_GROUP = 'quizle';

/**
 * @return ContainerInterface
 */
function container() {
    static $container;
    if ( ! $container ) {
        $config    = require dirname( __DIR__ ) . '/config/config.php';
        $init      = require dirname( __DIR__ ) . '/config/container.php';
        $container = new Container( $init( $config ) );
    }

    return $container;
}

/**
 * @return void
 */
function init_i18n() {
    $text_domain = QUIZLE_TEXTDOMAIN;
    $locale      = ( is_admin() && function_exists( 'get_user_locale' ) ) ? get_user_locale() : get_locale();
    $mo_file     = dirname( QUIZLE_FILE ) . "/languages/{$text_domain}-{$locale}.mo";

    if ( is_readable( $mo_file ) ) {
        $loaded = load_textdomain( $text_domain, $mo_file );
        if ( ! $loaded ) {
            trigger_error( "Unable to load translations for \"{$text_domain}\" text domain", E_USER_NOTICE );
        }
    }
}

/**
 * @param string $plugin
 *
 * @return void
 */
function redirect_on_activated( $plugin ) {
    if ( $plugin === QUIZLE_BASENAME && ! container()->get( Settings::class )->verify() ) {
        $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
        $action        = $wp_list_table->current_action();
        if ( $action === 'activate' ) {
            flush_rewrite_rules( false );
            wp_redirect( get_settings_page_url() );
            die;
        }
    }
}

/**
 * @param array $actions
 *
 * @return array
 */
function add_settings_plugin_action( $actions ) {
    array_unshift( $actions, sprintf( '<a href="%s">%s</a>', get_settings_page_url(), __( 'Settings', QUIZLE_TEXTDOMAIN ) ) );

    return $actions;
}

/**
 * @return string
 */
function admin_icon_url() {
    return 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path d="M177.4 154.7c8.4-14.9 12.6-33.1 12.6-54.7 0-21.7-4.2-39.9-12.7-54.8-8.5-14.9-19.9-26.1-34.3-33.7C128.8 3.8 112.7 0 95 0 77.2 0 61.1 3.8 46.8 11.5c-14.3 7.6-25.7 18.9-34.2 33.7C4.2 60.1 0 78.3 0 100c0 21.5 4.2 39.8 12.6 54.7 8.5 14.8 19.8 26.1 34.2 33.8C61.1 196.2 77.2 200 95 200h105v-45.3h-22.6zm-46.6-25.1c-3.1 8-7.6 14.1-13.6 18.3-6 4.1-13.4 6.2-22.1 6.2-8.8 0-16.2-2.1-22.2-6.2-6-4.2-10.5-10.3-13.6-18.3-3.1-8-4.6-17.9-4.6-29.6s1.5-21.5 4.6-29.5c3.1-8 7.7-14.1 13.6-18.3 6-4.2 13.5-6.3 22.2-6.3 8.8 0 16.2 2.1 22.1 6.3 6 4.1 10.6 10.2 13.6 18.3 3.1 8 4.7 17.8 4.7 29.5s-1.6 21.5-4.7 29.6z" fill="currentColor"/></svg>' );
}

/**
 * Activation function
 *
 * @return void
 * @see \register_activation_hook()
 */
function activate() {
    add_option( 'quizle-log-level', Logger::DISABLED );
    container()->get( Database::class )->install();
    update_option( 'quizle--flush_rewrite_rules', 1 );
}

/**
 * Deactivation function
 *
 * @return void
 * @see \register_deactivation_hook()
 */
function deactivate() {
    flush_rewrite_rules();
}

/**
 * Uninstallation function
 *
 * @return void
 * @see \register_uninstall_hook()
 */
function uninstall() {
    $settings = container()->get( Settings::class );
    if ( $settings->get_value( 'clear_database' ) ) {
        container()->get( Database::class )->uninstall();
        $settings->clear_database();
        delete_option( 'quizle-log-level' );
    }
}

/**
 * @return string
 */
function get_settings_page_url() {
    if ( container()->get( Settings::class )->verify() ) {
        return add_query_arg( [
            'post_type' => Quizle::POST_TYPE,
            'page'      => MenuPage::SETTINGS_SLUG,
        ], admin_url( 'edit.php' ) );
    }

    return add_query_arg( 'page', MenuPage::SETTINGS_SLUG, admin_url( 'admin.php' ) );
}

/**
 * @param string    $json
 * @param bool|null $associative
 * @param int       $depth
 * @param int       $flags
 *
 * @return mixed|null
 * @see \json_decode()
 */
function json_decode( $json, $associative = null, int $depth = 512, int $flags = 0 ) {
    $result = \json_decode( $json, $associative, $depth, $flags );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        trigger_error( 'Json parse error: ' . json_last_error_msg(), E_USER_WARNING );

        return null;
    }

    return $result;
}

/**
 * @param string      $identity
 * @param string|null $post_status
 *
 * @return WP_Post|null
 */
function get_quizle( $identity, $post_status = 'publish' ) {
    return get_posts( [
        'name'           => $identity,
        'post_type'      => Quizle::POST_TYPE,
        'post_status'    => $post_status,
        'posts_per_page' => 1,
    ] )[0] ?? null;
}

/**
 * @param string $token
 *
 * @return QuizleResult|null
 */
function get_result_by_token( $token ) {
    if ( $token ) {
        return container()->get( Database::class )->get_quizle_result_by_token( $token );
    }

    return null;
}

/**
 * @param int|WP_Post $quizle
 *
 * @return mixed
 */
function get_quizle_type( $quizle ) {
    $id = $quizle;
    if ( $quizle instanceof WP_Post ) {
        $id = $quizle->ID;
    }

    return get_post_meta( $id, 'quizle-type', true );
}

/**
 * @param int $id
 *
 * @return string
 */
function get_edit_quizle_result_url( $id ) {
    return add_query_arg( [
        'post_type'      => Quizle::POST_TYPE,
        'page'           => MenuPage::RESULT_LIST_SLUG,
        'quiz_result_id' => $id,
    ], admin_url( 'edit.php' ) );
}

/**
 * @param string $token
 * @param int    $quiz_id
 *
 * @return string
 */
function get_quizle_result_url( $token, $quiz_id ) {
    return add_query_arg( RESULT_REQUEST_VAR, $token, get_permalink( $quiz_id ) );
}

/**
 * @param int $quizle_id
 *
 * @return string
 */
function get_quizle_analytic_url( $quizle_id ) {
    return add_query_arg( [
        'post_type' => Quizle::POST_TYPE,
        'page'      => 'analytics',
        'id'        => $quizle_id,
    ], admin_url( 'edit.php' ) );
}

/**
 * @return bool
 */
function is_quizle_result_page() {
    if ( is_singular( Quizle::POST_TYPE ) && array_key_exists( RESULT_REQUEST_VAR, $_REQUEST ) ) {
        return true;
    }

    return false;
}

/**
 * @param string               $string
 * @param string|string[]|null $allowed_tags
 * @param bool                 $remove_breaks
 *
 * @return string
 * @see wp_strip_all_tags()
 */
function strip_tags_and_scripts( $string, $allowed_tags = null, $remove_breaks = false ) {
    $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
    $string = \strip_tags( $string, $allowed_tags );
    if ( $remove_breaks ) {
        $string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
    }

    return trim( $string );
}

/**
 * @param string $value
 *
 * @return string
 */
function sanitize_textarea( $value ) {
    return strip_tags_and_scripts(
        $value,
        '<p><h1><h2><h3><h4><h5><h6><a><strong><i><del><ins><span><br>',
        true
    );
}

/**
 * @param string $phone
 *
 * @return string
 */
function sanitize_phone( $phone ) {
    return preg_replace( '/[^0-9+]/', '', $phone );
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
 * @param string $slug
 * @param string $name
 * @param array  $args
 *
 * @return false|void
 * @see \get_template_part()
 */
function get_template_part( $slug, $name = null, $args = [] ) {
    do_action( "get_template_part_{$slug}", $slug, $name, $args );

    $templates = [];
    $name      = (string) $name;
    if ( '' !== $name ) {
        $templates[] = "{$slug}-{$name}.php";
    }

    $templates[] = "{$slug}.php";

    do_action( 'get_template_part', $slug, $name, $templates, $args );

    if ( ! locate_template( $templates, true, false, $args ) ) {
        return false;
    }
}

/**
 * @param string|array $template_names
 * @param bool         $load
 * @param bool         $require_once
 * @param array        $args
 *
 * @return string|null
 * @see \locate_template()
 */
function locate_template( $template_names, $load = false, $require_once = true, $args = [] ) {
    $located = null;
    foreach ( (array) $template_names as $template_name ) {
        if ( ! $template_name ) {
            continue;
        }

        // prevent to locate admin templates from other places
        if ( 'admin/' === substr( $template_name, 0, 6 ) ) {
            if ( file_exists( dirname( QUIZLE_FILE ) . '/template-parts/' . $template_name ) ) {
                $located = dirname( QUIZLE_FILE ) . '/template-parts/' . $template_name;
                break;
            }
            continue;
        }

        if ( file_exists( STYLESHEETPATH . '/' . QUIZLE_SLUG . '/' . $template_name ) ) {
            $located = STYLESHEETPATH . '/' . QUIZLE_SLUG . '/' . $template_name;
            break;
        } elseif ( file_exists( TEMPLATEPATH . '/' . QUIZLE_SLUG . '/' . $template_name ) ) {
            $located = TEMPLATEPATH . '/' . QUIZLE_SLUG . '/' . $template_name;
            break;
        } elseif ( file_exists( dirname( QUIZLE_FILE ) . '/template-parts/' . $template_name ) ) {
            $located = dirname( QUIZLE_FILE ) . '/template-parts/' . $template_name;
            break;
        }
    }

    $located = apply_filters( 'quizle/locate_template/located', $located, $template_name, $args );
    if ( ! file_exists( $located ) ) {
        trigger_error( 'Unable to locate template file "' . $located . '" from template names "' . implode( '", "', $template_names ) . '"' );

        return null;
    }

    if ( $load && '' !== $located ) {
        load_template( $located, $require_once, $args );
    }

    return $located;
}

/**
 * @param callable $fn
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
 * @return string|null
 */
function generate_quizle_name() {
    $name = apply_filters( 'quizle/functions/generate_name', uniqid() );

    for ( $i = 0 ; $i < 1000 ; $i ++ ) { // try to check if unique, don't worry about race condition
        $posts = get_posts( [
            'name'           => $name,
            'post_type'      => Quizle::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => 1,
        ] );
        if ( ! $posts ) {
            return $name;
        }
    }

    return null;
}

/**
 * @param array  $attributes
 * @param string $auto_escape
 *
 * @return string
 */
function build_attributes( array $attributes, $auto_escape = false ) {
    array_walk( $attributes, function ( &$v, $k ) use ( $auto_escape ) {
        if ( ! is_numeric( $k ) ) {
            if ( null !== $v ) {
                if ( $auto_escape ) {
                    $v = $k . '="' . esc_attr( $v ) . '"';
                } else {
                    $v = $k . '="' . $v . '"';
                }
            }
        }
    } );
    $attributes = array_filter( $attributes, function ( $val ) {
        return $val !== null;
    } );
    $attributes = implode( ' ', $attributes );

    return $attributes;
}

/**
 * @return bool
 */
function is_debug() {
    return defined( 'QUIZLE_DEV_MODE' ) && QUIZLE_DEV_MODE;
}

/**
 * @param int $length
 *
 * @return string
 */
function generate_string( $length ) {
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen( $characters );
    $result           = '';
    for ( $i = 0 ; $i < $length ; $i ++ ) {
        $result .= $characters[ rand( 0, $charactersLength - 1 ) ];
    }

    return $result;
}

/**
 * @param int $length
 *
 * @return string
 */
function generate_string_lower( $length ) {
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen( $characters );
    $result           = '';
    for ( $i = 0 ; $i < $length ; $i ++ ) {
        $result .= $characters[ rand( 0, $charactersLength - 1 ) ];
    }

    return $result;
}

/**
 * @return string
 */
function get_ip() {
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

/**
 * Increases or decreases the brightness of a color by a percentage of the current brightness.
 *
 * @param string|array $color          formats: #FFF, #FFFFFF, FFF, FFFFFF, or array like [126, 129, 129]
 * @param float        $adjust_percent A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
 *
 * @return string in format #ffffff
 */
function adjust_brightness( $color, $adjust_percent ) {
    if ( is_string( $color ) ) {
        $color = ltrim( $color, '#' );

        if ( strlen( $color ) == 3 ) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        if ( strlen( $color ) == 3 ) {
            $color = str_repeat( substr( $color, 0, 1 ), 2 ) .
                     str_repeat( substr( $color, 1, 1 ), 2 ) .
                     str_repeat( substr( $color, 2, 1 ), 2 );
        }

        $color = array_map( 'hexdec', str_split( $color, 2 ) );
    }

    $result = '#';
    foreach ( $color as $color_part ) {
        $adjustable_limit = $adjust_percent < 0 ? $color_part : 255 - $color_part;
        $adjust_amount    = ceil( $adjustable_limit * $adjust_percent );

        $result .= str_pad( dechex( $color_part + $adjust_amount ), 2, '0', STR_PAD_LEFT );
    }

    return $result;
}

/**
 * @param string $color formats: #fff, #ffffff, fff, ffffff
 *
 * @return float|int
 * @see https://stackoverflow.com/questions/1331591/given-a-background-color-black-or-white-text
 */
function get_yiq( $color ) {
    $color = ltrim( $color, '#' );
    if ( strlen( $color ) == 3 ) {
        $color = str_repeat( substr( $color, 0, 1 ), 2 ) .
                 str_repeat( substr( $color, 1, 1 ), 2 ) .
                 str_repeat( substr( $color, 2, 1 ), 2 );
    }

    [ $r, $g, $b ] = array_map( 'hexdec', str_split( $color, 2 ) );

    return ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000;
}

/**
 * @param string|array $color              formats: #FFF, #FFFFFF, FFF, FFFFFF, or array like [126, 129, 129]
 * @param float        ...$adjust_percents A numbers between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
 *
 * @return string[] strings in format #ffffff
 */
function get_adjusted_colors( $color, ...$adjust_percents ) {
    return array_map( function ( $adjust_percent ) use ( $color ) {
        return adjust_brightness( $color, $adjust_percent );
    }, $adjust_percents );
}

/**
 * @param WP_Post $quizle
 *
 * @return string|null
 */
function get_quizle_height( $quizle ) {
    if ( $height = trim( get_post_meta( $quizle->ID, 'quizle-height', true ) ) ) {
        if ( is_numeric( $height ) ) {
            return "{$height}px";
        }

        return $height;
    }

    return null;
}

/**
 * Retrieve answers from store result data
 *
 * @param array $answers
 *
 * @return array
 */
function retreive_answers( array $answers ) {
    $result = [];
    foreach ( $answers as $answer ) {
        if ( '__text__' === $answer['answer_id'] ) {
            $result[] = $answer['value'];
        } elseif ( '__file__' === $answer['answer_id'] ) {
            foreach ( $answer['value'] as $url ) {
                $result[] = $url;
            }
        } else {
            if ( ! empty( $answer['_checked'] ) ) {
                if ( 'custom' === ( $answer['type'] ?? '' ) ) {
                    $result[] = $answer['_custom_answer'] ?? '';
                } else {
                    $result[] = $answer['name'];
                }
            }
        }
    }

    return $result;
}

/**
 * @param WP_Post|null $quizle if passed - check quizle can save results
 *
 * @return bool
 */
function is_file_upload_allowed( $quizle = null ) {
    // allow for all (if enabled by setting) or for logged in only
    $allowed = \Wpshop\Quizle\container()->get( Settings::class )->get_value( 'file_upload.allow_guest' ) ||
               is_user_logged_in();;

    if ( $quizle ) {
        if ( is_preview() || ! get_post_meta( $quizle->ID, 'save-quizle-contacts-and-results', true ) ) {
            $allowed = false;
        }
    }

    return $allowed;
}

/**
 * @param int $size
 * @param int $precision
 *
 * @return string
 */
function human_filesize( $size, $precision = 2 ) {
    $units = [ 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
    $step  = 1024;
    $i     = 0;
    while ( ( $size / $step ) > 0.9 ) {
        $size = $size / $step;
        $i ++;
    }

    return round( $size, $precision ) . $units[ $i ];
}

/**
 * @return false|string
 */
function get_max_file_uploads() {
    return ini_get( 'max_file_uploads' );
}


/**
 * @return float|int
 */
function get_file_upload_max_size() {
    static $max_size = - 1;

    if ( $max_size < 0 ) {
        // Start with post_max_size.
        $post_max_size = parse_size( ini_get( 'post_max_size' ) );
        if ( $post_max_size > 0 ) {
            $max_size = $post_max_size;
        }

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $upload_max = parse_size( ini_get( 'upload_max_filesize' ) );
        if ( $upload_max > 0 && $upload_max < $max_size ) {
            $max_size = $upload_max;
        }
    }

    return $max_size;
}

/**
 * @param $size
 *
 * @return float
 */
function parse_size( $size ) {
    $unit = preg_replace( '/[^bkmgtpezy]/i', '', $size ); // Remove the non-unit characters from the size.
    $size = preg_replace( '/[^0-9\.]/', '', $size ); // Remove the non-numeric characters from the size.
    if ( $unit ) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round( $size * pow( 1024, stripos( 'bkmgtpezy', $unit[0] ) ) );
    } else {
        return round( $size );
    }
}

/**
 * @param string $content
 * @param string $editor_id
 * @param array  $settings
 *
 * @return \Closure
 */
function quizle_editor_wrap( $content, $editor_id, $settings = [] ) {
    if ( container()->get( Settings::class )->get_value( 'enable_wp_editor' ) ) {
        wp_editor( $content, $editor_id, $settings );

        return function () {
            // do nothing
        };
    }

    return function ( $fallback ) {
        // call fallback function if wp editor disabled
        $fallback();
    };
}
