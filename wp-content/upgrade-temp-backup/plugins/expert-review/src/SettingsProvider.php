<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Settings\CustomStyleOptions;
use Wpshop\ExpertReview\Settings\QaOptions;
use Wpshop\ExpertReview\Settings\LikeOptions;
use Wpshop\ExpertReview\Settings\ExpertOptions;
use Wpshop\ExpertReview\Settings\PluginOptions;
use Wpshop\SettingApi\OptionField\Checkbox;
use Wpshop\SettingApi\OptionField\Color;
use Wpshop\SettingApi\OptionField\Editor;
use Wpshop\SettingApi\OptionField\RawHtml;
use Wpshop\SettingApi\OptionField\Select;
use Wpshop\SettingApi\OptionField\Text;
use Wpshop\SettingApi\OptionField\Textarea;
use Wpshop\SettingApi\Section\Section;
use Wpshop\SettingApi\SettingsProviderInterface;
use Wpshop\SettingApi\SettingsPage\TabSettingsPage;

class SettingsProvider implements SettingsProviderInterface {

    use TemplateRendererTrait;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var PluginOptions
     */
    protected $baseOptions;

    /**
     * @var LikeOptions
     */
    protected $like_options;

    /**
     * @var AdvancedOptions
     */
    protected $advanced_options;

    /**
     * @var QaOptions
     */
    protected $qa_options;

    /**
     * @var CustomStyleOptions
     */
    protected $style_options;

    /**
     * SettingsProvider constructor.
     *
     * @param Plugin             $plugin
     * @param PluginOptions      $baseOptions
     * @param LikeOptions        $like_options
     * @param AdvancedOptions    $advanced_options
     * @param QaOptions          $faq_options
     * @param CustomStyleOptions $style_options
     */
    public function __construct(
        Plugin $plugin,
        PluginOptions $baseOptions,
        LikeOptions $like_options,
        AdvancedOptions $advanced_options,
        QaOptions $faq_options,
        CustomStyleOptions $style_options
    ) {
        $this->plugin           = $plugin;
        $this->baseOptions      = $baseOptions;
        $this->like_options     = $like_options;
        $this->advanced_options = $advanced_options;
        $this->qa_options       = $faq_options;
        $this->style_options    = $style_options;
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSubmenu() {

        $baseOptions = $this->baseOptions;

        $submenu = new TabSettingsPage(
            __( 'Settings', Plugin::TEXT_DOMAIN ),
            __( 'Settings', Plugin::TEXT_DOMAIN ),
            'manage_options',
            'expert-review-settings'
        );

        $submenu->setParentSlug( AdminMenu::MAIN_SLUG );

        $submenu->addSection( $section = new Section(
            $baseOptions->getSection(),
            __( 'Main', Plugin::TEXT_DOMAIN ),
            PluginOptions::class
        ) );

        $section->addField( $field = new Text( 'license' ) );
        $field
            ->setLabel( __( 'License', Plugin::TEXT_DOMAIN ) )
            ->setPlaceholder( $baseOptions->license ? '*****' : __( 'Enter license key', Plugin::TEXT_DOMAIN ) )
            ->setValue( $baseOptions->show_license_key ? null : '' )
            ->setSanitizeCallback( function ( $value ) use ( $baseOptions ) {
                if ( ! $value && ! $baseOptions->show_license_key ) {
                    $value = $baseOptions->license;
                }
                $value = trim( $value );
                if ( current_user_can( 'administrator' ) && $value ) {
                    $this->plugin->activate( $value );
                }

                return null;
            } )
        ;

        if ( apply_filters( 'expert_review_settings:show_license_key', true ) ) {
            $section->addField( $field = new Checkbox( 'show_license_key' ) );
            $field
                ->setLabel( __( 'Show License', Plugin::TEXT_DOMAIN ) )
                ->setDescription( __( 'Show license key in input', Plugin::TEXT_DOMAIN ) )
                ->setEnabled( current_user_can( 'administrator' ) )
            ;
        }

        if ( ! $this->plugin->verify() ) {
            return $submenu;
        }

        $expertOptions = new ExpertOptions();
        $submenu->addSection( $section = new Section(
            $expertOptions->getSection(),
            __( 'Experts', Plugin::TEXT_DOMAIN ),
            $expertOptions
        ) );

        $section->addField( $field = new RawHtml( 'experts' ) );
        $field
            ->setRenderCallback( function ( $id, $name, $value ) {
                $name    = $name ?: $id;
                $experts = $value ? json_decode( $value, true ) : [];
                echo $this->render( 'experts', [
                    'name'    => $name,
                    'value'   => $value,
                    'experts' => $experts,
                ] );
            } )
        ;

        $section->addField( $field = new Checkbox( 'use_user_expert_links' ) );
        $field
            ->setLabel( _x( 'Use Users Links', 'experts_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( _x( 'link of user profiles will be used if users is used as expert', 'experts_options', Plugin::TEXT_DOMAIN ) )
        ;

        /**
         * Likes
         */
        $like_options = $this->like_options;
        $submenu->addSection( $section = new Section(
            $like_options->getSection(),
            _x( 'Likes', 'like_options', Plugin::TEXT_DOMAIN ),
            $like_options
        ) );

        $section->addField( $field = new Checkbox( 'likes_before_content' ) );
        $field
            ->setLabel( _x( 'Likes before post content', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( _x( 'add [expert_review_likes] before content', 'like_options', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Checkbox( 'likes_after_content' ) );
        $field
            ->setLabel( _x( 'Likes after post content', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( _x( 'add [expert_review_likes] after content', 'like_options', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Text( 'exclude_post_ids' ) );
        $field
            ->setLabel( _x( 'Exclude posts', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'comma separated values', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Textarea( 'exclude_post_categories' ) );
        $field
            ->setLabel( _x( 'Exclude post categories', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'comma or new line separated category names', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Editor( 'likes_content' ) );
        $field
            ->setLabel( _x( 'Like Content', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setEditorOptions( [ 'teeny' => false ] )
        ;

        $section->addField( $field = new Checkbox( 'likes_for_comment' ) );
        $field
            ->setLabel( _x( 'Likes for Comment', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( _x( 'add likes after comment', 'like_options', Plugin::TEXT_DOMAIN ) )
        ;

        $section->addField( $field = new Editor( 'comment_likes_content' ) );
        $field
            ->setLabel( _x( 'Like Content for Comment', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setEditorOptions( [ 'teeny' => false ] )
        ;

        $section->addField( $field = new Select( 'microdata_type' ) );
        $field
            ->setLabel( _x( 'Add Likes to MicroData', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setOptions( [
                ''       => _x( 'None', 'like_options', Plugin::TEXT_DOMAIN ),
                'schema' => _x( 'schema.org', 'like_options', Plugin::TEXT_DOMAIN ),
                //'json_ld' => _x( 'json+ld', 'like_options', Plugin::TEXT_DOMAIN ),
            ] )
        ;

        $section->addField( $field = new Checkbox( 'microdata_likes' ) );
        $field
            ->setLabel( _x( 'Likes MicroData', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( _x( 'add likes to microdata', 'like_options', Plugin::TEXT_DOMAIN ) )
        ;

        $section->addField( $field = new Checkbox( 'microdata_dislikes' ) );
        $field
            ->setLabel( _x( 'Dislikes MicroData', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setDescription( _x( 'add dislikes to microdata', 'like_options', Plugin::TEXT_DOMAIN ) )
        ;

        /**
         * QA
         */
        $submenu->addSection( $section = new Section(
            $this->qa_options->getSection(),
            _x( 'HTML Tags', 'settings', Plugin::TEXT_DOMAIN ),
            $this->qa_options
        ) );

        $tag_options = apply_filters( 'expert_review:settings_qa_tag_options', [
            'div' => 'div',
            'h2'  => 'h2',
            'h3'  => 'h3',
        ] );

        $section->addField( $field = new Select( 'qa_question_tag' ) );
        $field
            ->setLabel( __( 'Question Tag to Wrap (Q&A)', Plugin::TEXT_DOMAIN ) )
            ->setOptions( $tag_options )
            ->setDescription( __( 'The tag used for wrapping questions in question and answers', Plugin::TEXT_DOMAIN ) )
        ;
//        $section->addField( $field = new Select( 'qa_answer_tag' ) );
//        $field
//            ->setLabel( __( 'Answer Tag to Wrap', Plugin::TEXT_DOMAIN ) )
//            ->setOptions( $tag_options )
//            ->setDescription( __( 'The tag used for wrapping answers', Plugin::TEXT_DOMAIN ) )
//        ;
        $section->addField( $field = new Select( 'faq_question_tag' ) );
        $field
            ->setLabel( __( 'Question Tag to Wrap (FAQ)', Plugin::TEXT_DOMAIN ) )
            ->setOptions( $tag_options )
            ->setDescription( __( 'The tag used for wrapping questions in FAQ', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Select( 'pluses_header_tag' ) );
        $field
            ->setLabel( __( 'Header Tag to Wrap (Pluses and Minuses)', Plugin::TEXT_DOMAIN ) )
            ->setOptions( $tag_options )
            ->setDescription( __( 'The tag used for wrapping Plus & Minus', Plugin::TEXT_DOMAIN ) )
        ;

        /**
         * Advanced
         */
        $submenu->addSection( $section = new Section(
            $this->advanced_options->getSection(),
            _x( 'Advanced', 'settings', Plugin::TEXT_DOMAIN ),
            $this->advanced_options
        ) );

        $section->addField( $field = new Checkbox( 'use_json_ld_faq_microdata' ) );
        $field
            ->setLabel( __( 'FAQPage JSON+LD ', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'Output microdata of faq page in json+ld', Plugin::TEXT_DOMAIN ) )
            ->setEnabled( current_user_can( 'administrator' ) )
        ;

        $section->addField( $field = new Select( 'expert_microdata_type' ) );
        $field
            ->setLabel( _x( 'Expert Block MicroData', 'like_options', Plugin::TEXT_DOMAIN ) )
            ->setOptions( [
                ''       => _x( 'None', 'like_options', Plugin::TEXT_DOMAIN ),
                'schema' => _x( 'schema.org', 'like_options', Plugin::TEXT_DOMAIN ),
                //'json_ld' => _x( 'json+ld', 'like_options', Plugin::TEXT_DOMAIN ),
            ] )
        ;

        $section->addField( $field = new Textarea( 'comment_to_scroll_selector' ) );
        $field
            ->setLabel( __( 'Comment Selector', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'where to scroll when click on button Ask Question', Plugin::TEXT_DOMAIN ) )
        ;

        $section->addField( $field = new Checkbox( 'ask_question_link_new_tab' ) );
        $field
            ->setLabel( __( 'Expert in New Tab', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'always open new tab on Ask Question button click if link type used', Plugin::TEXT_DOMAIN ) )
        ;

        $section->addField( $field = new Text( 'email_to' ) );
        $field
            ->setLabel( __( 'Email To', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'question will be sent to the email', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Textarea( 'email_cc' ) );
        $field
            ->setLabel( __( 'Email Copy To (CC)', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'New line separated values. Example John Doe &lt;john@example.com&gt; or john@example.com', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Textarea( 'email_bcc' ) );
        $field
            ->setLabel( __( 'Email Copy To (BCC)', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'New line separated values. Example John Doe &lt;john@example.com&gt; or john@example.com', Plugin::TEXT_DOMAIN ) )
        ;
        $section->addField( $field = new Select( 'email_to_expert' ) );
        $field
            ->setLabel( __( 'Email Copy To Expert', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'Send copy of email to expert if has it', Plugin::TEXT_DOMAIN ) )
            ->setOptions( [
                ''    => __( 'Don\'t send', Plugin::TEXT_DOMAIN ),
                'cc'  => __( 'CC', Plugin::TEXT_DOMAIN ),
                'bcc' => __( 'BCC', Plugin::TEXT_DOMAIN ),
            ] )
        ;

        $section->addField( $field = new Checkbox( 'enable_consent_checkbox' ) );
        $field
            ->setLabel( __( 'Enable Consent Checkbox', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'consent to the processing of personal data', Plugin::TEXT_DOMAIN ) )
        ;

        $section->addField( $field = new Checkbox( 'enable_templates' ) );
        $field
            ->setLabel( __( 'Enable Templates', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'render content using templates (this will disable setting from HTML section)', Plugin::TEXT_DOMAIN ) )
        ;

        $submenu->addSection( $section = new Section(
            $this->style_options->getSection(),
            _x( 'Custom Style', 'settings', Plugin::TEXT_DOMAIN ),
            $this->style_options
        ) );

        $section->addField( $field = new Checkbox( 'enabled' ) );
        $field
            ->setLabel( __( 'Enable Custom Style', Plugin::TEXT_DOMAIN ) )
            ->setDescription( __( 'enable the feature and add styles to html document', Plugin::TEXT_DOMAIN ) )
        ;

        $section->addField( $field = new Color( 'background_color' ) );
        $field->setLabel( __( 'Background', Plugin::TEXT_DOMAIN ) );
        $section->addField( $field = new Color( 'primary_color' ) );
        $field->setLabel( __( 'Primary Color', Plugin::TEXT_DOMAIN ) );
        $section->addField( $field = new Color( 'gradient_color' ) );
        $field->setLabel( __( 'Gradient Color', Plugin::TEXT_DOMAIN ) );


        return $submenu;
    }
}
