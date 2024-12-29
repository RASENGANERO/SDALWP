<?php

namespace Wpshop\Quizle\Integration;

use Wpshop\Quizle\Admin\Settings;

class ReCaptcha {

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

    /**
     * @return void
     */
    public function init() {
        add_filter( 'quizle/shortcode/options', [ $this, '_append_options' ] );
    }

    /**
     * @return bool
     */
    public function enabled() {
        return (bool) $this->settings->get_value( 'grecaptcha.enabled' );
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function _append_options( $options ) {
        if ( $this->enabled() ) {
            $options['gr_enabled']  = 1;
            $options['gr_site_key'] = $this->settings->get_value( 'grecaptcha.site_key' );
        }

        return $options;
    }

    /**
     * @param string $response
     *
     * @return bool
     */
    public function verify( $response ) {
        $resp = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
            'body'      => [
                'secret'   => $this->settings->get_value( 'grecaptcha.secret_key' ),
                'response' => $response,
            ],
            'sslverify' => false,
        ] );

        $result = json_decode( wp_remote_retrieve_body( $resp ), true );

        return ! empty( $result['success'] );
    }
}
