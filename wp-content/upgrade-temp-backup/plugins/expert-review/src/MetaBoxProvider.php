<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Shortcodes\Poll;
use Wpshop\MetaBox\Form\Element\ColorPicker;
use Wpshop\MetaBox\Form\Element\RawHtml;
use Wpshop\MetaBox\Form\Element\Text;
use Wpshop\MetaBox\MetaBoxContainer\SimpleMetaBoxContainer;
use Wpshop\MetaBox\MetaBoxManager;
use Wpshop\MetaBox\Provider\MetaBoxProviderInterface;
use Wpshop\MetaBox\Provider\ScriptProviderInterface;
use Wpshop\MetaBox\Provider\StyleProviderInterface;

class MetaBoxProvider implements
    MetaBoxProviderInterface,
    ScriptProviderInterface,
    StyleProviderInterface {

    use TemplateRendererTrait;

    /**
     * @var SimpleMetaBoxContainer
     */
    protected $metaBoxPrototype;

    /**
     * MetaBoxProvider constructor.
     *
     * @param SimpleMetaBoxContainer $metaBoxPrototype @deprecated
     */
    public function __construct(
        SimpleMetaBoxContainer $metaBoxPrototype
    ) {
        $this->metaBoxPrototype = $metaBoxPrototype;
    }

    /**
     * @param MetaBoxManager $manager
     *
     * @return void
     */
    public function initMetaBoxes( MetaBoxManager $manager ) {
        $manager->addMetaBox( $box = clone $this->metaBoxPrototype );
        $box
            ->setId( 'expert_review_polls' )
            ->setTitle( __( 'Poll Data', Plugin::TEXT_DOMAIN ) )
            ->setScreen( Poll::POST_TYPE )
        ;

        $box->addElement( $element = new RawHtml( 'expert_review_poll_data' ) );
        $element->setRenderCallback( function ( $post ) {
            return $this->render( 'poll-metadata', ['post' => $post] );
        } );

    }

    /**
     * @inheritDoc
     */
    public function enqueueScripts() {
        add_action( 'current_screen', function () {
            if ( \get_current_screen()->post_type !== ExpertReview::POST_TYPE ) {
                return;
            }
            $this->_enqueueScripts();
        } );
    }

    protected function _enqueueScripts() {
        add_action( 'admin_enqueue_scripts', function () {

            // color picker deps
            wp_enqueue_script( 'wp-color-picker', 'jquery' );
            $selector = ColorPicker::SELECTOR_CLASS;
            $js       = <<<"JS"
jQuery(function($) {
    \$('.{$selector}').wpColorPicker({
	    change: function (event, ui) {
	        jQuery(event.target).trigger('color-picker:change');
	    },
	    clear: function (event) {
	        jQuery(event.target).parent().find('.wp-color-picker').trigger('color-picker:clear');
    	}
    });
});
JS;
            wp_add_inline_script( 'wp-color-picker', $js );

            // media file deps
            wp_enqueue_media();

            $browseSelector = '.js-wpshop-form-element-browse';
            $urlSelector    = '.js-wpshop-form-element-url';
            $js             = <<<"JS"
jQuery(function($) {
	\$('{$browseSelector}').on('click', function (event) {
	    event.preventDefault();

	    var self = $(this);

	    var fileFrame = wp.media.frames.file_frame = wp.media({
	        title: self.data('uploader_title'),
	        button: {
	            text: self.data('uploader_button_text'),
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
//			FormMediaFile::registerInlineScript();
        } );
    }

    public function enqueueStyles() {
        add_action( 'current_screen', function () {
            if ( \get_current_screen()->post_type !== ExpertReview::POST_TYPE ) {
                return;
            }
            $this->_enqueueStyles();
        } );
    }

    protected function _enqueueStyles() {
        // color picker deps
        wp_enqueue_style( 'wp-color-picker' );
    }
}
