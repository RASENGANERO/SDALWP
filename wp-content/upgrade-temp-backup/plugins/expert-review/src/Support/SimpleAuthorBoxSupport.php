<?php

namespace Wpshop\ExpertReview\Support;

use stdClass;
use WP_User;

class SimpleAuthorBoxSupport {

    /**
     * @return void
     */
    public function init() {
        if ( $this->is_enabled() ) {
            add_filter( 'expert_review:expert_avatar_url', [ $this, '_avatar_url' ], 9, 2 );
            add_filter( 'expert_review:expert_avatar_url:preview', [ $this, '_avatar_url_preview' ], 9 );
        }

        do_action( __METHOD__, $this );
    }

    /**
     * @param string $url
     * @param array  $atts
     *
     * @return string
     */
    public function _avatar_url( $url, array $atts ) {
        if ( $atts['expert_type'] === 'user_id' ) {
            return get_user_meta( $atts['expert_id'], 'sabox-profile-image', true );
        }

        return $url;
    }

    /**
     * @param WP_User|stdClass $user
     *
     * @return string
     */
    public function _avatar_url_preview( $user ) {
        return get_user_meta( $user->ID, 'sabox-profile-image', true );
    }

    /**
     * @return bool
     */
    public function is_enabled() {
        return in_array(
            'simple-author-box/simple-author-box.php',
            apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
        );
    }
}
