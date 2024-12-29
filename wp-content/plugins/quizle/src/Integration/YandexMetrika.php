<?php

namespace Wpshop\Quizle\Integration;

use Wpshop\Quizle\Admin\Settings;

/**
 * todo https://yandex.ru/support/metrica/marketplace.html
 */
class YandexMetrika {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    /**pow
     * @return void
     */
    public function init() {
        add_filter( 'quizle/shortcode/options', [ $this, '_append_options' ] );
        add_action( 'wp_head', [ $this, '_insert_counter' ] );
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function _append_options( $options ) {
        if ( $this->settings->get_value( 'integrations.metrika.enabled' ) ) {
            $options['ym_enabled'] = 1;
            $options['ym_counter'] = $this->settings->get_value( 'integrations.metrika.counter' );
        }

        return $options;
    }

    /**
     * @return void
     */
    public function _insert_counter() {
        if ( $this->settings->get_value( 'integrations.metrika.enabled' ) &&
             ! $this->settings->get_value( 'integrations.metrika.disable_code_output' )
        ) {
            \Wpshop\Quizle\get_template_part( 'yandex-metrika', '', [
                'counter' => $this->settings->get_value( 'integrations.metrika.counter' ),
            ] );
        }
    }
}
