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
        if ( ! apply_filters( 'quizle/shortcode/do_output', true, $shortcode, $atts ) ) {
            return '';
        }

        if ( empty( $atts['name'] ) || ! ( $quizle = get_quizle( $atts['name'] ) ) ) {
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

        $html    = '';
        $options = [
            'type'                        => $quiz_type,
            'view_type'                   => $quiz_view_type,
            'animation_duration'          => 200,
            'progress_animation_duration' => $this->settings->get_value( 'progress_animation_duration' ),
            'contacts_order'              => get_post_meta( $quizle->ID, 'result-enabled', true )
                ? ( get_post_meta( $quizle->ID, 'contact-enabled', true )
                    ? get_post_meta( $quizle->ID, 'contact-form-order', true )
                    : null )
                : 'only_contacts',
            'save_contacts_and_results'   => ! is_preview() && get_post_meta( $quizle->ID, 'save-quizle-contacts-and-results', true ),
        ];
        if ( $quiz_type === Quizle::TYPE_TEST ) {
            $options['show_results'] = (bool) get_post_meta( $quizle->ID, 'test-instant-answer', true );
        }

        $html .= '<div class="quizle-container">';
        $html .= '<style>' . $styles . '</style>';
        if ( is_debug() ) {
            $html .= sprintf( '<a href="%s" target="_blank">edit quizle</a>', get_edit_post_link( $quizle ) );
        }

        if ( is_wp_error( $context = Context::createFromWpQuery() ) ) {
            $context = [];
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

        $html .= '  <div class="quizle-questions js-quizle-questions">';
        $html .= $question_html;
        $html .= '  </div><!--.quizle-questions-->';

        $contacts_html = $this->output_contacts( $quizle->ID, $atts );

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

            $html .= '        <button class="quizle-button js-quizle-next-step" data-direction="next" data-finish_text="' . esc_attr( $finish_btn_text ) . '"' . $disabled . '>' . $next_btn_text . '</button>';
            $html .= '        <div class="quizle-footer__step-description js-quizle-next-step-description" data-shift="' . __( 'Enter', QUIZLE_TEXTDOMAIN ) . ' shift+↵">' . __( 'Enter', QUIZLE_TEXTDOMAIN ) . ' ↵</div>';
            $html .= '      </div>';

            $html .= '    </div>';
            $html .= '  </div>';
        } else {
            $html .= '  <div class="quizle-footer js-quizle-footer">';
            $html .= '    <button class="quizle-button js-quizle-list-submit">' . __( 'Submit Answers', QUIZLE_TEXTDOMAIN ) . '</button>';
            $html .= '  </div>';
        }

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
     * @param int $quizle_id
     *
     * @return string
     */
    protected function output_result( $quizle_id ) {
        $result_enabled = get_post_meta( $quizle_id, 'result-enabled', true );
        $quizle_results = get_post_meta( $quizle_id, 'quizle-results', true );

        if ( ! empty( $quizle_results ) ) {
            $quizle_results = json_decode( $quizle_results, true );
        }

        if ( ! $result_enabled || empty( $quizle_results ) ) {
            return '';
        }

        $progress_bar_text = apply_filters( 'quizle/progress_bar/text', __( 'Preparing Results...', QUIZLE_TEXTDOMAIN ) );
        $progress          = '<div class="quizle-progress-bar-container"><div class="quizle-progress-bar"><div class="quizle-progress-bar__inner js-result-progress-bar" style="width: 0"></div></div><div class="quizle-progress-bar__text">' . $progress_bar_text . '</div></div>';

        return '<div class="quizle-results js-quizle-results" style="display: none">' . $progress . '</div>';

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
            $html .= '<div class="quizle-contacts__field"><input type="text" name="data[contacts][phone]" required placeholder="' . esc_attr__( 'Phone', QUIZLE_TEXTDOMAIN ) . '" class="quizle-text"></div>';
        }

        foreach ( $messengers as $messenger_key => $messenger ) {
            $html .= '<div class="quizle-contacts__field"><input type="text" name="data[messengers][' . $messenger_key . ']" placeholder="' . esc_attr( $messenger['title'] ) . '" class="quizle-text"></div>';
        }

        $html .= '<div class="quizle-contacts__buttons"><button disabled class="quizle-button" type="submit" data-toggle_txt="' . esc_attr__( 'Submitting data...', QUIZLE_TEXTDOMAIN ) . '">' . $contact_btn_text . '</button></div>';

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

        $question_style = null;
        if ( $quiz_view_type == Quizle::VIEW_TYPE_SLIDES && $n != 1 ) {
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

        $html .= '<div class="quizle-question__body">';

        $html .= '<div class="quizle-question__header">';
        $html .= '  <div class="quizle-question__title">' . $question['title'] . '</div>';
        $html .= '  <div class="quizle-question__description">' . $question['description'] . '</div>';
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
