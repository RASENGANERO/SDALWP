<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Wpshop\Quizle\Admin;

use WP_Post;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use Wpshop\Quizle\Social;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\esc_json;
use function Wpshop\Quizle\get_adjusted_colors;
use function Wpshop\Quizle\get_yiq;
use function Wpshop\Quizle\json_decode;
use function Wpshop\Quizle\quizle_editor_wrap;

class MetaBoxes {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Social
     */
    protected $social;

    /**
     * @var WP_Post|null
     */
    protected $post;

    /**
     * @var array[]
     */
    protected $fields;

    /**
     * @var \string[][]
     */
    protected $quizle_types;

    /**
     * @var array[]
     */
    protected $question_types;

    /**
     * @var array
     */
    protected $quizle_view_type;

    /**
     * @var array
     */
    protected $progress_view_type;

    /**
     * @var array
     */
    protected $contact_form_order;

    /**
     * @param Settings     $settings
     * @param Social       $social
     * @param WP_Post|null $post
     */
    public function __construct( Settings $settings, Social $social, WP_Post $post = null ) {
        $this->settings = $settings;
        $this->social   = $social;

        $this->post = $post;

        $this->quizle_types = container()->get( Quizle::class )->get_types();

        $this->question_types = [
            [
                'label' => __( 'Text', QUIZLE_TEXTDOMAIN ),
                'value' => 'text',
            ],
            [
                'label' => __( 'Textarea', QUIZLE_TEXTDOMAIN ),
                'value' => 'textarea',
            ],
            [
                'label' => __( 'Check', QUIZLE_TEXTDOMAIN ),
                'value' => 'check',
            ],
            [
                'label' => __( 'Image Horizontal', QUIZLE_TEXTDOMAIN ),
                'value' => 'image_horizontal',
            ],
            [
                'label' => __( 'Image Vertical', QUIZLE_TEXTDOMAIN ),
                'value' => 'image_vertical',
            ],
            [
                'label' => __( 'File Upload', QUIZLE_TEXTDOMAIN ),
                'value' => 'file',
            ],
        ];

        $this->quizle_view_type = [
            'slides' => __( 'Slides (only one question is shown)', QUIZLE_TEXTDOMAIN ),
            'list'   => __( 'List (all questions one after another)', QUIZLE_TEXTDOMAIN ),
        ];

        $this->progress_view_type = [
            'hide'    => __( 'Do not show', QUIZLE_TEXTDOMAIN ),
            'line'    => __( 'Show line progress', QUIZLE_TEXTDOMAIN ),
            'numbers' => __( 'Show number finished / all questions', QUIZLE_TEXTDOMAIN ),
        ];

        $this->contact_form_order = [
            'before_results' => __( 'Show before the results', QUIZLE_TEXTDOMAIN ),
            'after_results'  => __( 'Show after results', QUIZLE_TEXTDOMAIN ),
        ];

        $this->fields = [
            'quizle-type',

            'welcome-enabled'     => 'absint',
            'welcome-title'       => 'sanitize_text_field',
            'welcome-description',
            //'welcome-description' => 'wp_kses_post',
            'welcome-button-text' => 'sanitize_text_field',
            'welcome-img'         => 'sanitize_url',
            'welcome-img-position',
            //'welcome-img-position' => 'wp_kses_post',

            'quizle-has-conditions' => 'absint',
            'quizle-questions'      => function ( $data ) {
                $sanitize_map = [
                    'title' => 'sanitize_text_field',
                    'image' => 'sanitize_url'
                    //'description' => 'Wpshop\Quizle\sanitize_textarea',
                ];

                $result = [];
                $data   = (array) json_decode( wp_unslash( $data ) );
                foreach ( $data as $key => $value ) {
                    if ( array_key_exists( $key, $sanitize_map ) ) {
                        $value = $sanitize_map[ $key ]( $value );
                    }
                    $result[ $key ] = $value;
                }

                return wp_slash( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) );
            },

            'result-enabled' => 'absint',
            'quizle-results' => function ( $data ) {
                $sanitize_map = [
                    'title'    => 'sanitize_text_field',
                    //'description' => 'Wpshop\Quizle\sanitize_textarea',
                    'image'    => 'sanitize_url',
                    'btn_text' => 'sanitize_text_field',
                    'link'     => 'sanitize_url',
                ];

                $result = [];

                $data = (array) json_decode( wp_unslash( $data ) );
                foreach ( $data as $key => $value ) {
                    if ( array_key_exists( $key, $sanitize_map ) ) {
                        $value = $sanitize_map[ $key ]( $value );
                    }
                    $result[ $key ] = $value;
                }

                return wp_slash( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) );
            },

            'contact-enabled'          => 'absint',
            'contact-with-name'        => 'absint',
            'contact-with-email'       => 'absint',
            'contact-with-phone'       => 'absint',
            'contact-with-messengers'  => 'absint',
            'contact-title'            => 'sanitize_text_field',
            'contact-description',
            //'contact-description'  => 'Wpshop\Quizle\sanitize_textarea',
            'contact-btn-text'         => 'sanitize_text_field',
            'contact-privacy-text'     => 'wp_kses_post',
            'contact-message',
            'contact-redirect-link',
            'contact-redirect-timeout' => 'absint',
            //'contact-message'      => 'Wpshop\Quizle\sanitize_textarea',
            'contact-form-order',

            'save-quizle-contacts-and-results' => 'absint',
            'show-social-share'                => [
                'depends'  => 'save-quizle-contacts-and-results',
                'sanitize' => function ( $value ) {
                    return get_post_meta( $this->post->ID, 'save-quizle-contacts-and-results', true ) ? absint( $value ) : 0;
                },
            ],
            'social-share-providers'           => function ( $items ) {
                $providers = array_keys( $this->social->get_share_providers() );

                return array_intersect( $providers, $items );
            },
            'messenger-providers'              => function ( $items ) {
                $providers = array_keys( $this->social->get_messengers() );

                return array_intersect( $providers, $items );
            },
            'random-questions'                 => 'absint',
            'random-answers'                   => 'absint',
            'test-instant-answer'              => 'absint',
            'can-change-answer'                => 'absint',
            'view-type'                        => function ( $val ) {
                if ( ! array_key_exists( $val, $this->quizle_view_type ) ) {
                    $val = array_key_first( $this->quizle_view_type );
                }

                return $val;
            },
            'quizle-progress'                  => function ( $val ) {
                if ( ! array_key_exists( $val, $this->progress_view_type ) ) {
                    $val = array_key_first( $this->progress_view_type );
                }

                return $val;
            },
            'last-step-btn-text'               => 'sanitize_text_field',
            'emails-for-contacts'              => function ( $val ) {
                $val = array_filter( wp_parse_list( $val ), 'is_email' );

                return implode( ', ', $val );
            },
            'all-slides-text',

            'quizle-color-primary'      => 'sanitize_hex_color',
            'quizle-color-text-primary' => 'sanitize_hex_color',
            'quizle-color-background'   => [
                'sanitize' => 'sanitize_hex_color',
                'save'     => function ( $key, $color ) {
                    update_post_meta( $this->post->ID, $key, $color );
//                    if ( ! $color ) {
//                        delete_post_meta( $this->post->ID, 'quizle-color-background-1' );
//                        delete_post_meta( $this->post->ID, 'quizle-color-background-2' );
//
//                        return;
//                    }

                    $color = $color ?: '#ffffff';

                    $yiq = get_yiq( $color );
                    if ( $yiq > apply_filters( 'quizle/element_colors/brightness_adjustment_threshold', 128 ) ) {
                        // make lighter
                        [ $color_1, $color_2 ] = get_adjusted_colors( $color, - 0.05, - 0.2 );
                    } else {
                        // make darker
                        [ $color_1, $color_2 ] = get_adjusted_colors( $color, 0.05, 0.2 );
                    }
                    update_post_meta( $this->post->ID, 'quizle-color-background-1', $color_1 );
                    update_post_meta( $this->post->ID, 'quizle-color-background-2', $color_2 );
                },
                'export'   => [
                    'quizle-color-background',
                    'quizle-color-background-1',
                    'quizle-color-background-2',
                ],
            ],
            'quizle-color-text'         => 'sanitize_hex_color',
            'quizle-height'             => 'sanitize_text_field',

            'finish-enabled' => 'absint',
            'finish-title'   => 'sanitize_text_field',
            'finish-img'     => 'sanitize_url',
            'finish-img-position',

            'quizle-completion-time' => 'absint',
        ];
    }

    /**
     * @return array
     */
    protected function get_wp_editor_settings() {
        /**
         * @since 1.4
         */
        $settings = apply_filters( 'quizle/edit_metabox/wp_editor_settings', [
            'wpautop'          => 1,
            'textarea_rows'    => 5,
            'teeny'            => 0,
            'drag_drop_upload' => 1,
            //'wp_skip_init'     => true,
        ] );

        return $settings;
    }

    /**
     * @return array|array[]
     */
    public function get_fields() {
        return $this->fields;
    }

    /**
     * @param array $types
     *
     * @return $this
     */
    public function set_quizle_types( array $types ) {
        $this->question_types = $types;

        return $this;
    }

    /**
     * @return void
     */
    public function update_metadata() {
        // set default values for switch groups without selected items
        $post_data = wp_parse_args( $_POST, [
            'messenger-providers'    => [],
            'social-share-providers' => [],
        ] );

        foreach ( $this->fields as $key => $config ) {
            if ( is_numeric( $key ) ) {
                $key    = $config;
                $config = [];
            } else {
                if ( ! is_array( $config ) ) {
                    $config = [ 'sanitize' => $config ];
                }
            }

            $value = $post_data[ $key ] ?? null;
            if ( null === $value ) {
                continue;
            }

            if ( is_callable( $config['sanitize'] ?? null ) ) {
                $value = call_user_func( $config['sanitize'], $value );
            }

            if ( is_callable( $config['save'] ?? null ) ) {
                call_user_func( $config['save'], $key, $value );
            } else {
                update_post_meta( $this->post->ID, $key, $value );
            }
        }
        // todo update logic for handle 'depends'
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    protected function get_value( $key, $default = null ) {
        if ( $this->post ) {
            if ( ! metadata_exists( 'post', $this->post->ID, $key ) ) {
                return null !== $default ? $default : $this->settings->get_value( $key );
            }

            return get_post_meta( $this->post->ID, $key, true );
        }

        return null;
    }

    /**
     * @param WP_Post $post
     *
     * @return $this
     */
    public function set_post( $post ) {
        $this->post = $post;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function doc_link( $type ) {
        return 'https://support.wpshop.ru/docs/plugins/quizle/quizle-settings/#' . $type;
    }

    /**
     * @return void
     */
    public function output() {
        ?>

        <div class="quizle-shortcode">
            <?php esc_html_e( 'A shortcode to insert on the page:', QUIZLE_TEXTDOMAIN ) ?>
            <input type='text' value='[quizle name="<?php echo $this->post->post_name ?>"]'>
        </div>

        <div class="quizle-messages js-quizle-messages">
        </div>

        <div class="quizle-types-header"><strong><?php echo __( 'Select quiz type:', QUIZLE_TEXTDOMAIN ) ?></strong>
            &nbsp;
            <a href="<?php echo $this->doc_link( 'quizle-types' ) ?>"><?php echo __( 'What type of quiz to choose?', QUIZLE_TEXTDOMAIN ) ?></a>
        </div>
        <div class="quizle-types js-quizle-types">
            <input type="hidden" name="quizle-type" value="<?php echo esc_attr( $this->get_value( 'quizle-type', 'contacts' ) ) ?>">
            <?php foreach ( $this->quizle_types as $type => $data ): ?>
                <div class="quizle-type js-quizle-type-item<?php echo $this->get_value( 'quizle-type', 'contacts' ) == $type ? ' checked' : '' ?>" data-type="<?php echo $type ?>">
                    <div class="quizle-type__title">
                        <div>
                            <?php echo $data['icon'] ?>
                            <?php echo $data['title'] ?>
                        </div>
                    </div>
                    <div class="quizle-type__description"><?php echo $data['description'] ?></div>
                </div>
            <?php endforeach ?>
        </div>

        <div class="quizle-box">
            <div class="quizle-box__header">
                <div class="quizle-box-header__title js-quizle-box-header-action-expand"><?php echo __( 'Start screen', QUIZLE_TEXTDOMAIN ); ?></div>
                <div class="quizle-box-header__actions">
                    <div class="quizle-box-header-action quizle-box-header-action--enable">
                        <input type="hidden" name="welcome-enabled" value="0">
                        <input type="checkbox" name="welcome-enabled" value="1" class="quizle-switch-box"<?php checked( $this->get_value( 'welcome-enabled' ), '1' ) ?>>
                    </div>
                    <div class="quizle-box-header-action quizle-box-header-action--expand js-quizle-box-header-action-expand"></div>
                    <div class="quizle-box-header-action quizle-box-header-action--doc">
                        <a href="<?php echo $this->doc_link( 'start-screen' ) ?>" target="_blank" rel="noopener">?</a>
                    </div>
                </div>
            </div>
            <div class="quizle-box__body" data-show_state="1" data-identity="start-screen">
                <div class="quizle-box__help">
                    Стартовый экран — блок с приветствием и описанием квиза.
                    <br><br>
                    Его задача мотивировать посетителя пройти ваш квиз. Поэтому можно предложить какой-то бонус в конце.
                </div>
                <div class="quizle-box__form">
                    <div class="quizle-form-row">
                        <?php $this->render_text_input( __( 'Title', QUIZLE_TEXTDOMAIN ), 'welcome-title', '', __( 'Take a short quiz and find the right answer for you', QUIZLE_TEXTDOMAIN ) ); ?>
                    </div>
                    <div class="quizle-form-row">
                        <?php quizle_editor_wrap(
                            $this->get_value( 'welcome-description' ),
                            'welcome-description',
                            $this->get_wp_editor_settings()
                        )( function () {
                            $this->render_textarea(
                                __( 'Description', QUIZLE_TEXTDOMAIN ),
                                'welcome-description',
                                sprintf( __( 'After you pass we give you a 20%% discount on all our products and a personal consultation with our manager', QUIZLE_TEXTDOMAIN ) )
                            );
                        } ) ?>


                    </div>
                    <div class="quizle-form-row">
                        <div class="quizle-form-cols">
                            <div class="quizle-form-col">
                                <?php $this->render_media_upload( __( 'Image', QUIZLE_TEXTDOMAIN ), 'welcome-img' ); ?>
                            </div>
                            <div class="quizle-form-col">
                                <?php $this->render_select( __( 'Image Position' ), 'welcome-img-position', [
                                    'background' => _x( 'Background', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'left'       => _x( 'Left', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'top'        => _x( 'Top', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'right'      => _x( 'Right', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'bottom'     => _x( 'Bottom', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                ], true, 'left' ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="quizle-form-row">
                        <?php $this->render_text_input( __( 'Button Text', QUIZLE_TEXTDOMAIN ), 'welcome-button-text', __( 'Start', QUIZLE_TEXTDOMAIN ) ); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="quizle-box">
            <div class="quizle-box__header">
                <div class="quizle-box-header__title js-quizle-box-header-action-expand"><?php echo __( 'Questions', QUIZLE_TEXTDOMAIN ) ?></div>
                <div class="quizle-box-header__actions">
                    <div class="quizle-box-header-action quizle-box-header-action--expand-all js-quizle-box-header-action-expand-all-questions">
                        <?php echo __( 'collapse all', QUIZLE_TEXTDOMAIN ) ?>
                    </div>
                    <div class="quizle-box-header-action quizle-box-header-action--expand js-quizle-box-header-action-expand"></div>
                    <div class="quizle-box-header-action quizle-box-header-action--doc">
                        <a href="<?php echo $this->doc_link( 'questions' ) ?>" target="_blank" rel="noopener">?</a>
                    </div>
                </div>
            </div>
            <div class="quizle-box__body" data-show_state="1" data-identity="questions-screen">
                <div class="quizle-box__help">
                    Если ваша задача получать лиды — старайтесь задавать простые вопросы, чтобы посетитель быстрее дошел
                    до конца и оставил заявку.
                </div>
                <div class="quizle-box__form">
                    <div class="quizle-questions js-questions-container"></div>

                    <input type="hidden" name="quizle-questions" value="<?php echo esc_json( $this->get_value( 'quizle-questions' ) ); ?>">
                    <input type="hidden" name="quizle-has-conditions" value="<?php echo esc_attr( $this->get_value( 'quizle-has-conditions', 0 ) ); ?>">
                    <span class="button js-add-quizle-question"><?php echo __( '+ Add Question', QUIZLE_TEXTDOMAIN ) ?></span>
                </div>
            </div>
        </div>

        <div class="quizle-box">
            <div class="quizle-box__header">
                <div class="quizle-box-header__title js-quizle-box-header-action-expand"><?php echo __( 'Results', QUIZLE_TEXTDOMAIN ) ?></div>
                <div class="quizle-box-header__actions">
                    <div class="quizle-box-header-action quizle-box-header-action--expand-all js-quizle-box-header-action-expand-all-results">
                        <?php echo __( 'collapse all', QUIZLE_TEXTDOMAIN ) ?>
                    </div>
                    <div class="quizle-box-header-action quizle-box-header-action--enable">
                        <input type="hidden" name="result-enabled" value="0">
                        <input type="checkbox" class="quizle-switch-box" name="result-enabled" value="1" <?php checked( $this->get_value( 'result-enabled' ), '1' ) ?>>
                    </div>
                    <div class="quizle-box-header-action quizle-box-header-action--expand js-quizle-box-header-action-expand"></div>
                    <div class="quizle-box-header-action quizle-box-header-action--doc">
                        <a href="<?php echo $this->doc_link( 'results' ) ?>" target="_blank" rel="noopener">?</a>
                    </div>
                </div>
            </div>
            <div class="quizle-box__body" data-show_state="1" data-identity="results-screen">
                <div class="quizle-box__form">
                    <div class="quizle-results js-results-container"></div>
                    <input type="hidden" name="quizle-results" value="<?php echo esc_json( $this->get_value( 'quizle-results' ) ) ?>">
                    <span class="button js-add-quizle-result"><?php echo __( '+ Add Result', QUIZLE_TEXTDOMAIN ) ?></span>
                </div>
            </div>
        </div>

        <div class="quizle-box">
            <div class="quizle-box__header">
                <div class="quizle-box-header__title js-quizle-box-header-action-expand"><?php echo __( 'Gathering contacts', QUIZLE_TEXTDOMAIN ) ?></div>
                <div class="quizle-box-header__actions">
                    <div class="quizle-box-header-action quizle-box-header-action--enable<?php echo $this->get_value( 'quizle-type' ) === Quizle::TYPE_CONTACTS ? ' disabled' : '' ?>">
                        <?php if ( $this->get_value( 'quizle-type' ) !== Quizle::TYPE_CONTACTS ): ?>
                            <input type="hidden" name="contact-enabled" value="0">
                        <?php endif ?>
                        <input type="checkbox" class="quizle-switch-box" name="contact-enabled" value="1"
                            <?php disabled( $this->get_value( 'quizle-type' ) === Quizle::TYPE_CONTACTS ) ?>
                            <?php checked( $this->get_value( 'contact-enabled' ) || $this->get_value( 'quizle-type' ) === Quizle::TYPE_CONTACTS, '1' ) ?>>
                        <?php if ( $this->get_value( 'quizle-type' ) === Quizle::TYPE_CONTACTS ): ?>
                            <input type="hidden" name="contact-enabled" value="1">
                        <?php endif ?>
                    </div>
                    <div class="quizle-box-header-action quizle-box-header-action--expand js-quizle-box-header-action-expand"></div>
                    <div class="quizle-box-header-action quizle-box-header-action--doc">
                        <a href="<?php echo $this->doc_link( 'gathering-contacts' ) ?>" target="_blank" rel="noopener">?</a>
                    </div>
                </div>
            </div>
            <div class="quizle-box__body" data-show_state="1" data-identity="contacts-screen">
                <div class="quizle-box__form">
                    <div class="quizle-form-row">

                        <div class="quizle-form-cols">
                            <div class="quizle-form-col">
                                <label class="quizle-form-label">
                                    <input type="hidden" name="contact-with-name" value="0">
                                    <input name="contact-with-name" type="checkbox" class="quizle-switch-box" value="1"<?php checked( (int) $this->get_value( 'contact-with-name', 1 ), '1' ) ?>>
                                    <?php echo __( 'Name', QUIZLE_TEXTDOMAIN ) ?>
                                </label>
                            </div>
                            <div class="quizle-form-col">
                                <label class="quizle-form-label">
                                    <input type="hidden" name="contact-with-email" value="0">
                                    <input name="contact-with-email" type="checkbox" class="quizle-switch-box" value="1"<?php checked( $this->get_value( 'contact-with-email', 1 ), '1' ) ?>>
                                    <?php echo __( 'E-mail', QUIZLE_TEXTDOMAIN ) ?>
                                </label>
                            </div>
                            <div class="quizle-form-col">
                                <label class="quizle-form-label">
                                    <input type="hidden" name="contact-with-phone" value="0">
                                    <input name="contact-with-phone" type="checkbox" class="quizle-switch-box" value="1"<?php checked( $this->get_value( 'contact-with-phone' ), '1' ) ?>>
                                    <?php echo __( 'Phone', QUIZLE_TEXTDOMAIN ) ?>
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="quizle-form-row">
                        <?php echo __( 'Messengers', QUIZLE_TEXTDOMAIN ) ?>
                        <div class="quizle-switch-group">
                            <?php $current_providers = array_keys( $this->social->get_quizle_messengers( $this->post->ID ) ) ?>
                            <?php foreach ( $this->social->get_messengers() as $provider => $item ): ?>
                                <input name="messenger-providers[]"
                                       value="<?php echo $provider ?>"
                                    <?php checked( in_array( $provider, $current_providers ) ) ?>
                                       type="checkbox" id="quizle-messenger-<?php echo $provider ?>" autocomplete="off">
                                <label class="quizle-switch" for="quizle-messenger-<?php echo $provider ?>" title="<?php echo esc_attr( $item['title'] ?? '' ) ?>">
                                    <span class="quizle-social-icon" style="--quizle-social-icon-width: 16px; --quizle-social-icon-height: 16px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 <?php echo "{$item['width']} {$item['height']}" ?>">
                                            <path d="<?php echo $item['path'] ?>" fill="currentColor"/>
                                        </svg>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div><!--.quizle-switch-group-->
                    </div>

                    <div class="quizle-form-row">
                        <label for="" class="quizle-form-label"><?php echo __( 'Title', QUIZLE_TEXTDOMAIN ) ?></label>
                        <input name="contact-title" type="text" class="quizle-text" value="<?php echo esc_attr( $this->get_value( 'contact-title' ) ) ?>" placeholder="<?php echo __( 'Fill out the form to get your results', QUIZLE_TEXTDOMAIN ) ?>">
                    </div>

                    <div class="quizle-form-row">
                        <label for="" class="quizle-form-label"><?php echo __( 'Description', QUIZLE_TEXTDOMAIN ) ?></label>
                        <?php if ( $this->settings->get_value( 'enable_wp_editor' ) ): ?>
                            <?php wp_editor( $this->get_value( 'contact-description' ), 'contact-description', $this->get_wp_editor_settings() ); ?>
                        <?php else: ?>
                            <textarea name="contact-description" rows="3" class="quizle-text"><?php echo esc_textarea( (string) $this->get_value( 'contact-description' ) ) ?></textarea>
                        <?php endif ?>
                    </div>

                    <div class="quizle-form-cols">
                        <div class="quizle-form-col">
                            <div class="quizle-form-row">
                                <label class="quizle-form-label"><?php echo __( 'Button Text', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input name="contact-btn-text" type="text" class="quizle-text" value="<?php echo esc_attr( $this->get_value( 'contact-btn-text', __( 'Submit', QUIZLE_TEXTDOMAIN ) ) ) ?>">
                            </div>
                        </div>
                        <div class="quizle-form-col">
                            <div class="quizle-form-row">
                                <label class="quizle-form-label"><?php echo __( 'Privacy policy text', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input name="contact-privacy-text" type="text" class="quizle-text" value="<?php echo esc_attr( $this->get_value( 'contact-privacy-text', $this->settings->get_value( 'privacy_policy' ) ) ) ?>" placeholder="<?php echo esc_attr( $this->settings->get_value( 'privacy_policy' ) ) ?>">
                            </div>
                        </div>
                    </div><!--.quizle-form-cols-->

                    <div class="quizle-form-row">
                        <label class="quizle-form-label"><?php echo __( 'Message after submitting the form', QUIZLE_TEXTDOMAIN ) ?></label>
                        <p><i>(<?php echo __( 'Moved to Finish Screen Block', 'quizle' ) ?>)</i></p>
                    </div>

                    <div class="quizle-form-cols">
                        <div class="quizle-form-col">
                            <div class="quizle-form-row">
                                <label class="quizle-form-label"><?php echo __( 'Redirect Link', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input name="contact-redirect-link" type="text" class="quizle-text" value="<?php echo esc_attr( $this->get_value( 'contact-redirect-link' ) ) ?>">
                                <p class="description">(укажите ссылку, если нужно сделать переадресацию после показа
                                    "Сообщения после отправки формы")</p>
                            </div>
                        </div>
                        <div class="quizle-form-col">
                            <div class="quizle-form-row">
                                <label class="quizle-form-label"><?php echo __( 'Redirect Timeout', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input name="contact-redirect-timeout" type="number" min="0" step="1" class="quizle-text" value="<?php echo esc_attr( $this->get_value( 'contact-redirect-timeout', 3000 ) ) ?>" title="<?php echo __( 'value in milliseconds', QUIZLE_TEXTDOMAIN ) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="quizle-form-row">
                        <?php $this->render_select( __( 'Show contact form', QUIZLE_TEXTDOMAIN ), 'contact-form-order', $this->contact_form_order ); ?>
                        <p class="description">(если в результатах задана переадресация по ссылке, то показ после
                            результатов не сработает, т.к. будет сделана переадресация)</p>
                    </div>

                </div>
            </div>
        </div>

        <div class="quizle-box">
            <div class="quizle-box__header">
                <div class="quizle-box-header__title js-quizle-box-header-action-expand"><?php echo __( 'Finish screen', QUIZLE_TEXTDOMAIN ); ?></div>
                <div class="quizle-box-header__actions">
                    <?php /*
                    <div class="quizle-box-header-action quizle-box-header-action--enable">
                        <input type="hidden" name="finish-enabled" value="0">
                        <input type="checkbox" name="finish-enabled" value="1" class="quizle-switch-box"<?php checked( $this->get_value( 'finish-enabled' ), '1' ) ?>>
                    </div>
                    */ ?>
                    <div class="quizle-box-header-action quizle-box-header-action--expand js-quizle-box-header-action-expand"></div>
                    <div class="quizle-box-header-action quizle-box-header-action--doc">
                        <a href="<?php echo $this->doc_link( 'finish-screen' ) ?>" target="_blank" rel="noopener">?</a>
                    </div>
                </div>
            </div>
            <div class="quizle-box__body" data-show_state="1" data-identity="finish-screen">
                <div class="quizle-box__form">
                    <div class="quizle-form-row">
                        <?php $this->render_text_input( __( 'Title', QUIZLE_TEXTDOMAIN ), 'finish-title', '' ); ?>
                    </div>
                    <div class="quizle-form-row">
                        <?php if ( $this->settings->get_value( 'enable_wp_editor' ) ): ?>
                            <label class="quizle-form-label"><?php echo __( 'Message after submitting the form', QUIZLE_TEXTDOMAIN ) ?></label>
                            <?php wp_editor( $this->get_value( 'contact-message' ), 'contact-message', $this->get_wp_editor_settings() ); ?>
                        <?php else: ?>
                            <?php $this->render_textarea( __( 'Message after submitting the form', QUIZLE_TEXTDOMAIN ), 'contact-message' ); ?>
                        <?php endif ?>
                    </div>
                    <div class="quizle-form-row">
                        <div class="quizle-form-cols">
                            <div class="quizle-form-col">
                                <?php $this->render_media_upload( __( 'Image', QUIZLE_TEXTDOMAIN ), 'finish-img' ); ?>
                            </div>
                            <div class="quizle-form-col">
                                <?php $this->render_select( __( 'Image Position' ), 'finish-img-position', [
                                    'background' => _x( 'Background', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'left'       => _x( 'Left', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'top'        => _x( 'Top', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'right'      => _x( 'Right', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                    'bottom'     => _x( 'Bottom', 'img_pos_options', QUIZLE_TEXTDOMAIN ),
                                ], true, 'left' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quizle-box">
            <div class="quizle-box__header">
                <div class="quizle-box-header__title js-quizle-box-header-action-expand"><?php echo __( 'Settings', QUIZLE_TEXTDOMAIN ) ?></div>
                <div class="quizle-box-header__actions">
                    <div class="quizle-box-header-action quizle-box-header-action--expand js-quizle-box-header-action-expand"></div>
                    <div class="quizle-box-header-action quizle-box-header-action--doc">
                        <a href="<?php echo $this->doc_link( 'settings' ) ?>" target="_blank" rel="noopener">?</a>
                    </div>
                </div>
            </div>
            <div class="quizle-box__body" data-show_state="1" data-identity="settings-screen">
                <div class="quizle-box__form">

                    <div class="quizle-form-row">
                        <label class="quizle-form-label">
                            <input type="hidden" name="save-quizle-contacts-and-results" value="0">
                            <input type="checkbox" class="quizle-switch-box" name="save-quizle-contacts-and-results" value="1"<?php checked( (int) $this->get_value( 'save-quizle-contacts-and-results', 1 ), '1' ) ?>>
                            <?php echo __( 'Save contacts and quiz results', QUIZLE_TEXTDOMAIN ) ?>
                        </label>
                    </div>

                    <div class="quizle-form-row">
                        <label class="quizle-form-label">
                            <input type="hidden" name="show-social-share" value="0">
                            <input type="checkbox" class="quizle-switch-box" name="show-social-share" data-depends="save-quizle-contacts-and-results" value="1"<?php checked( (int) $this->get_value( 'show-social-share', 1 ), '1' ) ?>>
                            <?php echo __( 'Show social share buttons (with enabled results only)', QUIZLE_TEXTDOMAIN ) ?>
                        </label>
                    </div>

                    <div class="quizle-form-row">
                        <div class="quizle-switch-group">
                            <?php $current_providers = array_keys( $this->social->get_quizle_providers( $this->post->ID, false ) ) ?>
                            <?php foreach ( $this->social->get_share_providers() as $provider => $item ): ?>
                                <input name="social-share-providers[]"
                                       value="<?php echo $provider ?>"
                                    <?php checked( in_array( $provider, $current_providers ) ) ?>
                                       type="checkbox" id="quizle-social-<?php echo $provider ?>" autocomplete="off">
                                <label class="quizle-switch" for="quizle-social-<?php echo $provider ?>" title="<?php echo esc_attr( $item['title'] ?? '' ) ?>">
                                    <span class="quizle-social-icon" style="--quizle-social-icon-width: 16px; --quizle-social-icon-height: 16px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 <?php echo "{$item['width']} {$item['height']}" ?>">
                                            <path d="<?php echo $item['path'] ?>" fill="currentColor"/>
                                        </svg>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div><!--.quizle-switch-group-->
                    </div>

                    <div class="quizle-form-row">
                        <label class="quizle-form-label">
                            <input type="hidden" name="random-questions" value="0">
                            <input type="checkbox" class="quizle-switch-box" name="random-questions" value="1"<?php checked( $this->get_value( 'random-questions' ), '1' ) ?>>
                            <?php echo __( 'Questions in random order', QUIZLE_TEXTDOMAIN ) ?>
                        </label>
                    </div>
                    <div class="quizle-form-row">
                        <label class="quizle-form-label">
                            <input type="hidden" name="random-answers" value="0">
                            <input type="checkbox" class="quizle-switch-box" name="random-answers" value="1"<?php checked( $this->get_value( 'random-answers' ), '1' ) ?>>
                            <?php echo __( 'Answers in random order', QUIZLE_TEXTDOMAIN ) ?>
                        </label>
                    </div>
                    <div class="quizle-form-row">
                        <label class="quizle-form-label">
                            <input type="hidden" name="test-instant-answer" value="0">
                            <input type="checkbox" class="quizle-switch-box" name="test-instant-answer" value="1"<?php checked( $this->get_value( 'test-instant-answer' ), '1' ) ?>>
                            <?php echo __( 'Show the correct or incorrect answer immediately after selecting an option (for Tests only)', QUIZLE_TEXTDOMAIN ) ?>
                        </label>
                    </div>
                    <div class="quizle-form-row">
                        <label class="quizle-form-label">
                            <input type="hidden" name="can-change-answer" value="0">
                            <input type="checkbox" class="quizle-switch-box" name="can-change-answer" value="1"<?php checked( $this->get_value( 'can-change-answer' ), '1' ) ?>>
                            <?php echo __( 'Allow to change preview answers', QUIZLE_TEXTDOMAIN ) ?>
                        </label>
                    </div>
                    <div class="quizle-form-row">
                        <?php $this->render_select( __( 'Question output format', QUIZLE_TEXTDOMAIN ), 'view-type', $this->quizle_view_type ); ?>
                    </div>
                    <div class="quizle-form-row">
                        <?php $this->render_select( __( 'Progress (with the format of outputting questions only in the form of slides)', QUIZLE_TEXTDOMAIN ), 'quizle-progress', $this->progress_view_type ); ?>
                    </div>
                    <div class="quizle-form-row">
                        <?php $this->render_text_input( __( 'Last step button text', QUIZLE_TEXTDOMAIN ), 'last-step-btn-text', __( 'Show Results', QUIZLE_TEXTDOMAIN ) ); ?>
                    </div>
                    <div class="quizle-form-row">
                        <?php $this->render_text_input(
                            __( 'Emails to get contacts (comma separated values)', QUIZLE_TEXTDOMAIN ),
                            'emails-for-contacts',
                            $this->settings->get_value( 'integrations.emails' )
                        ); ?>
                    </div>

                    <div class="quizle-form-row">
                        <?php $this->render_text_input( __( 'Quiz Completion Time (in seconds)', QUIZLE_TEXTDOMAIN ), 'quizle-completion-time', 0 ); ?>
                    </div>

                    <?php /*
                    <div class="quizle-form-row">
                        <label for="" class="quizle-form-label"><?php echo __( 'Text for All Slides', QUIZLE_TEXTDOMAIN ) ?></label>
                        <textarea name="all-slides-text" rows="3" class="quizle-text"><?php echo esc_textarea( (string) $this->get_value( 'all-slides-text' ) ) ?></textarea>
                    </div>
                    */ ?>

                </div>
            </div>
        </div>

        <div class="quizle-box">
            <div class="quizle-box__header">
                <div class="quizle-box-header__title js-quizle-box-header-action-expand"><?php echo __( 'Appearance', QUIZLE_TEXTDOMAIN ) ?></div>
                <div class="quizle-box-header__actions">
                    <div class="quizle-box-header-action quizle-box-header-action--expand js-quizle-box-header-action-expand"></div>
                    <div class="quizle-box-header-action quizle-box-header-action--doc">
                        <a href="<?php echo $this->doc_link( 'appearance' ) ?>" target="_blank" rel="noopener">?</a>
                    </div>
                </div>
            </div>
            <div class="quizle-box__body" data-show_state="1" data-identity="appearance-screen">
                <div class="quizle-box__form">
                    <div class="quizle-form-row">
                        <div class="quizle-form-cols">
                            <div class="quizle-form-col">
                                <label class="quizle-form-label"><?php echo __( 'Control Color', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input type="text" name="quizle-color-primary" class="js-color-picker" value="<?php echo esc_attr( $this->get_value( 'quizle-color-primary' ) ) ?>"
                                       data-default-color="<?php echo esc_attr( $this->settings->get_default( 'quizle-color-primary' ) ) ?>">
                            </div>
                            <div class="quizle-form-col">
                                <label class="quizle-form-label"><?php echo __( 'Control Text Color', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input type="text" name="quizle-color-text-primary" class="js-color-picker" value="<?php echo esc_attr( $this->get_value( 'quizle-color-text-primary' ) ) ?>"
                                       data-default-color="<?php echo esc_attr( $this->settings->get_default( 'quizle-color-text-primary' ) ) ?>">
                            </div>
                            <div class="quizle-form-col">
                                <label class="quizle-form-label"><?php echo __( 'Background Color', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input type="text" name="quizle-color-background" class="js-color-picker" value="<?php echo esc_attr( $this->get_value( 'quizle-color-background' ) ) ?>"
                                       data-default-color="<?php echo esc_attr( $this->settings->get_default( 'quizle-color-background' ) ) ?>">
                            </div>
                            <div class="quizle-form-col">
                                <label class="quizle-form-label"><?php echo __( 'Text Color', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input type="text" name="quizle-color-text" class="js-color-picker" value="<?php echo esc_attr( $this->get_value( 'quizle-color-text', '#111111' ) ) ?>"
                                       data-default-color="<?php echo esc_attr( $this->settings->get_default( 'quizle-color-text' ) ) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="quizle-form-row">
                        <div class="quizle-form-row">
                            <?php $this->render_text_input( __( 'Quizle Slide Height', QUIZLE_TEXTDOMAIN ), 'quizle-height', '600px' ); ?>
                            <p class="description"><?php echo __( 'Height works only for the Slide output format. If no units are specified, pixels will be used. If the field is empty, the height will depend on the content.', QUIZLE_TEXTDOMAIN ) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            var quizleMetaboxOptions = {
                conditions: {
                    types: [
                        {
                            value: "show",
                            text: "<?php echo esc_js( _x( 'show', 'show conditions', QUIZLE_TEXTDOMAIN ) ) ?>"
                        },
                        {
                            value: "hide",
                            text: "<?php echo esc_js( _x( 'hide', 'show conditions', QUIZLE_TEXTDOMAIN ) ) ?>"
                        },
                    ],
                    relations: [
                        {
                            value: "AND",
                            text: "<?php echo esc_js( _x( 'all', 'show conditions', QUIZLE_TEXTDOMAIN ) ) ?>"
                        },
                        {
                            value: "OR",
                            text: "<?php echo esc_js( _x( 'some of the', 'show conditions', QUIZLE_TEXTDOMAIN ) ) ?>"
                        },
                    ],
                    labelPartial: "<?php echo esc_js( __( 'Partial Match', QUIZLE_TEXTDOMAIN ) ) ?>",
                    labelCaseSensitive: "<?php echo esc_js( __( 'Case Sensitive', QUIZLE_TEXTDOMAIN ) ) ?>"
                }
            };
            var quizleFunctions = {
                getResultBtnText: function (data) {
                    return typeof data.btn_text !== 'undefined' ? data.btn_text : '<?php echo esc_js( __( 'Get more details', QUIZLE_TEXTDOMAIN ) ) ?>'
                }
            }
        </script>

        <script type="text/html" id="tmpl-quizle-question">
            <# // console.log('tmpl-quizle-question', data); #>
            <div class="quizle-question js-quizle-question-item">
                <div class="quizle-question-header quizle-question__header">
                    <div class="quizle-question-header__move"></div>
                    <div class="quizle-question-header__title js-quizle-question-header-title js-quizle-question-action-expand"><?php echo __( 'Question', QUIZLE_TEXTDOMAIN ) ?>
                        <span class="quizle-question-header__title-additional">{{data.title}}</span>
                    </div>
                    <div class="quizle-question-header__actions">
                        <div class="quizle-question-action quizle-question-action--copy" title="<?php echo __( 'Duplicate Question', QUIZLE_TEXTDOMAIN ) ?>"></div>
                        <div class="quizle-question-action quizle-question-action--delete" title="<?php echo __( 'Remove Question', QUIZLE_TEXTDOMAIN ) ?>" data-confirm="<?php echo __( 'Are you sure you want to remove the question?', QUIZLE_TEXTDOMAIN ) ?>"></div>
                        <div class="quizle-question-action quizle-question-action--expand js-quizle-question-action-expand"></div>
                    </div>
                </div>
                <div class="quizle-question__body">

                    <div class="quizle-form-row">
                        <div class="quizle-form-cols">
                            <div class="quizle-form-col">
                                <label for="" class="quizle-form-label"><?php echo __( 'Enter Question', QUIZLE_TEXTDOMAIN ) ?></label>
                                <input type="text" class="quizle-text js-quizle-question-name" data-name="title" value="{{data.title}}">
                            </div>
                            <div class="quizle-form-col">
                                <label class="quizle-form-label"><?php echo __( 'Question Type', QUIZLE_TEXTDOMAIN ) ?></label>
                                <select data-name="type" class="quizle-select js-quizle-question-type">
                                    <?php foreach ( $this->question_types as $item ): ?>
                                        <option
                                                value="<?php echo $item['value'] ?>"
                                                {{data.type=== "<?php echo esc_js( $item['value'] ) ?>" ? "selected" : ""}}><?php esc_html_e( $item['label'], QUIZLE_TEXTDOMAIN ) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="quizle-form-col js-question-columns">
                                <label class="quizle-form-label"><?php echo __( 'Columns', QUIZLE_TEXTDOMAIN ) ?></label>
                                <select data-name="columns" class="quizle-select">
                                    <option value="1" {{data.columns===
                                    "1" ? "selected" : ""}}>1</option>
                                    <option value="2" {{data.columns===
                                    "2" ? "selected" : ""}}>2</option>
                                    <option value="3" {{data.columns===
                                    "3" ? "selected" : ""}}>3</option>
                                    <option value="4" {{data.columns===
                                    "4" ? "selected" : ""}}>4</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="quizle-form-row">
                        <label for="" class="quizle-form-label"><?php echo __( 'Description', QUIZLE_TEXTDOMAIN ) ?></label>
                        <?php
                        $this->wrap_for_editor( 'quizle-question-{{data.question_id}}', function () {
                            ?>
                            <textarea name="" id="quizle-question-{{data.question_id}}" rows="3" class="quizle-text" data-name="description">{{data.description}}</textarea>
                            <?php
                        } );
                        ?>
                    </div>

                    <div class="quizle-form-row js-question-right-answer-description">
                        <label for="" class="quizle-form-label"><?php echo __( 'Right Answer Description', QUIZLE_TEXTDOMAIN ) ?></label>
                        <?php
                        $this->wrap_for_editor( 'quizle-right-answer-description-{{data.question_id}}', function () {
                            ?>
                            <textarea name="" id="quizle-right-answer-description-{{data.question_id}}" rows="3" class="quizle-text" data-name="right_answer_description">{{data.right_answer_description}}</textarea>
                            <?php
                        } );
                        ?>
                    </div>

                    <div class="quizle-form-row">
                        <div class="quizle-question__media js-quizle-question-media"></div>
                    </div>

                    <div class="quizle-answers">
                        <div class="js-quizle-answers-container"></div>
                        <input type="hidden" name="">
                        <span class="button js-add-quizle-answer"><?php echo __( 'Add Answer', QUIZLE_TEXTDOMAIN ) ?></span>
                        <span class="button js-add-quizle-custom-answer"
                              {{{ !data.can_add_custom_answer ? '' : 'style="display: none"' }}}>
                        <?php echo __( 'Add Custom Answer', QUIZLE_TEXTDOMAIN ) ?></span>
                    </div>

                    <div class="quizle-question-settings">
                        <div>
                            <label class="js-quizle-is-required-checkbox">
                                <input type="checkbox" data-name="required" {{data.required ? "checked" : ""}}>
                                <?php echo __( 'Required', QUIZLE_TEXTDOMAIN ) ?>
                            </label>
                            <label class="js-quizle-is-multiple-checkbox">
                                <input type="checkbox" data-name="is_multiple" {{data.is_multiple ? "checked" : ""}}>
                                <?php echo __( 'Multiselect', QUIZLE_TEXTDOMAIN ) ?>
                            </label>
                        </div>
                        <input type="hidden" class="js-quizle-question-id" data-name="question_id" value="{{data.question_id}}">
                    </div>
                    <div class="quizle-question-conditions">
                        <span class="button js-quizle-question--add-conditions"
                              {{{data.can_add_conditions ? '' : 'style="display: none"'}}}
                        ><?php echo __( 'Add Branching', QUIZLE_TEXTDOMAIN ) ?></span>
                    </div>
                </div>
            </div>
        </script>
        <script type="text/html" id="tmpl-quizle-answer">
            <# // console.log('tmpl-quizle-answer', data); #>
            <div class="quizle-answer js-quizle-answer-item">
                <div class="quizle-answer__move"></div>
                <div class="quizle-answer__image js-quizle-answer-image" data-image="{{data.image}}"></div>
                <div class="quizle-answer__answer">
                    <# if (data.type !== 'custom') { #>
                    <input type="text" class="quizle-text" data-name="name" value="{{data.name}}" placeholder="<?php echo __( 'answer text', QUIZLE_TEXTDOMAIN ) ?>">
                    <# } else { #>
                    <span class="js-quizle-answer-custom-text">(<?php echo __( 'custom answer', QUIZLE_TEXTDOMAIN ) ?>)</span>
                    <# } #>
                </div>
                <div class="quizle-answer__score">
                    <input type="number" class="quizle-text js-quizle-answer-score-number" placeholder="<?php echo __( 'score', QUIZLE_TEXTDOMAIN ) ?>">
                    <select class="quizle-select js-quizle-answer-score-select" data-empty_opt_text="<?php echo __( 'select value', QUIZLE_TEXTDOMAIN ) ?>">
                        <option value=""><?php echo __( 'select value', QUIZLE_TEXTDOMAIN ) ?></option>
                    </select>

                    <input type="hidden" data-name="value" value="{{data.value}}">
                </div>
                <div class="quizle-answer__actions">
                    <div class="quizle-answer-action quizle-answer-action--description"
                         title="<?php echo __( 'Add Description', QUIZLE_TEXTDOMAIN ) ?>" style="display: none"></div>
                    <div class="quizle-answer-action quizle-answer-action--delete"
                         title="<?php echo __( 'Remove Answer', QUIZLE_TEXTDOMAIN ) ?>"
                         data-confirm="<?php echo __( 'Are you sure you want to remove the answer?', QUIZLE_TEXTDOMAIN ) ?>"></div>
                </div>
                <input type="hidden" class="js-quizle-answer-id" data-name="answer_id" value="{{data.answer_id}}">
                <input type="hidden" class="js-quizle-answer-type" data-name="type" value="{{data.type ? data.type : 'general' }}">
            </div>
        </script>

        <?php \Wpshop\Quizle\get_template_part(
            'admin/text-html-templates/quizle-result-item',
            null,
            [ 'metaboxes' => $this ] )
        ?>
        <?php \Wpshop\Quizle\get_template_part( 'admin/text-html-templates/quizle-image-uploader' ) ?>
        <?php \Wpshop\Quizle\get_template_part( 'admin/text-html-templates/quizle-media-uploader' ) ?>
        <?php \Wpshop\Quizle\get_template_part( 'admin/text-html-templates/quizle-conditions-group' ) ?>
        <?php \Wpshop\Quizle\get_template_part( 'admin/text-html-templates/quizle-condition-item' ) ?>

        <script type="text/html" id="tmpl-quizle-popup">
            <div class="quizle-popup">
                <div class="quizle-popup__header">
                    {{data.header}}
                    <span class="quizle-popup__close js-quizle-popup-close"></span>
                </div>
                <div class="quizle-popup__body">{{data.content}}</div>
            </div>
        </script>

        <?php
    }

    /**
     * @param string   $prefix
     * @param callable $cb
     *
     * @return void
     */
    public function wrap_for_editor( $id_prefix, $cb ) {
        if ( ! $this->settings->get_value( 'enable_wp_editor' ) ) {
            $cb();

            return;
        }
        ?>
        <div class="wp-editor-wrap" id="<?php echo $id_prefix ?>-wrap">
            <?php $cb() ?>
        </div>
        <?php
    }

    /**
     * @param string $label
     * @param string $name
     *
     * @return void
     */
    protected function render_text_input( $label, $name, $default = '', $placeholder = '' ) {
        echo '<label for="' . $this->get_id( $name ) . '" class="quizle-form-label">' . $label . '</label>';
        echo '<input type="text" class="quizle-text" name="' . $name . '" id="' . $this->get_id( $name ) . '" value="' . esc_attr( $this->get_value( $name, $default ) ) . '" placeholder="' . $placeholder . '">';
    }

    /**
     * @param string $label
     * @param string $name
     *
     * @return void
     */
    protected function render_textarea( $label, $name, $placeholder = '' ) {
        echo '<label for="' . $this->get_id( $name ) . '" class="quizle-form-label">' . $label . '</label>';
        echo '<textarea class="quizle-text" name="' . $name . '" id="' . $this->get_id( $name ) . '" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( (string) $this->get_value( $name ) ) . '</textarea>';
    }

    /**
     * @param string|null $label
     * @param string|null $name
     * @param array       $options
     * @param bool        $fetch_value
     *
     * @return void
     */
    protected function render_select( $label, $name, array $options, $fetch_value = true, $default = null ) {
        $id = '';
        if ( $label ) {
            echo '<label class="quizle-form-label" for="' . $this->get_id( $name ) . '">' . $label . '</label>';
            $id = $this->get_id( $name );
        }

        $value = null;
        if ( is_bool( $fetch_value ) && $fetch_value && $name ) {
            $value = $this->get_value( $name, $default );
        }

        echo '<select name="' . $name . '" id="' . $id . '" class="quizle-select">';
        foreach ( $options as $k => $group_or_option ) {
            if ( is_string( $group_or_option ) ) {
                echo '<option value="' . esc_attr( $k ) . '"' . ( null !== $value ? selected( $value, $k ) : '' ) . '>' . $group_or_option . '</option>';
            } elseif ( $option_items = $group_or_option['items'] ?? [] ) {
                echo '<optgroup label="' . ( $group_or_option['label'] ?? '' ) . '">';
                foreach ( $option_items as $val => $option_label ) {
                    echo '<option value="' . esc_attr( $val ) . '"' . ( null !== $value ? selected( $value, $val ) : '' ) . '>' . $option_label . '</option>';
                }
                echo '</optgroup>';
            }
        }
        echo '</select>';
    }

    /**
     * @param string $label
     * @param string $name
     * @param string $size
     *
     * @return void
     */
    protected function render_media_upload( $label, $name, $size = 'small' ) {
        $url = $this->get_value( $name );
        ?>
        <div class="quizle-image-upload js-quizle-image-uploader">
            <div class="quizle-image-upload__preview quizle-image-upload__preview--<?php echo $size ?> js-quizle-image-preview"<?php echo $url ? '' : ' style="display:none"' ?>>
                <?php if ( $url ): ?>
                    <img src="<?php echo esc_attr( $url ) ?>" class="quizle-image-upload__preview-img">
                <?php endif ?>
            </div>
            <button class="button quizle-image-upload__upload-btn js-quizle-image-browse"<?php echo ! $url ? '' : ' style="display:none"' ?>><?php echo __( 'Upload Image', QUIZLE_TEXTDOMAIN ) ?></button>
            <button class="button quizle-image-upload__remove-btn js-quizle-image-remove"<?php echo $url ? '' : ' style="display:none"' ?>><?php echo __( 'Remove Image', QUIZLE_TEXTDOMAIN ) ?></button>
            <input type="hidden" class="js-quizle-image-url" name="<?php echo $name ?>" value="<?php echo $url ?>">
        </div>
        <?php
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function get_id( $name ) {
        return sanitize_html_class( $name );
    }
}
