<?php

namespace Wpshop\PluginMyPopup;

use WP_Post;

class DefaultHooks {

    /**
     * @return void
     */
    public function init() {
        add_filter( 'my_popup:content', 'trim' );
        add_filter( 'my_popup:content', 'wpautop' );
        add_filter( 'my_popup:content', [ $this, '_do_shortcode' ], 10, 2 );

        add_filter( 'my_popup:social_profile_link', [ $this, '_social_link' ], 10, 2 );

        add_filter( 'my_popup:get_metadata', function ( $result, $key ) {
            if ( $key === 'my_popup_prepared_code' ) {
                if ( ! is_array( $result ) ) {
                    $result = [ $result ];
                }
            }

            return $result;
        }, 10, 2 );

        add_filter( 'my_popup:shortcode_post_data:preview', function ( $key ) {
            return sprintf( __( '<{%s} will be placed automatically>', Plugin::TEXT_DOMAIN ), $key );
        } );

        add_filter( 'plugin_action_links', function ( $links, $plugin_file ) {
            if ( MY_POPUP_BASENAME === $plugin_file ) {
                $links[] = sprintf( '<a href="%s">%s</a>', get_settings_page_url(), __( 'Settings' ) );
            }

            return $links;
        }, 10, 2 );
    }

    /**
     * @param string  $content
     * @param WP_Post $post
     *
     * @return string
     */
    public function _do_shortcode( $content, $post ) {
        $post_gl         = $GLOBALS['post'] ?? null;
        $GLOBALS['post'] = $post;
        $result          = do_shortcode( $content );
        $GLOBALS['post'] = $post_gl;

        return $result;
    }

    /**
     * @param string $link
     * @param string $profile
     *
     * @return string
     */
    public function _social_link( $link, $profile ) {
        switch ( $profile ) {
            case 'whatsapp':
                $link = 'https://api.whatsapp.com/send?phone=' . $link;
                break;
            case 'viber':
                $link = 'viber://chat?number=' . $link;
                break;
            default:
                break;
        }

        return $link;
    }
}
