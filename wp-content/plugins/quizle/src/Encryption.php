<?php

namespace Wpshop\Quizle;

class Encryption {

    /**
     * @var string
     */
    protected $salt;

    /**
     * Constructor
     */
    public function __construct() {
        $salt_length = 8;

        /**
         * Allows to change salt generation
         *
         * @since 1.2.0
         */
        $this->salt = apply_filters(
            'quizle/encryption/salt',
            substr( md5( defined( 'NONCE_SALT' ) ? NONCE_SALT : 'NONCE_SALT' ), 2, $salt_length ),
            $salt_length,
            $this
        );
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, '_init_salt_cookie' ] );
    }

    /**
     * @return void
     */
    public function _init_salt_cookie() {
        setcookie( COOKIE_SALT, $this->salt, 0, '/' );
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function encrypt_text( $text ) {
        return $this->encrypt( base64_encode( rawurlencode( $text ) ) );
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function encrypt( $text ) {
//        $text = rawurldecode( $text );

        $utf8_char_code_at = function ( $str ) {
            $char = mb_substr( $str, 0, 1, 'UTF-8' );

            if ( mb_check_encoding( $char, 'UTF-8' ) ) {
                $ret = mb_convert_encoding( $char, 'UTF-32BE', 'UTF-8' );

                return hexdec( bin2hex( $ret ) );
            } else {
                return null;
            }
        };

        $apply_salt_to_char = function ( $code ) use ( $utf8_char_code_at ) {
            return array_reduce(
                array_map( $utf8_char_code_at, mb_str_split( $this->salt, 1, 'UTF-8' ) ),
                function ( $a, $b ) {
                    return $a ^ $b;
                },
                $code
            );
        };

        $byte_hex = function ( $n ) {
            return substr( '0' . dechex( $n ), - 2 );
        };

        $parts = (array) mb_str_split( $text, 1, 'UTF-8' );
        $parts = array_map( $utf8_char_code_at, $parts );
        $parts = array_map( $apply_salt_to_char, $parts );
        $parts = array_map( $byte_hex, $parts );

        return implode( '', $parts );
    }

    /**
     * @param string $length
     *
     * @return string
     */
    public function generate_salt( $length ) {
        return generate_string( $length );
    }
}
