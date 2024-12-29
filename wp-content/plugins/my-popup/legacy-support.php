<?php

if ( ! function_exists( 'wp_parse_list' ) ) {
    function wp_parse_list( $list ) {
        if ( ! is_array( $list ) ) {
            return preg_split( '/[\s,]+/', $list, - 1, PREG_SPLIT_NO_EMPTY );
        }

        return $list;
    }
}
