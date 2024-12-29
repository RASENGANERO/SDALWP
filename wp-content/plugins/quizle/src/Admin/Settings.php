<?php

namespace Wpshop\Quizle\Admin;

use Wpshop\Settings\AbstractSettings;
use function Wpshop\Quizle\get_adjusted_colors;
use function Wpshop\Quizle\get_yiq;

class Settings extends AbstractSettings {

    const TEXT_DOMAIN = QUIZLE_TEXTDOMAIN;

    /**
     * @var bool
     */
    protected $do_flush_rewrite_rules = false;

    /**
     * @var array
     */
    protected $defaults = [
        'is_quizle_public' => 1,
        'clear_database'   => 1,
        'privacy_policy'   => '',

        'enable_phone_mask'            => 0,
        'user_messengers_brand_colors' => 0,
        'verify_nonce'                 => 1,
        'enable_wp_editor'             => 0,
        'prevent_autofocus'            => 0,

        'progress_animation_duration' => 1000,  // ms

        'quizle-color-primary'      => '#5c3bfe',
        'quizle-color-text-primary' => '#ffffff',
        'quizle-color-background'   => '',
        'quizle-color-background-1' => '#ffffff',
        'quizle-color-background-2' => '#ffffff',
        'quizle-color-text'         => '#111111',

        'grecaptcha.enabled'    => 0,
        'grecaptcha.site_key'   => '',
        'grecaptcha.secret_key' => '',

        'integrations.emails'                      => '',
        'integrations.webhook.urls'                => '',
        'integrations.telegram.enabled'            => 0,
        'integrations.telegram.token'              => '',
        'integrations.telegram.chat_id'            => '',
        'integrations.amocrm.enabled'              => 0,
        'integrations.amocrm.long_term_token'      => '',
        'integrations.amocrm.base_domain'          => '',
        'integrations.amocrm.price'                => '0',
        'integrations.bitrix.enabled'              => 0,
        'integrations.bitrix.endpoint'             => '',
        'integrations.metrika.enabled'             => 0,
        'integrations.metrika.disable_code_output' => 0,
        'integrations.metrika.counter'             => '',
        'integrations.skip_empty_contacts'         => '0',

        'file_upload.limit'           => 3,
        'file_upload.accept'          => 'image/*,text/*,.doc,.pdf,.zip,.rar,.7z',
        'file_upload.reset_on_reload' => 0,
        'file_upload.allow_guest'     => 1,
    ];

    /**
     * @var callable[]
     */
    protected $sanitizers = [
        'is_quizle_public' => 'intval',
        'clear_database'   => 'intval',
        'privacy_policy'   => 'wp_kses_post',

        'enable_phone_mask'            => 'absint',
        'user_messengers_brand_colors' => 'absint',
        'verify_nonce'                 => 'absint',
        'enable_wp_editor'             => 'absint',
        'prevent_autofocus'            => 'absint',

        'quizle-color-primary'    => 'sanitize_hex_color',
        'quizle-color-background' => 'sanitize_hex_color',
        'quizle-color-text'       => 'sanitize_hex_color',

        'grecaptcha.enabled'    => 'absint',
        'grecaptcha.site_key'   => 'sanitize_text_field',
        'grecaptcha.secret_key' => 'sanitize_text_field',

        'integrations.emails'                      => 'sanitize_text_field',
        'integrations.webhook.urls'                => 'sanitize_text_field',
        'integrations.telegram.enabled'            => 'absint',
        'integrations.telegram.token'              => 'sanitize_text_field',
        'integrations.telegram.chat_id'            => 'sanitize_text_field',
        'integrations.amocrm.enabled'              => 'absint',
        'integrations.amocrm.long_term_token'      => 'sanitize_text_field',
        'integrations.amocrm.base_domain'          => 'sanitize_text_field',
        'integrations.amocrm.price'                => 'absint',
        'integrations.bitrix.enabled'              => 'absint',
        'integrations.bitrix.endpoint'             => 'sanitize_text_field',
        'integrations.metrika.enabled'             => 'absint',
        'integrations.metrika.disable_code_output' => 'absint',
        'integrations.metrika.counter'             => 'sanitize_text_field',
        'integrations.skip_empty_contacts'         => 'absint',

        'file_upload.limit'           => 'absint',
        'file_upload.reset_on_reload' => 'absint',
        'file_upload.allow_guest'     => 'absint',
    ];

    /**
     * @var string[]
     */
    protected $password_fields = [
        'integrations.amocrm.long_term_token',
        'integrations.bitrix.endpoint',
        'grecaptcha.secret_key',
    ];

    /**
     * @return void
     */
    public function setup_tabs() {
        $this->add_tab( 'settings', __( 'Settings', QUIZLE_TEXTDOMAIN ) );
        $this->add_tab( 'integrations', __( 'Integrations', QUIZLE_TEXTDOMAIN ) );
        $this->add_tab( 'appearance', __( 'Appearance', QUIZLE_TEXTDOMAIN ) );
    }

    /**
     * @return void
     */
    public function init() {
        $this->defaults['privacy_policy'] = __( 'I have read and agree to the <a href="#" target="_blank">privacy policy</a>.', QUIZLE_TEXTDOMAIN );
        parent::init();

        add_filter( 'pre_update_option', function ( $value, $option ) {
            if ( $option !== $this->option ) {
                return $value;
            }

            $color = $value['quizle-color-background'] ?? '';
            $color = $color ?: '#ffffff'; // set white by default
            $yiq   = get_yiq( $color );
            if ( $yiq > apply_filters( 'quizle/element_colors/brightness_adjustment_threshold', 128 ) ) {
                // make lighter
                [ $color_1, $color_2 ] = get_adjusted_colors( $color, - 0.05, - 0.2 );
            } else {
                // make darker
                [ $color_1, $color_2 ] = get_adjusted_colors( $color, 0.05, 0.2 );
            }
            $value['quizle-color-background-1'] = $color_1;
            $value['quizle-color-background-2'] = $color_2;

            return $value;
        }, 10, 2 );

        $this->init_flush_rewrite_rules();
    }

    /**
     * Call <pre>flush_rewrite_rules()</pre> on enable/disable is_quizle_public
     *
     * @return void
     * @see \flush_rewrite_rules()
     */
    protected function init_flush_rewrite_rules() {
        add_action( 'update_option', function ( $option, $old_value, $value ) {
            if ( $option !== $this->option ) {
                return;
            }

            $keys_for_flush = [
                'is_quizle_public',
            ];
            foreach ( $keys_for_flush as $key ) {
                if ( array_key_exists( $key, $value ) ) {
                    if ( ! array_key_exists( $key, $old_value ) || $value[ $key ] !== $old_value[ $key ] ) {
                        $this->do_flush_rewrite_rules = true;
                        break;
                    }
                }
            }
        }, 10, 3 );

        add_action( 'updated_option', function ( $option ) {
            if ( $option === $this->option ) {
                $this->_options = null;
            }

            if ( $option === $this->option && $this->do_flush_rewrite_rules ) {
                flush_rewrite_rules();
                $this->do_flush_rewrite_rules = false;
            }
        }, 9 );
    }

    /**
     * @inheridoc
     */
    public function get_tab_icons() {
        return array_merge( parent::get_tab_icons(), [
            'settings'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256.01 209.36c12.96 0 25.24 4.27 33.69 11.7 9.07 7.98 13.87 19.73 14.29 34.93-.42 15.21-5.23 26.96-14.3 34.94-8.46 7.44-20.74 11.7-33.69 11.7s-25.24-4.27-33.7-11.7c-9.07-7.98-13.87-19.73-14.29-34.93.42-15.21 5.23-26.96 14.3-34.93 8.46-7.44 20.74-11.7 33.7-11.7M332.28 0H179.72v78.23a189.922 189.922 0 0 0-36.88 21.69l-66.6-39.13L0 195.17l66.58 39.12a198.125 198.125 0 0 0 0 43.41L0 316.83l76.24 134.39 66.6-39.13a189.419 189.419 0 0 0 36.88 21.69v78.23h152.56v-78.23c13.05-5.8 25.41-13.08 36.88-21.69l66.6 39.13L512 316.83l-66.58-39.12c.79-7.23 1.19-14.5 1.19-21.71s-.4-14.48-1.19-21.71L512 195.17 435.76 60.79l-66.6 39.13a189.419 189.419 0 0 0-36.88-21.69V0ZM146.65 155.93c16.34-13.16 35.37-29.23 55.09-36.62l23.83-9.82V46.55h60.88v62.95l23.83 9.82c19.67 7.36 38.8 23.5 55.09 36.62l53.61-31.5 30.48 53.72-53.59 31.49c1.62 12.88 5.23 33.6 4.93 46.36.31 12.67-3.32 33.6-4.93 46.36l53.59 31.49-30.48 53.72-53.61-31.5c-16.34 13.16-35.37 29.23-55.09 36.62l-23.83 9.82v62.95h-60.88v-62.95l-23.83-9.82c-19.67-7.36-38.8-23.5-55.09-36.62l-53.61 31.5-30.48-53.72 53.59-31.49c-1.62-12.88-5.23-33.6-4.93-46.36-.31-12.67 3.32-33.6 4.93-46.36l-53.59-31.49 30.48-53.72 53.61 31.5ZM256 161.36c-47.46 0-94.92 31.55-96 94.63 1.07 63.1 48.53 94.64 96 94.64s94.92-31.55 96-94.64c-1.07-63.09-48.53-94.64-96-94.64Z" fill="currentColor"></path></svg>',
            'integrations' => '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 32 32"><path d="M15.7 29.7c-.8 0-1.5-.7-1.5-1.5v-3.9c-3.8-.7-6.6-4-6.6-8v-2.5H6.5c-.8 0-1.5-.7-1.5-1.5s.7-1.5 1.5-1.5H25c.8 0 1.5.7 1.5 1.5s-.7 1.5-1.5 1.5h-1.1v2.5c0 4-2.9 7.3-6.6 8v3.9c-.1.8-.7 1.5-1.6 1.5zm0-8.3c2.8 0 5.1-2.3 5.1-5.1v-2.5H10.6v2.5c0 2.8 2.3 5.1 5.1 5.1zM21 9.9c-.8 0-1.5-.7-1.5-1.5v-4c0-.8.7-1.5 1.5-1.5s1.5.7 1.5 1.5v4c0 .8-.6 1.5-1.5 1.5zm-10.6 0c-.8 0-1.5-.7-1.5-1.5v-4c0-.8.7-1.5 1.5-1.5s1.5.7 1.5 1.5v4c0 .8-.6 1.5-1.5 1.5z"/></svg>',
            'appearance'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M176 263c48.52 0 88-39.48 88-88s-39.48-88-88-88-88 39.48-88 88 39.48 88 88 88Zm0-128c22.06 0 40 17.94 40 40s-17.94 40-40 40-40-17.94-40-40 17.94-40 40-40ZM432 0H80C35.89 0 0 35.89 0 80v352c0 44.11 35.89 80 80 80h352c44.11 0 80-35.89 80-80V80c0-44.11-35.89-80-80-80ZM80 48h352c17.67 0 32 14.33 32 32v209.01l-76.82-78.77a24 24 0 0 0-17.1-7.24H370c-6.41 0-12.56 2.57-17.07 7.13L196.44 368.44l-66.36-67.29a24.02 24.02 0 0 0-17.07-7.15h-.02a24.05 24.05 0 0 0-17.07 7.12l-47.93 48.47V80c0-17.67 14.33-32 32-32ZM48 432v-14.14l64.98-65.71 49.72 50.42-60.71 61.42H80c-17.67 0-32-14.33-32-32Zm384 32H169.48l200.4-202.74L464 357.77v74.24c0 17.67-14.33 32-32 32Z" fill="currentColor"></path></svg>',
        ] );
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function doc_link( $type ) {
        switch ( $type ) {
            case 'doc':
                return 'https://support.wpshop.ru/docs/plugins/quizle';
            case 'faq':
                return 'https://support.wpshop.ru/fag_tag/quizle/';
            default:
                return null;
        }
    }

    /**
     * @inheridoc
     */
    protected static function get_template_parts_root() {
        return dirname( QUIZLE_FILE ) . '/template-parts/';
    }

    /**
     * @inheritвoc
     */
    public static function product_prefix() {
        return 'quizle_';
    }
}
