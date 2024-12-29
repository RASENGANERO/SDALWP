<?php

namespace Wpshop\PluginMyPopup;

class MyPopupPreview {

    use TemplateRendererTrait;

    /**
     * @var MyPopup
     */
    protected $popup;

    /**
     * MyPopupPreview constructor.
     *
     * @param MyPopup $popup
     */
    public function __construct( MyPopup $popup ) {
        $this->popup = $popup;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, '_show_preview_page' ] );

        add_action( 'my_popup:preview_footer', 'wp_enqueue_global_styles', 1 );
        add_action( 'my_popup:preview_footer', 'wp_maybe_inline_styles', 1 );
        add_action( 'my_popup:preview_footer', 'wp_print_footer_scripts', 20 );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function _show_preview_page() {
        // @todo add filter and localize to use in script
//		$param = apply_filters( 'my_popup_preview_page_param', 'act' );
//		$page  = apply_filters( 'my_popup_preview_page', 'preview-popup' );

        $param = 'act';
        $page  = 'preview-popup';

        if ( isset( $_GET[ $param ] ) &&
             $_GET[ $param ] === $page &&
             isset( $_GET['id'] )
        ) {
            if ( ! defined( 'DONOTCACHEPAGE' ) ) {
                define( 'DONOTCACHEPAGE', true );
            }
            add_filter( 'show_admin_bar', '__return_false' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You have no permission to preview popup.', MY_POPUP_TEXTDOMAIN ) );
            }

            if ( ! ( $post = get_post( $_GET['id'] ) ) ) {
                wp_die( __( 'Unable to preview not founded popup.', MY_POPUP_TEXTDOMAIN ) );
            }

            if ( $post->post_type !== MyPopup::POST_TYPE ) {
                wp_die( __( 'Unable to preview not "my_popup" post type.', MY_POPUP_TEXTDOMAIN ) );
            }

            $this->popup->set_preview_mode( true );

            echo $this->render( 'preview', [
                'popup' => $this->popup,
                'post'  => $post,
            ] );
            die;
        }
    }
}
