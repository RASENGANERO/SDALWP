<?php

namespace Wpshop\PluginMyPopup;

class AdminMenu {

    use TemplateRendererTrait;

    const SETTINGS_SLUG = 'my-popup-settings';

    /**
     * @return void
     */
    public function init() {
        add_action( 'admin_menu', [ $this, '_add_submenu' ] );
    }

    /**
     * @return void
     */
    public function _add_submenu() {
        add_submenu_page(
            'edit.php?post_type=' . MyPopup::POST_TYPE,
            __( 'Settings', Plugin::TEXT_DOMAIN ),
            __( 'Settings', Plugin::TEXT_DOMAIN ),
            'manage_options',
            self::SETTINGS_SLUG,
            [ $this, 'render_info' ]
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
