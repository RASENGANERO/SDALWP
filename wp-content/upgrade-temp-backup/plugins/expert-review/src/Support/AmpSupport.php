<?php

namespace Wpshop\ExpertReview\Support;

use Wpshop\ExpertReview\Shortcodes;

class AmpSupport {

    /**
     * @return void
     */
    public function init() {
        if ( $this->is_enabled() ) {
            $this->disable_shortcodes_on_amp();
        }

        do_action( __METHOD__, $this );
    }

    /**
     * @return void
     */
    protected function disable_shortcodes_on_amp() {
        if ( $this->is_amp() ) {
            $shortcodes = [
                Shortcodes::SHORTCODE_EXPERT_REVIEW,
                Shortcodes::SHORTCODE_LIKES,
                Shortcodes::SHORTCODE_LIKES_RATE,
                Shortcodes::SHORTCODE_FAQ,
                Shortcodes::SHORTCODE_POLL,
            ];
            foreach ( $shortcodes as $shortcode ) {
                remove_shortcode( $shortcode );
                add_shortcode( $shortcode, '__return_empty_string' );
            }
        }
    }

    /**
     * @return bool
     */
    public function is_amp() {
        if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
            return true;
        }

        if ( apply_filters( 'expert_review:amp_support:check_request_uri', true ) &&
             ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], '/amp/' ) ) ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function is_enabled() {
        return in_array(
            'amp/amp.php',
            (array) apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) )
        );
    }
}
