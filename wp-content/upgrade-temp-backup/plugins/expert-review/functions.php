<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @param string $template
 * @param array  $params
 *
 * @return false|string
 * @throws Exception
 */
function er_render_template( $template, array $params = [] ) {
    $file = _er_locate_template( $template );

    return er_ob_get_content( function ( $__params__, $__file__ ) {
        extract( $__params__, EXTR_OVERWRITE );
        require $__file__;
    }, $params, $file );
}

/**
 * @param callable $fn
 *
 * @return false|string
 * @throws \Exception
 */
function er_ob_get_content( $fn ) {
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
 * @param string|array $template_names
 *
 * @return string|null
 */
function _er_locate_template( $template_names ) {
    $located = null;
    foreach ( (array) $template_names as $template_name ) {
        if ( ! $template_name ) {
            continue;
        }
        if ( file_exists( STYLESHEETPATH . '/expert-review/' . $template_name ) ) {
            $located = STYLESHEETPATH . '/expert-review/' . $template_name;
            break;
        } elseif ( file_exists( TEMPLATEPATH . '/expert-review/' . $template_name ) ) {
            $located = TEMPLATEPATH . '/expert-review/' . $template_name;
            break;
        } elseif ( file_exists( dirname( EXPERT_REVIEW_FILE ) . '/template-parts/' . $template_name ) ) {
            $located = dirname( EXPERT_REVIEW_FILE ) . '/template-parts/' . $template_name;
            break;
        }
    }

    return $located;
}
