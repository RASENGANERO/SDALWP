<?php

namespace Wpshop\ExpertReview\Support;

class YTurboSupport {

    /**
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, '_init_support' ] );

        do_action( __METHOD__, $this );
    }

    /**
     * @return void
     */
    public function _init_support() {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $yturbo_options = get_option( 'yturbo_options' );
        if ( has_filter( 'do_feed_' . $yturbo_options['ytrssname'] ) ) {
            add_filter( 'expert_review_likes_prevent_render', '__return_true' );
        }
    }

    /**
     * @return bool
     */
    public function is_enabled() {
        return in_array(
            'rss-for-yandex-turbo/rss-for-yandex-turbo.php',
            apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
        );
    }

    /**
     * @return bool
     */
    public static function is_feed() {
        $yturbo_options = (array) get_option( 'yturbo_options', [] );
        if ( isset( $yturbo_options['ytrssname'] ) ) {
            return is_feed( $yturbo_options['ytrssname'] );
        }

        return false;
    }
}
