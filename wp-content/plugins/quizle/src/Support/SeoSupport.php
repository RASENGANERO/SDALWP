<?php

namespace Wpshop\Quizle\Support;

use function Wpshop\Quizle\is_quizle_result_page;

class SeoSupport {

    /**
     * @return void
     */
    public function init() {
        add_filter( 'wp_robots', [ $this, '_noindex' ], PHP_INT_MAX - 9 );
        add_filter( 'rank_math/frontend/robots', [ $this, '_rank_math_noindex' ] );
        add_filter( 'aioseo_robots_meta', [ $this, '_aioseo_noindex' ] );
    }


    /**
     * @param array $robots
     *
     * @return array
     */
    public function _noindex( $robots ) {
        if ( is_quizle_result_page() ) {
            return [
                'noindex' => true,
            ];
        }

        return $robots;
    }

    /**
     * @param array $robots
     *
     * @return array
     */
    public function _rank_math_noindex( $robots ) {
        if ( is_quizle_result_page() ) {
            return [ 'index' => 'noindex' ];
        }

        return $robots;
    }

    /**
     * @param array $robots
     *
     * @return array
     */
    public function _aioseo_noindex( $robots ) {
        if ( is_quizle_result_page() ) {
            return [ 'index' => 'noindex' ];
        }

        return $robots;
    }
}
