<?php

namespace Wpshop\ExpertReview;

use Puc_v4_Factory;
use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Settings\PluginOptions;

class Plugin {

    const TEXT_DOMAIN = 'expert-review';

    /**
     * @var null|bool
     */
    protected $_verify;

    /**
     * @var string
     */
    public $name = 'Expert Review';

    /**
     * @var string
     */
    public $version = EXPERT_REVIEW_VERSION;

    /**
     * @var string
     */
    protected $plugin_file;

    /**
     * @var string
     */
    protected $verify_url;

    /**
     * @var string
     */
    protected $update_url;

    /**
     * @var string
     */
    protected $update_slug;

    /**
     * @var string
     */
    protected $update_check_period;

    /**
     * @var string
     */
    protected $update_option_name;

    /**
     * @var PluginOptions
     */
    protected $options;

    /**
     * Plugin constructor.
     *
     * @param array         $config
     * @param PluginOptions $options
     */
    public function __construct( array $config, PluginOptions $options ) {
        $this->configure( $config );
        $this->options = $options;
    }

    /**
     * @param array $cnf
     *
     * @return void
     */
    protected function configure( array $cnf ) {
        $update_cnf = $cnf['update'];

        $this->verify_url          = $cnf['verify_url'];
        $this->update_url          = $update_cnf['url'];
        $this->update_slug         = $update_cnf['slug'];
        $this->update_check_period = $update_cnf['check_period'];
        $this->update_option_name  = $update_cnf['opt_name'];
    }

    /**
     * @param string $plugin_file
     *
     * @return bool
     */
    public function init( $plugin_file ) {
        $this->plugin_file = $plugin_file;

        $this->load_languages();
//        $this->init_metadata();
        add_action( 'admin_notices', function () {
            $this->license_notice();
        } );
        $this->enqueue_resources( $this->version );
        $this->setup_editor_plugins( $this->version );
        $this->init_updates();

        do_action( __METHOD__, $this );

        add_filter( 'mce_css', [ $this, 'mce_css_styles' ] );

        return true;
    }

    /**
     * @return string
     */
    public function getPluginFile() {
        return $this->plugin_file;
    }

    /**
     * @return void
     */
    protected function load_languages() {
        load_plugin_textdomain( static::TEXT_DOMAIN, false, dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' );
    }

    /**
     * @param string $version
     *
     * @return void
     * @todo move to dedicated class
     */
    protected function enqueue_resources( $version ) {

        if ( $this->verify() ) {
            add_action( 'wp_enqueue_scripts', function () use ( $version ) {
                wp_enqueue_style( 'expert-review-style', plugin_dir_url( __DIR__ ) . 'assets/public/css/styles.min.css', [], $version );
                wp_enqueue_script( 'expert-review-scripts', plugin_dir_url( __DIR__ ) . 'assets/public/js/scripts.min.js', [ 'jquery' ], $version, true );

                do_action( 'expert_review_enqueue_scripts', $version );

                $advanced_options = new AdvancedOptions();
                wp_localize_script( 'expert-review-scripts', 'expert_review_ajax', [
                    'url'                        => admin_url( 'admin-ajax.php' ),
                    'nonce'                      => wp_create_nonce( 'wpshop-nonce' ),
                    'comment_to_scroll_selector' => $advanced_options->comment_to_scroll_selector,
                    'ask_question_link_new_tab'  => $advanced_options->ask_question_link_new_tab,
                    'i18n'                       => [
                        'question_popup_name'     => _x( 'Name', 'js_i18n', static::TEXT_DOMAIN ),
                        'question_popup_email'    => _x( 'Email', 'js_i18n', static::TEXT_DOMAIN ),
                        'question_popup_phone'    => _x( 'Phone', 'js_i18n', static::TEXT_DOMAIN ),
                        'question_popup_question' => _x( 'Question', 'js_i18n', static::TEXT_DOMAIN ),
                        'question_popup_submit'   => _x( 'Submit', 'js_i18n', static::TEXT_DOMAIN ),
                        'question_popup_cancel'   => _x( 'Cancel', 'js_i18n', static::TEXT_DOMAIN ),
                        'question_submitted'      => _x( 'Your question was successfully submitted', 'js_i18n', static::TEXT_DOMAIN ),
                        'legacy_form'             => _x( 'Require to update custom form generation', 'js_i18n', static::TEXT_DOMAIN ),
                        'field_cannot_be_empty'   => _x( 'Field cannot be empty', 'js_i18n', static::TEXT_DOMAIN ),
                        'field_must_be_checked'   => _x( 'Field must be checked', 'js_i18n', static::TEXT_DOMAIN ),
                        'consent'                 => _x( 'Consent to the processing of personal data', 'js_i18n', static::TEXT_DOMAIN ),
                    ],
                    'consent'                    => $advanced_options->enable_consent_checkbox,
                ] );
            } );
        }

        add_action( 'admin_enqueue_scripts', function ( $hook_suffix ) use ( $version ) {
            wp_enqueue_style( 'expert-review-style', plugins_url( 'assets/admin/css/style.min.css', $this->plugin_file ), [], $version );
            wp_enqueue_script( 'expert-review-admin-scripts', plugins_url( 'assets/admin/js/scripts.min.js', $this->plugin_file ), [
                'shortcode',
                'wp-util',
                'jquery',
            ], $version, true );

            do_action( 'expert_review_admin_enqueue_scripts', $version );

            wp_localize_script( 'expert-review-admin-scripts', 'expert_review_globals', [
                'url'              => admin_url( 'admin-ajax.php' ),
                'nonce'            => wp_create_nonce( 'wpshop-nonce' ),
                'editor_icon'      => plugins_url( 'assets/admin/images/tinymce-icon.png', $this->plugin_file ),
                'is_settings_page' => $hook_suffix === 'expert-review_page_expert-review-settings',
                'enabled'          => $this->verify(),
                'i18n'             => [
                    'require_name'                  => _x( 'Please, enter at least name', 'js_i18n', static::TEXT_DOMAIN ),
                    'edit'                          => _x( 'edit', 'js_i18n', static::TEXT_DOMAIN ),
                    'remove'                        => _x( 'remove', 'js_i18n', static::TEXT_DOMAIN ),
                    'save'                          => _x( 'Save', 'js_i18n', static::TEXT_DOMAIN ),
                    'title'                         => _x( 'Expert Review', 'js_i18n', static::TEXT_DOMAIN ),
                    'ok'                            => _x( 'OK', 'js_i18n', static::TEXT_DOMAIN ),
                    'cancel'                        => _x( 'Cancel', 'js_i18n', static::TEXT_DOMAIN ),
                    'link'                          => _x( 'Link', 'js_i18n', static::TEXT_DOMAIN ),
                    'pluses_minuses_title'          => _x( 'Plus & Minus', 'js_i18n', static::TEXT_DOMAIN ),
                    'qa_title'                      => _x( 'Questions to the expert', 'js_i18n', static::TEXT_DOMAIN ),
                    'add_expert_review'             => _x( 'Add Expert Review Block', 'js_i18n', static::TEXT_DOMAIN ),
                    'add_likes'                     => _x( 'Add Likes', 'js_i18n', static::TEXT_DOMAIN ),
                    'add_likes_for_comments'        => _x( 'Add Likes for Comments', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_popup_title'             => _x( 'Expert Review Likes', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_style'             => _x( 'Style', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_size'              => _x( 'Size', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_icons'             => _x( 'Icon', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_alignment'         => _x( 'Alignment', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_show_icon'         => _x( 'Show Icon', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_text_show'               => _x( 'show', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_text_hide'               => _x( 'hide', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_show_label'        => _x( 'Show Label', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_show_count'        => _x( 'Show Count', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_hide_dislikes'     => _x( 'Hide Dislikes', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_like'              => _x( 'Label Like', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_dislike'           => _x( 'Label Dislike', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_name'              => _x( 'Name', 'js_i18n_label_name', static::TEXT_DOMAIN ),
                    'likes_tooltip_name'            => _x( 'If you fill out this field, the likes will be attached to the name and not to the post', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_link'              => _x( 'Link', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_label_post_id'           => _x( 'Post ID', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_tooltip_post_id'         => _x( 'if Name is filled this field will be ignored', 'js_i18n_label_name', static::TEXT_DOMAIN ),
                    'likes_tooltip_link'            => _x( 'If you fill out this field, it will be used in rating table', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_style_options'           => [
                        [
                            'text' => _x( 'Simple 1', 'js_i18n', static::TEXT_DOMAIN ),
                        ],
                    ],
                    'likes_default_value_like'      => _x( 'Like', 'js_i18n', static::TEXT_DOMAIN ),
                    'likes_default_value_dislike'   => _x( 'Dislike', 'js_i18n', static::TEXT_DOMAIN ),
                    'add_likes_rate'                => _x( 'Add Likes Rate', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_popup_title'         => _x( 'Likes Rate', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_title'               => _x( 'Title', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_title_default'       => _x( 'Rate result table', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_style'               => _x( 'Style', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_order'               => _x( 'Order', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_order_asc'           => _x( 'asc', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_order_desc'          => _x( 'desc', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_order_asc_likes'     => _x( 'asc likes', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_order_desc_likes'    => _x( 'desc likes', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_order_asc_dislikes'  => _x( 'asc dislikes', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_order_desc_dislikes' => _x( 'desc dislikes', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_post_ids_title'      => _x( 'Post IDs', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_include_likes_title' => _x( 'Include Likes', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_limit_title'         => _x( 'Output Count', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_total_score'         => _x( 'Show Total Score', 'js_i18n', static::TEXT_DOMAIN ),
                    'like_rate_total_score_text'    => _x( 'show', 'js_i18n', static::TEXT_DOMAIN ),

                    'expert_question_button_text' => _x( 'Ask Question', 'js_i18n', static::TEXT_DOMAIN ),

                    'faq_title'         => _x( 'FAQ', 'js_i18n', static::TEXT_DOMAIN ),
                    'add_faq'           => _x( 'Add FAQ', 'js_i18n', static::TEXT_DOMAIN ),
                    'faq_title_default' => _x( 'Frequently Asked Questions', 'js_i18n', static::TEXT_DOMAIN ),

                    'select_preset' => _x( 'Please, select preset', 'js_i18n', static::TEXT_DOMAIN ),
                    'enter_name'    => _x( 'Please enter name', 'js_i18n', static::TEXT_DOMAIN ),
                    'want_override' => _x( 'Are you sure you want override preset', 'js_i18n', static::TEXT_DOMAIN ),

                    'add_poll' => _x( 'Add Poll', 'js_i18n', static::TEXT_DOMAIN ),
                    'poll'     => _x( 'Poll', 'js_i18n', static::TEXT_DOMAIN ),
                ],
            ] );

            wp_enqueue_media();

            $browseSelector = '.js-wpshop-form-element-browse';
            $urlSelector    = '.js-wpshop-form-element-url';
            $js             = <<<"JS"
jQuery(function($) {
	\$(document).on('click', '{$browseSelector}', function (event) {
	    event.preventDefault();

	    var self = $(this);

	    var fileFrame = wp.media.frames.file_frame = wp.media({
	        title: self.data('uploader_title'),
	        button: {
	            text: self.data('uploader_button_text')
	        },
	        multiple: false
	    });

	    fileFrame.on('select', function () {
	        attachment = fileFrame.state().get('selection').first().toJSON();

	        self.prev('{$urlSelector}').val(attachment.url);
	        self.prev('{$urlSelector}').trigger('change');
	    });

	    fileFrame.open();
	});
});
JS;
            wp_add_inline_script( 'jquery', $js );
        } );
    }

    /**
     * @param string $version
     *
     * @return void
     */
    protected function setup_editor_plugins( $version ) {
        add_action( 'init', function () use ( $version ) {
            if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
                return;
            }

            // tinymce
            add_filter( 'mce_external_plugins', function ( $plugin_array ) use ( $version ) {
                $plugin_array['expert_review_plugin'] = plugins_url( 'assets/admin/js/tinymce.plugin.min.js?v=' . $version, $this->plugin_file );

                return $plugin_array;
            } );

            add_filter( 'mce_buttons', function ( $buttons ) {
                array_push(
                    $buttons,
                    'expert_review_plugin_button'
                );

                return $buttons;
            } );

            // gutenberg
            add_action( 'enqueue_block_editor_assets', function () use ( $version ) {
                wp_enqueue_style( 'expert-review-gutenberg-style', plugins_url() . '/expert-review/assets/admin/css/gutenberg.min.css', [ 'wp-edit-blocks' ], $version );
                wp_enqueue_script( 'expert-review-gutenberg-scripts', plugins_url() . '/expert-review/assets/admin/js/gutenberg.min.js', [
                    'wp-blocks',
                    'wp-element',
                    'wp-editor',
                ], $version, true );
            } );
        } );
    }


    /**
     * Classic tinymce enqueue styles with plugin
     *
     * @param $url
     *
     * @return string
     */
    public function mce_css_styles( $url ) {
        if ( ! empty( $url ) ) {
            $url .= ',';
        }
        $url .= trailingslashit( plugin_dir_url( $this->plugin_file ) ) . 'assets/admin/css/editor-styles.min.css';

        return $url;
    }

    /**
     * @return void
     */
    protected function init_metadata() {
//		add_action( 'plugins_loaded', function () {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $data          = get_plugin_data( $this->plugin_file, false, false );
        $this->version = $data['Version'];
        $this->name    = $data['Name'];
//		} );
    }

    /**
     * @return void
     */
    public function init_updates() {
        if ( ! $this->verify() ) {
            return;
        }
        Puc_v4_Factory::buildUpdateChecker(
            $this->update_url,
            $this->plugin_file,
            $this->update_slug,
            $this->update_check_period,
            $this->update_option_name
        )->addQueryArgFilter( function ( $queryArgs ) {
            if ( $licence = $this->options->license ) {
                $queryArgs['license_key'] = $licence;
            }

            return $queryArgs;
        } );
    }

    /**
     * @return void
     */
    protected function license_notice() {
        if ( ! $this->verify() ) {
            echo '<div class="notice notice-error">';
            echo '<h2>' . __( 'Attention!', static::TEXT_DOMAIN ) . '</h2>';
            echo '<p>' . sprintf( __( 'To activate plugin %s you need to enter the license key on <a href="%s">this page</a>.', static::TEXT_DOMAIN ), $this->name, admin_url( 'admin.php?page=expert-review-settings' ) ) . '</p>';
            echo '</div>';
        }
    }

    /**
     * @return bool
     */
    public function verify() {
        if ( null === $this->_verify ) {
            $license        = $this->options->license;
            $license_verify = $this->options->license_verify;
            $license_error  = $this->options->license_error;

            if ( ! empty( $license ) && ! empty( $license_verify ) && empty( $license_error ) ) {
                //TODO: проверка на истечение лицензии
                $this->_verify = true;
            } else {
                $this->_verify = false;
            }
        }

        return $this->_verify;
    }

    /**
     * @param string $license
     *
     * @return bool
     */
    public function activate( $license ) {
        $url = trim( $this->verify_url );

        if ( ! $url ) {
            $this->options->license_verify = '';
            $this->options->license_error  = __( 'Unable to check license without activation url', static::TEXT_DOMAIN );

            return false;
        }

        $args = [
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => [
                'action'    => 'activate_license',
                'license'   => $license,
                'item_name' => $this->name,
                'version'   => $this->version,
                'type'      => 'plugin',
                'url'       => home_url(),
                'ip'        => Utilities::get_ip(),
            ],
        ];

        $response = wp_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            $response = wp_remote_post( str_replace( "https", "http", $url ), $args );
        }

        if ( is_wp_error( $response ) ) {
            $this->options->license_verify = '';
            $this->options->license_error  = __( 'Can\'t get response from license server', $this->options->text_domain );

            return false;
        }

        $body = wp_remote_retrieve_body( $response );

        if ( mb_substr( $body, 0, 2 ) == 'ok' ) {
            $this->options->license        = $license;
            $this->options->license_verify = time() + ( WEEK_IN_SECONDS * 4 );
            $this->options->license_error  = '';

            return true;
        }

        $this->options->license_verify = '';
        $this->options->license_error  = $body;

        return false;
    }
}
