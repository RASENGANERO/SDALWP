<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Wpshop\Quizle;

use WP_Post;
use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Css\CssBuilder;

class Shortcodes {

    const SHORTCODE_QUIZLE = 'quizle';

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Encryption
     */
    protected $encryption;

    /**
     * @var Social
     */
    protected $social;

    /**
     * @var bool
     */
    protected $fist_question_rendered = false;

    /**
     * @var bool
     */
    protected $has_file_upload_question = false;

    /**
     * Shortcodes constructor.
     *
     * @param Settings   $settings
     * @param Encryption $encryption
     * @param Social     $social
     */
    public function __construct(
        Settings $settings,
        Encryption $encryption,
        Social $social
    ) {
        $this->settings   = $settings;
        $this->encryption = $encryption;
        $this->social     = $social;
    }

    /**
     * @return void
     */
    public function init() {
        add_shortcode( self::SHORTCODE_QUIZLE, [ $this, '_shortcode_quizle' ] );
        add_shortcode( 'quizle_page_content', [ $this, '_page_content' ] );
    }

    /**
     * @param $atts
     * @param $content
     * @param $shortcode
     *
     * @return mixed|string|void
     */
    public function _page_content( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'id' => 0,
        ], $atts, $shortcode );

        if ( $post = get_post( (int) $atts['id'] ) ) {
            return apply_filters( 'the_content', $post->post_content );
        }

        return '';
    }

    /**
     * @param string $name
     *
     * @return WP_Post|null
     * @deprecated
     */
    protected function get_post( $name ) {
        return get_quizle( $name );
    }

    /**
     * @param array  $atts
     * @param string $content
     * @param string $shortcode
     *
     * @return string
     */
    public function _shortcode_quizle( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( [
            'name'   => '',
            'width'  => '',
            'height' => '',
            'color'  => '',
        ], $atts, $shortcode );

        /**
         * Allows to prevent output of the shortcode
         */
        $do_output = apply_filters( 'quizle/shortcode/do_output', true, $shortcode, $atts );
        if ( ! $do_output ) {
            return '';
        }

        if ( is_wp_error( $context = Context::createFromWpQuery() ) ) {
            $context = '';
        }

        $quizle = get_quizle(
            $atts['name'],
            $context && $context->is_preview ? 'any' : 'publish'
        );

        if ( empty( $atts['name'] ) || ! $quizle ) {
            return '<!-- wrong quizle name -->';
        }

        $quiz_type      = get_quizle_type( $quizle );
        $has_conditions = get_post_meta( $quizle->ID, 'quizle-has-conditions', true );
        // questions with conditions used with slides only
        $quiz_view_type = $has_conditions ? 'slides' : get_post_meta( $quizle->ID, 'view-type', true );
        if ( empty( $quiz_type ) ) {
            $quiz_view_type = Quizle::VIEW_TYPE_SLIDES;
        }

        $output_description = $quiz_type === Quizle::TYPE_TEST && get_post_meta( $quizle->ID, 'test-instant-answer', true );

//        $sidebar = $this->output_sidebar();
        $sidebar = '';

        $welcome_enabled = get_post_meta( $quizle->ID, 'welcome-enabled', true );
        $questions       = get_post_meta( $quizle->ID, 'quizle-questions', true );

        $random_questions = (int) get_post_meta( $quizle->ID, 'random-questions', true );
        $random_answers   = (int) get_post_meta( $quizle->ID, 'random-answers', true );

        if ( ! empty( $questions ) ) {
            $questions = json_decode( $questions, true );
        }

        if ( empty( $questions ) ) {
            if ( current_user_can( 'edit_posts' ) ) {
                $html = '<div class="quizle">';
                $html .= __( 'This quiz does not contain questions.', QUIZLE_TEXTDOMAIN );
                $html .= sprintf( '<br><a href="%s" target="_blank">%s</a>', get_edit_post_link( $quizle ), __( 'Edit quiz', QUIZLE_TEXTDOMAIN ) );
                $html .= '</div>';

                return $html;
            }

            return '<!-- quizle: quiz does not contain questions -->';
        }

        $n = 0;
        array_walk( $questions, function ( &$item ) use ( &$n ) {
            $item['_index'] = $n ++;
        } );

        $question_html = '';

        $right_answers = [];

        $count_questions_without_conditions = 0;

        $fist_question_required = null;

        $n = 0;
        if ( ! empty( $questions ) ) {

            // В случайном порядке вопросы
            if ( ! $has_conditions && $random_questions ) {
                shuffle( $questions );
            }

            foreach ( $questions as $question ) {
                if ( null === $fist_question_required ) {
                    $fist_question_required = boolval( $question['required'] ?? false );
                }

                $n ++;
                if ( empty( $question['conditions'] ) ) {
                    $count_questions_without_conditions ++;
                }

                $answer_n = 0;
                $answers  = $question['answers'] ?? [];
                array_walk( $answers, function ( &$answer ) use ( &$answer_n ) {
                    $answer['_index'] = $answer_n ++;
                } );

                if ( $random_answers ) {
                    shuffle( $answers );
                }

                $question['answers'] = $answers;

                if ( Quizle::TYPE_TEST === $quiz_type ) {
                    $right_answers[ $question['question_id'] ] = $this->get_right_answers( $answers );
                }

                $question_html .= $this->display_question( $quizle, $question, $n, $quiz_view_type, $atts, $output_description );
            }
        }


        $quizle_body_style = '';
        if ( ! empty( $welcome_enabled ) ) {
            $quizle_body_style = ' style="display: none;"';
        }

        $progress = $this->display_progress( $quizle, $count_questions_without_conditions, $atts );

        $quizle_classes   = [];
        $quizle_classes[] = 'quizle--' . $quizle->ID;
        $quizle_classes[] = 'quizle--view-' . $quiz_view_type;

        // если есть фоновый цвет, тогда назначаем класс
        $color_background = get_post_meta( $quizle->ID, 'quizle-color-background', true );
        if ( ! empty( $color_background ) ) {
            $quizle_classes[] = 'has-background';
        }
        if ( ! empty( get_quizle_height( $quizle ) ) ) {
            $quizle_classes[] = 'has-height';
        }

        $styles = new CssBuilder( '.quizle--' . $quizle->ID );
        $styles->add( '', [
            '--quizle-primary-color'      => get_post_meta( $quizle->ID, 'quizle-color-primary', true ) ?: null,
            '--quizle-primary-color-text' => get_post_meta( $quizle->ID, 'quizle-color-text-primary', true ) ?: null,
            '--quizle-background'         => get_post_meta( $quizle->ID, 'quizle-color-background', true ) ?: null,
            '--quizle-background-1'       => get_post_meta( $quizle->ID, 'quizle-color-background-1', true ) ?: null,
            '--quizle-background-2'       => get_post_meta( $quizle->ID, 'quizle-color-background-2', true ) ?: null,
            '--quizle-text-color'         => get_post_meta( $quizle->ID, 'quizle-color-text', true ) ?: null,
            '--quizle-height'             => get_quizle_height( $quizle ),
        ] );

        $html = '';

        [ $result_enabled, $quizle_results ] = $this->get_results_data( $quizle->ID );

        $options = [
            'type'                        => $quiz_type,
            'view_type'                   => $quiz_view_type,
            'animation_duration'          => 200,
            'progress_animation_duration' => $this->settings->get_value( 'progress_animation_duration' ),
            'completion_time'             => absint( get_post_meta( $quizle->ID, 'quizle-completion-time', true ) ),
            'contacts_order'              => $result_enabled && ! empty( $quizle_results )
                ? ( get_post_meta( $quizle->ID, 'contact-enabled', true )
                    ? get_post_meta( $quizle->ID, 'contact-form-order', true )
                    : null )
                : 'only_contacts',
            'save_contacts_and_results'   => ! is_preview() && get_post_meta( $quizle->ID, 'save-quizle-contacts-and-results', true ),
            'can_change_answer'           => (bool) get_post_meta( $quizle->ID, 'can-change-answer', true ),
            'prevent_autofocus'           => (bool) $this->settings->get_value( 'prevent_autofocus' ),
        ];
        if ( $quiz_type === Quizle::TYPE_TEST ) {
            $options['show_results'] = (bool) get_post_meta( $quizle->ID, 'test-instant-answer', true );
        }

        /**
         * Allows to modify quizle options
         *
         * @since 1.3
         */
        $options = apply_filters( 'quizle/shortcode/options', $options, $quizle );

        $html .= '<div class="quizle-container">';
        $html .= '<style>' . $styles . '</style>';
        if ( is_debug() ) {
            $html .= sprintf( '<a href="%s" target="_blank">edit quizle</a>', get_edit_post_link( $quizle ) );
        }

        $attributes = [
            'data-identity'        => esc_attr( $quizle->post_name ),
            'data-title'           => esc_attr( $quizle->post_title ),
            'class'                => 'quizle ' . implode( ' ', $quizle_classes ) . ' js-quizle',
            'data-options'         => esc_json( json_encode( $options ) ),
            'data-questions_count' => $count_questions_without_conditions,
            'data-context'         => (string) $context,
        ];

        if ( $right_answers ) {
            $attributes['data-results'] = $this->encryption->encrypt_text( json_encode( $right_answers ) );
        }
        $attributes = build_attributes( $attributes );

        $html .= "  <div {$attributes}>";

        if ( ! empty( $welcome_enabled ) ) {
            $html .= $this->output_welcome( $quizle->ID, $atts );
        }

        $html .= '<div class="quizle-body js-quizle-body"' . $quizle_body_style . '>';

        $html .= '    <div class="quizle-expiration js-quizle-expiration-countdown" style="display: none">';
        $html .= '        <div class="quizle-expiration__progress"><div class="quizle-expiration__progress-line js-quizle-expiration-countdown-progress"></div></div>';
        $html .= '        <span class="quizle-expiration__text">' . __( 'Time left:', 'quizle' ) . ' <span class="quizle-expiration__time js-quizle-expiration-countdown-time"></span></span>';
        $html .= '    </div><!--.quizle-expiration-->';

        $html .= '  <div class="quizle-questions js-quizle-questions">';
        $html .= $question_html;
        $html .= '  </div><!--.quizle-questions-->';

//        if ( $all_slides_text = trim( get_post_meta( $quizle->ID, 'all-slides-text', true ) ) ) {
//            $html .= '<div>';
//            $html .= $all_slides_text;
//            $html .= '</div>';
//        }

        $contacts_html = $this->output_contacts( $quizle->ID, $atts );

        $html .= $this->output_expiration_screen( $quizle->ID );
        $html .= $this->output_file_uploading();
        $html .= $this->output_result( $quizle->ID );
        $html .= $contacts_html;

        if ( $quiz_view_type == Quizle::VIEW_TYPE_SLIDES ) {
            $prev_btn_text   = apply_filters( 'quizle/steps/prev_btn_text', __( 'Previous', QUIZLE_TEXTDOMAIN ), $quizle, $atts );
            $next_btn_text   = apply_filters( 'quizle/steps/next_btn_text', __( 'Next', QUIZLE_TEXTDOMAIN ), $quizle, $atts );
            $finish_btn_text = apply_filters( 'quizle/steps/finish_btn_text', get_post_meta( $quizle->ID, 'last-step-btn-text', true ) ?: __( 'Finish', QUIZLE_TEXTDOMAIN ), $quizle, $atts );

            $quizle_footer_classes = [
                'quizle-footer',
                'js-quizle-footer',
            ];

            if ( ! empty( $progress ) ) {
                $quizle_footer_classes[] = 'quizle-footer--has-progress';
            }

            $html .= '  <div class="' . implode( ' ', $quizle_footer_classes ) . '">';

            $html .= $progress;

            $html .= '    <div class="quizle-footer__steps js-quizle-steps">';

            $html .= '      <div class="quizle-footer__step">';
            $html .= '        <button class="quizle-button js-quizle-prev-step" data-direction="prev" disabled>' . $prev_btn_text . '</button>';
            $html .= '      </div>';
            $html .= '      <div class="quizle-footer__step">';

            $disabled = $fist_question_required ? ' disabled' : '';

            $html .= '        <button class="quizle-button js-quizle-next-step" data-direction="next" data-finish_text="' . esc_attr( $finish_btn_text ) . '"' . $disabled . '>';
            $html .= $next_btn_text . '</button>';
            $html .= '        <div class="quizle-footer__step-description js-quizle-next-step-description" data-shift="' . __( 'Enter', QUIZLE_TEXTDOMAIN ) . ' shift+↵">' . __( 'Enter', QUIZLE_TEXTDOMAIN ) . ' ↵</div>';
            $html .= '      </div>';

            $html .= '    </div>';
            $html .= '  </div>';
        } else {
            $html .= '  <div class="quizle-footer js-quizle-footer">';
            $html .= '    <button class="quizle-button js-quizle-list-submit">' . __( 'Submit Answers', QUIZLE_TEXTDOMAIN ) . '</button>';
            $html .= '  </div>';
        }

//        $html .= '    <div class="quizle-expiration js-quizle-expiration-countdown" style="display: none">';
//        $html .= '        <span class="quizle-expiration__text">' . __( 'Time left:', 'quizle' ) . ' <span class="quizle-expiration__time js-quizle-expiration-countdown-time"></span></span>';
//        $html .= '        <div class="quizle-expiration__progress"><div class="quizle-expiration__progress-line js-quizle-expiration-countdown-progress"></div></div>';
//        $html .= '    </div><!--.quizle-expiration-->';
        $html .= '</div><!--.quizle-body-->';

//        $html .= $sidebar;

        $html .= '  </div>';
        $html .= '</div>';


        return $html;
    }

    /**
     * Used to reveal the right answers in test quiz
     *
     * @param array $answers
     *
     * @return array
     */
    protected function get_right_answers( $answers ) {
        $result = [];

        if ( ! $answers ) {
            return $result;
        }

        $max = max( array_map( function ( $item ) {
            return intval( $item['value'] );
        }, $answers ) );

        if ( $max ) {
            foreach ( $answers as $answer ) {
                if ( intval( $answer['value'] ) == $max ) {
                    $result[] = [ 'id' => $answer['answer_id'], 'val' => $answer['value'] ];
                }
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function output_sidebar() {
        $sidebar = <<<HTML
<div class="quizle-sidebar">
            <div class="quizle-sidebar__header">Sidebar header</div>
            <div class="quizle-sidebar__body">sidebar sidebar here sidebar check test sidebar yes sidebar sidebar</div>
        </div>
HTML;

        return $sidebar;
    }

    /**
     * @return string
     */
    protected function output_file_uploading() {
        if ( ! $this->has_file_upload_question ) {
            return '';
        }

        return '<div class="quizle-upload-screen js-quizle-upload-screen" style="display: none">' .
               '<svg xmlns="http://www.w3.org/2000/svg" width="66" height="86" fill="none"><path fill="currentColor" d="M12.015 69.36c0-.704.218-.903.615-1.197l.695-.52c3.711-2.788 14.486-10.878 19.598-10.878 5.107 0 15.87 8.079 19.589 10.871.275.207.511.384.704.527.395.294.614.493.614 1.197v6.588s.078.73-.75.73l-14.206.016-5.95.006a388120.65 388120.65 0 0 1-20.159-.022c-.828 0-.75-.73-.75-.73V69.36Z"/><path fill="currentColor" fill-rule="evenodd" d="M62.431 21.333c0 6.865-4.086 9.7-5.831 10.91-1.38.959-11.92 7.268-15.953 9.677v4.137l16.017 9.698c3.683 2.23 5.767 3.822 5.767 10.62v12.438h1.948c.891 0 1.621.725 1.621 1.61v3.967c0 .885-.73 1.61-1.62 1.61H1.62A1.62 1.62 0 0 1 0 84.39v-3.967c0-.885.728-1.61 1.62-1.61h1.948v-12.18c0-6.867 4.086-9.7 5.83-10.91 1.382-.958 11.921-7.268 15.955-9.678v-4.136L9.334 32.21c-3.682-2.23-5.766-3.822-5.766-10.622V7.187H1.62A1.62 1.62 0 0 1 0 5.577V1.609C0 .724.728 0 1.62 0h62.759c.891 0 1.62.724 1.62 1.61v3.967c0 .885-.729 1.61-1.62 1.61H62.43v14.146Zm-8.802 39.372L36.212 50.16a2.898 2.898 0 0 1-1.4-2.476V40.28c0-1.016.537-1.96 1.415-2.483 6.246-3.725 15.935-9.544 17.035-10.308 1.407-.975 3.335-2.313 3.335-6.156V7.187H9.4v14.402c0 1.999.193 3.274.59 3.896.33.52 1.042.967 2.378 1.776l17.419 10.546a2.89 2.89 0 0 1 1.399 2.473v7.404a2.895 2.895 0 0 1-1.415 2.486c-6.245 3.725-15.935 9.544-17.035 10.306-1.408.976-3.336 2.313-3.336 6.158v12.179h47.197V66.375c0-1.999-.192-3.273-.588-3.896-.329-.518-1.041-.965-2.38-1.774Z" clip-rule="evenodd"/></svg>' .
               '<p>' . __( 'Uploading files...', 'quizle' ) . '</p>' .
               '</div>';
    }

    /**
     * @return string
     */
    protected function output_expiration_screen( $quizle_id ) {
        if ( get_post_meta( $quizle_id, 'quizle-completion-time', true ) ) {
            return '<div class="quizle-completion-expire js-quizle-completion-expire" style="display: none">' .
                   '<svg xmlns="http://www.w3.org/2000/svg" width="66" height="86" fill="none"><path fill="currentColor" d="M12.015 69.36c0-.704.218-.903.615-1.197l.695-.52c3.711-2.788 14.486-10.878 19.598-10.878 5.107 0 15.87 8.079 19.589 10.871.275.207.511.384.704.527.395.294.614.493.614 1.197v6.588s.078.73-.75.73l-14.206.016-5.95.006a388120.65 388120.65 0 0 1-20.159-.022c-.828 0-.75-.73-.75-.73V69.36Z"/><path fill="currentColor" fill-rule="evenodd" d="M62.431 21.333c0 6.865-4.086 9.7-5.831 10.91-1.38.959-11.92 7.268-15.953 9.677v4.137l16.017 9.698c3.683 2.23 5.767 3.822 5.767 10.62v12.438h1.948c.891 0 1.621.725 1.621 1.61v3.967c0 .885-.73 1.61-1.62 1.61H1.62A1.62 1.62 0 0 1 0 84.39v-3.967c0-.885.728-1.61 1.62-1.61h1.948v-12.18c0-6.867 4.086-9.7 5.83-10.91 1.382-.958 11.921-7.268 15.955-9.678v-4.136L9.334 32.21c-3.682-2.23-5.766-3.822-5.766-10.622V7.187H1.62A1.62 1.62 0 0 1 0 5.577V1.609C0 .724.728 0 1.62 0h62.759c.891 0 1.62.724 1.62 1.61v3.967c0 .885-.729 1.61-1.62 1.61H62.43v14.146Zm-8.802 39.372L36.212 50.16a2.898 2.898 0 0 1-1.4-2.476V40.28c0-1.016.537-1.96 1.415-2.483 6.246-3.725 15.935-9.544 17.035-10.308 1.407-.975 3.335-2.313 3.335-6.156V7.187H9.4v14.402c0 1.999.193 3.274.59 3.896.33.52 1.042.967 2.378 1.776l17.419 10.546a2.89 2.89 0 0 1 1.399 2.473v7.404a2.895 2.895 0 0 1-1.415 2.486c-6.245 3.725-15.935 9.544-17.035 10.306-1.408.976-3.336 2.313-3.336 6.158v12.179h47.197V66.375c0-1.999-.192-3.273-.588-3.896-.329-.518-1.041-.965-2.38-1.774Z" clip-rule="evenodd"/></svg>' .
                   '<p>' . __( 'Quizle completion time has expired.', 'quizle' ) . '</p>' .
                   '</div>';
        }

        return '';
    }

    /**
     * @param int $quizle_id
     *
     * @return string
     */
    protected function output_result( $quizle_id ) {
        [ $result_enabled, $quizle_results ] = $this->get_results_data( $quizle_id );

        if ( ! $result_enabled || empty( $quizle_results ) ) {
            return '';
        }

        $progress_bar_text = apply_filters( 'quizle/progress_bar/text', __( 'Preparing Results...', QUIZLE_TEXTDOMAIN ) );
        $progress          = '<div class="quizle-progress-bar-container"><div class="quizle-progress-bar"><div class="quizle-progress-bar__inner js-result-progress-bar" style="width: 0"></div></div><div class="quizle-progress-bar__text">' . $progress_bar_text . '</div></div>';

        return '<div class="quizle-results js-quizle-results" style="display: none">' . $progress . '</div>';

    }

    /**
     * @param int $quizle_id
     *
     * @return array
     */
    protected function get_results_data( $quizle_id ) {
        $result_enabled = get_post_meta( $quizle_id, 'result-enabled', true );
        $quizle_results = get_post_meta( $quizle_id, 'quizle-results', true );
        if ( ! empty( $quizle_results ) ) {
            $quizle_results = json_decode( $quizle_results, true );
        }

        return [ $result_enabled, $quizle_results ];
    }

    /**
     * @param int   $quizle_id
     * @param array $atts
     *
     * @return string|null
     */
    protected function output_contacts( $quizle_id, $atts ) {

        $contact_enabled      = get_post_meta( $quizle_id, 'contact-enabled', true );
        $contact_with_name    = get_post_meta( $quizle_id, 'contact-with-name', true );
        $contact_with_email   = get_post_meta( $quizle_id, 'contact-with-email', true );
        $contact_with_phone   = get_post_meta( $quizle_id, 'contact-with-phone', true );
        $contact_title        = get_post_meta( $quizle_id, 'contact-title', true );
        $contact_description  = apply_filters( 'quizle/contacts/description', get_post_meta( $quizle_id, 'contact-description', true ), $quizle_id, $atts );
        $contact_btn_text     = get_post_meta( $quizle_id, 'contact-btn-text', true );
        $contact_privacy_text = get_post_meta( $quizle_id, 'contact-privacy-text', true ) ?: $this->settings->get_value( 'privacy_policy' );
        $contact_message      = apply_filters( 'quizle/contacts/message', get_post_meta( $quizle_id, 'contact-message', true ), $quizle_id, $atts );
        $contact_form_order   = get_post_meta( $quizle_id, 'contact-form-order', true );

        $messengers = $this->social->get_quizle_messengers( $quizle_id );

        if ( empty( $contact_btn_text ) ) {
            // todo из настроек плагина
            $contact_btn_text = __( 'Submit', QUIZLE_TEXTDOMAIN );
        }

        if ( ! $contact_enabled ) {
            return null;
        }

        $user       = wp_get_current_user();
        $user_name  = apply_filters( 'quizle/contacts/user_name', $user->display_name, $user );
        $user_email = $user->user_email;

        $html = '';
        $html .= '<div class="quizle-contacts js-quizle-contacts" style="display: none;">';

        // если есть заголовок или описание -- выводи их
        if ( ! empty( $contact_title ) || ! empty( $contact_description ) ) {
            $html .= '<div class="quizle-contacts__body">';
            if ( ! empty( $contact_title ) ) {
                $html .= '<div class="quizle-contacts__title">' . $contact_title . '</div>';
            }
            if ( ! empty( $contact_description ) ) {
                $html .= '<div class="quizle-contacts__description">' . $contact_description . '</div>';
            }
            $html .= '</div>'; // .quizle-contacts__body
        }

        $html .= '<div class="quizle-contacts__fields js-quizle-contact-data">';

        $html .= '<form><fieldset>';

        if ( $contact_with_name ) {
            $html .= '<div class="quizle-contacts__field"><input type="text" name="data[contacts][name]" value="' . $user_name . '" required placeholder="' . esc_attr__( 'Name', QUIZLE_TEXTDOMAIN ) . '" class="quizle-text"></div>';
        }
        if ( $contact_with_email ) {
            $html .= '<div class="quizle-contacts__field"><input type="email" name="data[contacts][email]" value="' . $user_email . '" required placeholder="' . esc_attr__( 'E-mail', QUIZLE_TEXTDOMAIN ) . '" class="quizle-text"></div>';
        }
        if ( $contact_with_phone ) {
            if ( $this->settings->get_value( 'enable_phone_mask' ) ) {
                $html .= '<div class="quizle-contacts__field js-quizle-contacts-phone">';
                $html .= '  <input type="tel" name="" required placeholder="" class="quizle-text">';
                $html .= '  <input type="hidden" name="data[contacts][phone]">';
                $html .= '</div>';
            } else {
                $html .= '<div class="quizle-contacts__field"><input type="text" name="data[contacts][phone]" required placeholder="' . esc_attr__( 'Phone', QUIZLE_TEXTDOMAIN ) . '" class="quizle-text"></div>';
            }
        }

        foreach ( $messengers as $messenger_key => $messenger ) {

            /**
             * Allows to set messenger required
             *
             * @since 1.2
             */
            $required = apply_filters( 'quizle/contacts/messenger_required', false, $messenger_key );
            $required = $required ? ' required' : '';

            $style = '';
            if ( $this->settings->get_value( 'user_messengers_brand_colors' ) ) {
                $style = ' style="color: ' . $messenger['color'] . '"';
            }

            $html .= '<div class="quizle-contacts__field">';
            $html .= '  <div class="quizle-contact-messengers">';
            $html .= '    <svg xmlns="http://www.w3.org/2000/svg" class="quizle-contact-messengers__icon" viewBox="0 0 32 32"><path d="' . $messenger['path'] . '" fill="currentColor"' . $style . '></path></svg>';
            $html .= '    <input type="text" name="data[messengers][' . $messenger_key . ']" placeholder="' . esc_attr( $messenger['title'] ) . '" class="quizle-text quizle-contact-messengers__input"' . $required . '>';
            $html .= '  </div>';
            $html .= '</div>';
        }

        $btn_attributes = [
            'class'           => 'quizle-button',
            'type'            => 'submit',
            'data-toggle_txt' => __( 'Submitting data...', QUIZLE_TEXTDOMAIN ),
        ];
        if ( $this->settings->get_value( 'grecaptcha.enabled' ) ) {
            $btn_attributes['class'] .= ' g-recaptcha';
        }

        $html .= '<div class="quizle-contacts__buttons"><button disabled ' . build_attributes( $btn_attributes ) . '>' . $contact_btn_text . '</button></div>';

        $html .= '</fieldset></form>';

        if ( ! empty( $contact_privacy_text ) ) {
            $html .= '<label class="quizle-contacts__agreement"><input type="checkbox" class="js-quizle-contacts-agreement" checked><span>' . $contact_privacy_text . '</span></label>';
        }

        $html .= '</div>'; // .quizle-contacts__fields

        $html .= '</div>'; // .quizle-contacts

        return $html;
    }

    /**
     * @param int   $quizle_id
     * @param array $atts
     *
     * @return string
     */
    protected function output_welcome( $quizle_id, $atts ) {
        $welcome_title        = get_post_meta( $quizle_id, 'welcome-title', true );
        $welcome_description  = apply_filters( 'quizle/welcome/description', get_post_meta( $quizle_id, 'welcome-description', true ), $quizle_id, $atts );
        $welcome_button_text  = get_post_meta( $quizle_id, 'welcome-button-text', true );
        $welcome_img          = get_post_meta( $quizle_id, 'welcome-img', true );
        $welcome_img_position = get_post_meta( $quizle_id, 'welcome-img-position', true );

        if ( empty( $welcome_button_text ) ) {
            // todo из настроек должно быть?
            $welcome_button_text = __( 'Start', QUIZLE_TEXTDOMAIN );
        }

        $welcome_classes = [];
        $welcome_style   = '';
        if ( ! empty( $welcome_img ) ) {
            $welcome_classes[] = 'quizle-image-screen--img-position-' . $welcome_img_position;
            if ( $welcome_img_position == 'background' ) {
                $welcome_style = 'background-image: url(\'' . $welcome_img . '\');';
            }
        }

        $html = '';
        $html .= '<div class="quizle-image-screen quizle-welcome js-quizle-welcome ' . implode( ' ', $welcome_classes ) . '" style="' . $welcome_style . '">';

        if ( ! empty( $welcome_img ) && $welcome_img_position != 'background' ) {
            $html .= '<div class="quizle-image-screen__image"><img src="' . $welcome_img . '" alt=""></div>';
        }

        $html .= '<div class="quizle-image-screen__container">'; // for scroll
        $html .= '<div class="quizle-image-screen__body">';
        $html .= '  <div class="quizle-image-screen__title">' . $welcome_title . '</div>';

        if ( ! empty( $welcome_description ) ) {
            $html .= '  <div class="quizle-image-screen__description">' . $welcome_description . '</div>';
        }

        $html .= '  <div class="quizle-image-screen__button">';
        $html .= '    <button class="quizle-button js-quizle-welcome-start">' . $welcome_button_text . '</button>';
        $html .= '  </div>';

        $html .= '</div>'; // .quizle-image-screen__body
        $html .= '</div>'; // .quizle-image-screen__container
        $html .= '</div>'; // .quizle-welcome

        return $html;
    }


    /**
     * @param WP_Post $quizle
     * @param int     $count_questions
     * @param array   $atts
     *
     * @return string
     */
    protected function display_progress( $quizle, $count_questions, $atts ) {
        $type = get_post_meta( $quizle->ID, 'quizle-progress', true );

        $html = '';

        $attributes = build_attributes( [
            'class'     => 'quizle-progress-container js-quizle-progress quizle-progress-type--' . $type,
            'data-type' => esc_attr( $type ?: 'none' ),
        ] );

        if ( 'line' === $type ) {
            $html .= '  <div class="quizle-progress">';
            $html .= '    <div class="quizle-progress__bar js-quizle-progress-bar" style="width: 0;"></div>';
            $html .= '  </div>';
            if ( apply_filters( 'quizle/progress/is_text_shown', true, $quizle, $atts ) ) {
                $html .= '<div class="quizle-progress__text">';
                $html .= __( 'Completed: ', QUIZLE_TEXTDOMAIN );
                $html .= '<span class="quizle-progress__percent js-quizle-progress-percent">0%</span>';
                $html .= '</div>';
            }
        } elseif ( 'numbers' === $type ) {
            $html .= '  <div class="quizle-progress">';
            $html .= '  <div><span class="js-quizle-progress-step">0</span> / <span class="js-quizle-progress-total">' . $count_questions . '</span></div>';
            $html .= '  </div>';
        }

        // если есть прогресс, оборачиваем в контейнер
        if ( ! empty( $html ) ) {
            $html = "<div $attributes>" . $html . "</div>";
        }

        return $html;
    }

    /**
     * @param WP_Post $quizle
     * @param string  $question
     * @param int     $n
     * @param string  $quiz_view_type
     * @param array   $atts
     * @param bool    $output_description
     *
     * @return string
     */
    protected function display_question( $quizle, $question, $n, $quiz_view_type, $atts, $output_description = false ) {

        $question = wp_parse_args( $question, [
            'title'       => '',
            'description' => '',
            'type'        => 'text',
            'columns'     => 1,
            'is_multiple' => false,
            'required'    => false,
        ] );

        if ( $question['type'] == 'file' && ! is_file_upload_allowed( $quizle ) ) {
            return '';
        }

        $question_style = null;
        if ( $quiz_view_type == Quizle::VIEW_TYPE_SLIDES && $this->fist_question_rendered ) {
            $question_style = 'display: none;';
        }

        $media = wp_parse_args( $question['media'] ?? '', [
            'id'       => '',
            'url'      => '',
            'type'     => '',
            'position' => 'right',
        ] );

        $attributes = [
            'class'            => 'quizle-question' . ( $media['url'] ? ' quizle-question--media-position-' . $media['position'] : '' ) . ' js-quizle-question',
            'style'            => $question_style,
            'data-required'    => $question['required'] ? 1 : '',
            'data-identity'    => $question['question_id'],
            'data-type'        => $question['type'],
            'data-index'       => $n,
            'data-is_multiple' => $question['is_multiple'] ? 1 : '',
        ];

        if ( ! empty( $question['conditions'] ) ) {
            $attributes['data-conditions'] = esc_attr( wp_json_encode( $question['conditions'], JSON_UNESCAPED_UNICODE ) );
        }

        $attributes_string = build_attributes( $attributes );

        $question['title']                    = apply_filters( 'quizle/question/title', $question['title'], $atts, $question );
        $question['description']              = apply_filters( 'quizle/question/description', $question['description'], $atts, $question );
        $question['right_answer_description'] = apply_filters( 'quizle/question/right_answer_description', $question['right_answer_description'] ?? '', $atts, $question );

        $html = "<div {$attributes_string}>";

        $media_html = '';
        if ( $media['url'] ) {
            $video_width  = 300;
            $video_height = 200;

            if ( $media_html_custom = apply_filters( 'quizle/question/media_html', null, $media, $quizle ) ) {
                $media_html .= $media_html_custom;
            } else {
                if ( 'video' === $media['type'] ) {
                    $media_html .= '<video class="quizle-question__video quizle-question__video--file js-quizle-video" controls preload="auto" width="' . $video_width . '" height="' . $video_height . '">';
                    $media_html .= '<source src="' . $media['url'] . '" />';
                    $media_html .= '</video>';
                } elseif ( 'image' == $media['type'] ) {
                    $media_html .= '<img class="quizle-question__image" src="' . $media['url'] . '">';
                } else {
                    global $wp_embed;
                    $orig_global_post = $GLOBALS['post'] ?? null;
                    $GLOBALS['post']  = $quizle;

                    $media_html .= '<div class="quizle-question__video quizle-question__video--embed js-quizle-video-embed">';
                    $media_html .= $wp_embed->shortcode( [
                        'width'  => $video_width,
                        'height' => $video_height,
                    ], $media['url'] );
                    $media_html .= '</div>';

                    unset( $GLOBALS['post'] );
                    if ( $orig_global_post ) {
                        $GLOBALS['post'] = $orig_global_post;
                    }
                }
            }

            $html .= '<div class="quizle-question__media quizle-question__media--position-' . $media['position'] . '">';
            $html .= $media_html;
            $html .= '</div>';
        }

        $description = apply_filters( 'the_content', $question['description'] );

        $html .= '<div class="quizle-question__body">';

        $html .= '<div class="quizle-question__header">';
        $html .= '  <div class="quizle-question__title">' . $question['title'] . '</div>';
        $html .= '  <div class="quizle-question__description">' . $description . '</div>';
        $html .= '</div>';

        $answers_classes = [];
        if ( ! empty( $question['type'] ) ) {
            $answers_classes[] = 'quizle-answers--' . $question['type'];
        }
        if ( ! empty( $question['columns'] ) ) {
            $answers_classes[] = 'quizle-answers--columns-' . $question['columns'];
        }

        $html .= '<div class="quizle-answers js-quizle-answers ' . implode( ' ', $answers_classes ) . '">';

        $render_callback = '';

        switch ( $question['type'] ) {
            case 'text' :
                $render_callback = [ $this, '_display_answer_text' ];
                break;
            case 'textarea' :
                $render_callback = [ $this, '_display_answer_textarea' ];
                break;
            case 'check' :
                $render_callback = [ $this, '_display_answer_check' ];
                break;
            case 'image_horizontal':
            case 'image_vertical' :
                $render_callback = [ $this, '_display_answer_image' ];
                break;
            case 'file':
                $render_callback = [ $this, '_display_file_upload' ];
                break;
            default :
                break;
        }

        if ( $render_callback ) {
            $html .= call_user_func( $render_callback, $question, $atts );
        } else {
            $html .= '<!-- render callback not found -->';
        }

        $html .= '</div>';

        if ( $output_description ) {
            $html .= '<div class="quizle-answer-description js-quizle-answer-description" style="display: none">';
            $html .= $this->encryption->encrypt_text( $question['right_answer_description'] );
            $html .= '</div>';
        }

        $html .= '</div><!--.quizle-question__body-->';

        $html .= '</div>';

        $this->fist_question_rendered = true;

        return $html;
    }

    /**
     * @return string
     */
    public function _display_file_upload( $question, $atts ) {
        $attributes = [
            'id'     => uniqid( 'quizle_file.' ),
            'class'  => 'js-quizle-file-upload-input',
            'accept' => $this->settings->get_value( 'file_upload.accept' ),
            'style'  => 'opacity:0',
        ];

        if ( $this->settings->get_value( 'file_upload.limit' ) > 1 ) {
            $attributes[] = 'multiple';
        }

        $html = '';
        $html .= '<div class="quizle-answer js-quizle-answer quizle-answer--file">';
        $html .= '<form method="post" enctype="multipart/form-data" class>';

        $html .= '<div class="quizle-file-upload-container">';
        $html .= '<input type="file" ' . build_attributes( $attributes ) . '>';
        $html .= '<label class="quizle-file-upload-label js-quizle-file-upload-label" for="' . $attributes['id'] . '" data-toggle_text="' . __( 'Done', 'quizle' ) . '">' . __( 'Select File', 'quizle' ) . '</label>';

        $info = [];
        if ( $this->settings->get_value( 'file_upload.limit' ) > 1 ) {
            $info[] = sprintf( _n( 'You can choose max %d file', 'You can choose max %d files', $this->settings->get_value( 'file_upload.limit' ), 'quizle' ), $this->settings->get_value( 'file_upload.limit' ) );
        }
        $info[] = sprintf( __( 'Max file size is %s', 'quizle' ), human_filesize( get_file_upload_max_size() ) );
        $html   .= '<span class="quizle-file-upload-container__info js-quizle-file-upload-info">' . implode( '. ', $info ) . '</span>';

        $html .= '<span class="quizle-file-upload-container__rolling js-quizle-file-upload-rolling" style="display: none">' . __( 'uploading', 'quizle' ) . '...</span>';
        $html .= '</div>';

        $html .= '<div class="quizle-file-upload-messages js-quizle-file-upload-messages"></div>';

        $html .= '<div class="quizle-file-upload-preview">';
        $html .= '</div><!--.quizle-file-upload-preview-->';

        //$html .= '<button class="quizle-button js-quizle-file-upload" disabled>' . __( 'Upload', 'quizle' ) . '</button>';
        $html .= '</form>';

        $html .= '</div>';

        $this->has_file_upload_question = true;

        return $html;
    }

    /**
     * @return string
     */
    public function _display_answer_text() {
        $html = '';

        $html .= '<div class="quizle-answer js-quizle-answer quizle-answer--text">';
        $html .= '<input type="text" class="quizle-text">';
        $html .= '</div>';

        return $html;
    }

    /**
     * @return string
     */
    public function _display_answer_textarea() {
        $html = '';

        $html .= '<div class="quizle-answer js-quizle-answer quizle-answer--textarea">';
        $html .= '<textarea class="quizle-text" rows="5"></textarea>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array $question
     * @param array $atts shortcode attributes
     *
     * @return string
     */
    public function _display_answer_check( $question, $atts ) {
        $html = '';
        $name = uniqid();

        $classes = [ 'quizle-answer js-quizle-answer quizle-answer--check' ];
        if ( $question['is_multiple'] ) {
            $classes[] = 'multiple';
        }

        foreach ( $question['answers'] as $answer ) {
            $_classes    = $classes;
            $answer_type = $answer['type'] ?? 'general';

            if ( 'custom' !== $answer_type && empty( $answer['name'] ) ) {
                continue;
            }

            if ( 'custom' === $answer_type ) {
                $_classes[] = 'quizle-answer--custom';
            }

            $attributes = build_attributes( [
                'class'         => implode( ' ', $_classes ),
                'data-identity' => $answer['answer_id'],
            ] );

            $answer['description'] = apply_filters( 'quizle/answer/description', $answer['description'] ?? '', $atts, $question, $answer );

            $html .= "<div {$attributes}>";
            $html .= '<label>';
            if ( ! $question['is_multiple'] ) {
                $html .= '<input type="radio" name="' . $name . '" value="" class="quizle-visually-hidden">';
            } else {
                $html .= '<input type="checkbox" name="' . $name . '" value="" class="quizle-visually-hidden">';
            }
            $html .= '<span class="quizle-answer__check"></span>';

            $html .= '<span class="quizle-answer__body">';
            if ( 'custom' === $answer_type ) {
                $html .= '<input type="text" name="' . $name . '"  placeholder="' . __( 'custom answer', QUIZLE_TEXTDOMAIN ) . '" value="" class="quizle-text quizle-answer__custom-text js-quizle-custom-answer-input" data-is_custom_answer="1">';
            } else {
                $html .= '<span class="quizle-answer__text">' . $answer['name'] . '</span>';
            }
            if ( ! empty( $answer['description'] ) ) {
                $html .= '    <span class="quizle-answer__description">' . $answer['description'] . '</span>';
            }
            $html .= '</span>';

            $html .= '</label>';
            $html .= '</div>';
        }

        return $html;

    }

    /**
     * @param array $question
     *
     * @return string
     */
    public function _display_answer_image( $question ) {
        $html = '';
        $name = uniqid();

        $classes = [ 'quizle-answer', 'js-quizle-answer' ];
        if ( $question['is_multiple'] ) {
            $classes[] = 'multiple';
        }

        if ( $question['type'] == 'image_vertical' ) {
            $classes[] = 'quizle-answer--image-vertical';
        } elseif ( $question['type'] == 'image_horizontal' ) {
            $classes[] = 'quizle-answer--image-horizontal';
        }


        foreach ( $question['answers'] as $answer ) {
            $answer_type = $answer['type'] ?? 'general';

            if ( 'custom' !== $answer_type && empty( $answer['name'] ) && empty( $answer['image'] ) ) {
                continue;
            }

            $attributes = build_attributes( [
                'class'         => implode( ' ', $classes ),
                'data-identity' => $answer['answer_id'],
            ] );

            $html .= "<div $attributes>";
            $html .= '  <label>';

            if ( 'custom' === $answer_type ) {
                $html .= '<input type="text" name="' . $name . '"  placeholder="' . __( 'custom answer', QUIZLE_TEXTDOMAIN ) . '" value="" class="quizle-text quizle-answer__custom-text js-quizle-custom-answer-input" data-is_custom_answer="1">';
            } else {
                if ( ! $question['is_multiple'] ) {
                    $html .= '<input type="radio" name="' . $name . '" value="" class="quizle-visually-hidden">';
                } else {
                    $html .= '<input type="checkbox" name="' . $name . '" value="" class="quizle-visually-hidden">';
                }
            }
            $html .= '    <span class="quizle-answer__check"></span>';
            if ( ! empty( $answer['image'] ) ) {
                $html .= '    <span class="quizle-answer__image"><img src="' . $answer['image'] . '" alt=""></span>';
            }
            if ( ! empty( $answer['name'] ) ) {
                $html .= '    <span class="quizle-answer__text">' . $answer['name'] . '</span>';
            }
            $html .= '  </label>';
            $html .= '</div>';
        }

        return $html;
    }
}
