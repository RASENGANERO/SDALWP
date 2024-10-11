<?php

if ( ! function_exists( 'array_key_first' ) ) {
    /**
     * @param array $arr
     *
     * @return int|string|null
     * @see https://www.php.net/manual/en/function.array-key-first.php
     */
    function array_key_first( array $arr ) {
        foreach ( $arr as $key => $unused ) {
            return $key;
        }

        return null;
    }
}

if ( ! function_exists( 'mb_str_split' ) ) {
    /**
     * @param string      $string
     * @param int         $length
     * @param string|null $encoding
     *
     * @return array
     * @see https://www.php.net/manual/en/function.mb-str-split.php
     */
    function mb_str_split( $string = '', $length = 1, $encoding = null ) {
        $split = [];
        if ( ! empty( $string ) ) {
            $mb_strlen = mb_strlen( $string, $encoding );
            for ( $pi = 0 ; $pi < $mb_strlen ; $pi += $length ) {
                $substr = mb_substr( $string, $pi, $length, $encoding );
                if ( ! empty( $substr ) || '0' === $substr ) {
                    $split[] = $substr;
                }
            }
        }

        return $split;
    }
}
