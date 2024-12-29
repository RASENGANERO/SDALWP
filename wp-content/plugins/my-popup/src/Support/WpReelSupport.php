<?php

namespace Wpshop\PluginMyPopup\Support;

/**
 * @link https://wordpress.org/plugins/wp-reel/
 * @link http://reel360.org/reel
 * @link https://github.com/pisi/Reel/blob/master/jquery.reel.js
 */
class WpReelSupport {

    /**
     * @return void
     */
    public function init() {
        if ( ! in_array( 'wp-reel/wp-reel.php', (array) get_option( 'active_plugins', [] ) ) ) {
            return;
        }

        add_action( 'wp_enqueue_scripts', function () {
            wp_add_inline_script( 'my-popup-scripts', $this->js(), 'before' );
        } );
    }

    /**
     * @return string
     */
    protected function js() {
        return <<<'JS'
jQuery(function ($){
    document.addEventListener('my_popup_show', function (e) {
        $.reel.scan(e.detail.$popup.find('img.reel'));
    });
});
JS;
    }
}
