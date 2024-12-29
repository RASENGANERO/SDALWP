<?php

namespace Wpshop\PluginMyPopup\Support;

class ContactForm7Support {

    /**
     * @return void
     */
    public function init() {
        if ( ! in_array( 'contact-form-7/wp-contact-form-7.php', (array) get_option( 'active_plugins', [] ) ) ) {
            return;
        }

        add_action( 'wp_enqueue_scripts', function () {
            wp_add_inline_script( 'my-popup-scripts', $this->js(), 'before' );
        } );
    }

    /**
     * @return string
     * @see https://github.com/takayukister/contact-form-7/blob/master/includes/js/src/index.js
     */
    protected function js() {
        $close_form_js = '';
        if ( $close_timeout = (int) apply_filters( 'my_popup:wpcf7_close_timout', 2500 ) ) {
            $close_form_js = <<<"JS"
    form.addEventListener('wpcf7mailsent', function (e) {
        setTimeout(function () {
            myPopupClose(form);
        }, {$close_timeout});
    });
JS;
        }

        return <<<"JS"
document.addEventListener('my_popup_show', function (e) {
    if (typeof wpcf7 === 'undefined') {
        return;
    }
    var form = e.detail.\$popup.find('.wpcf7 > form').get(0);
    if (!form) {
        return;
    }
    wpcf7.init(form);
    form.closest('.wpcf7').classList.replace('no-js', 'js');
{$close_form_js}
});
JS;
    }
}
