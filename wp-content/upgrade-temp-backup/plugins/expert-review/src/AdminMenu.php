<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Shortcodes\Poll;

class AdminMenu {

    use TemplateRendererTrait;

    const MAIN_SLUG = 'wpshop-expert-review';

    /**
     * @var string|null
     */
    protected $plugin_file;

    /**
     * @param string $plugin_file
     *
     * @return void
     */
    public function init( $plugin_file ) {
        $this->plugin_file = $plugin_file;

        add_action( 'admin_menu', [ $this, '_setup_menu' ] );

        do_action( __METHOD__, $this );
    }

    /**
     * @return void
     */
    public function _setup_menu() {
        add_menu_page(
            'Expert Review',
            'Expert Review',
            'manage_options',
            self::MAIN_SLUG,
            '',
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%" style="fill:#a0a5aa"><path d="M242.5 224.3c-51.7 0-93.7-39.9-93.7-88.9s42.1-89 93.7-89 93.7 39.9 93.7 88.9-42 89-93.7 89zm0-130c-23.8 0-43.3 18.4-43.3 41s19.4 41 43.3 41 43.3-18.4 43.3-41-19.4-41-43.3-41zm186.7 73.6c-1 9.4-5.7 14.1-14 14.1h-11.6c-8.3 0-13.1-4.7-14-14.1l-9.3-91c-.6-5.8-.9-9.6-.9-11.3 0-12.8 9.9-19.2 29.8-19.2 19.8 0 29.8 6.4 29.8 19.2 0 3.5-.2 7.4-.6 11.3l-9.2 91zM409 256.5c-7.6 0-14.1-2.6-19.6-7.9-5.6-5.3-8.3-11.6-8.3-19s2.7-13.7 8.3-19c5.6-5.3 12.1-7.9 19.6-7.9 7.8 0 14.4 2.6 20 7.9 5.6 5.3 8.3 11.6 8.3 19s-2.7 13.7-8.3 19c-5.7 5.3-12.3 7.9-20 7.9zm-166.5 5.7c-97 0-175.7 71.5-175.7 159.7 0 12 1.5 23.6 4.3 34.9 53.2 6.4 111 10 171.5 10s118.2-3.5 171.5-10c2.7-11.3 4.3-22.9 4.3-34.9-.1-88.3-78.8-159.7-175.9-159.7zm112.8 154.5c-33.4 3.3-73.2 5.1-112 5.1s-78.5-1.9-111.9-5.2c-.5-5.9-.2-11.9 1.2-18.2 9.9-45.9 48.6-83.1 110.8-83 62.3 0 100.8 37.2 110.7 83.2 1.3 6.2 1.7 12.2 1.2 18.1z"/></svg>'),
            76.99
        );
        add_submenu_page(
            self::MAIN_SLUG,
            'Expert Review',
            'Expert Review',
            'manage_options',
            self::MAIN_SLUG,
            [ $this, 'render_info' ],
            100
        );
        add_submenu_page(
            self::MAIN_SLUG,
            __( 'Polls', Plugin::TEXT_DOMAIN ),
            __( 'Polls', Plugin::TEXT_DOMAIN ),
            'manage_options',
            'edit.php?post_type=' . Poll::POST_TYPE,
            '',
            110
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function render_info() {
        echo $this->render( 'info' );
    }
}
