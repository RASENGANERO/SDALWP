<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\Button;
use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\MediaFile;

class FormMediaFile {

	/**
	 * @var array
	 */
	protected static $inlineRegistry = [];

	/**
	 * @var FormButton
	 */
	protected $buttonRenderer;

	/**
	 * @var FormText
	 */
	protected $textRenderer;

	/**
	 * FormMediaFile constructor.
	 *
	 * @param FormButton $buttonRenderer
	 * @param FormText   $textRenderer
	 */
	public function __construct( FormButton $buttonRenderer, FormText $textRenderer ) {
		$this->buttonRenderer = $buttonRenderer;
		$this->textRenderer   = $textRenderer;
	}

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	public function render( FormElementInterface $element ) {

		if ( ! $element instanceof MediaFile ) {
			throw new \InvalidArgumentException( sprintf(
				'%s requires that the element is of type %s',
				MediaFile::class,
				__METHOD__
			) );
		}

		ElementRenderer::appendCssClass( $element, 'js-wpshop-form-element-url' );
		$input = $this->textRenderer->render( $element );

		$button = new Button();
		$button->setName( 0 );
		$button->setValue( $element->getValue() );
		$button->setAttribute('class', 'button');
		ElementRenderer::appendCssClass( $button, 'js-wpshop-form-element-browse' );

		return $input . $this->buttonRenderer->render( $button, $element->getButtonContent() );
	}

	/**
	 * @param string $browseSelectorClass
	 * @param string $urlSelectorClass
	 */
	public static function registerInlineScript( $browseSelectorClass = 'js-wpshop-form-element-browse', $urlSelectorClass = 'js-wpshop-form-element-url' ) {
		if ( array_key_exists( $browseSelectorClass . $urlSelectorClass, static::$inlineRegistry ) ) {
			return;
		}

		static::$inlineRegistry[ $browseSelectorClass . $urlSelectorClass ] = true;

		$script = static::js( ".$browseSelectorClass", ".$urlSelectorClass" );
		wp_add_inline_script( 'jquery', $script );
	}

	/**
	 * @param string $browseSelector
	 * @param string $urlSelector
	 *
	 * @return string
	 */
	protected static function js( $browseSelector, $urlSelector ) {
		return <<<"JS"
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
			    });
			
			    fileFrame.open();
			});
		});
JS;
	}
}
