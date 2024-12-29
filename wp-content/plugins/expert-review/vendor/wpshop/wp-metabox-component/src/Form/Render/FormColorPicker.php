<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\ColorPicker;
use Wpshop\MetaBox\Form\Element\FormElementInterface;

class FormColorPicker extends FormText {

	/**
	 * @var array
	 */
	protected static $inlineRegistry = [];

	/**
	 * @inheritDoc
	 */
	public function render( FormElementInterface $element ) {

		if ( ! wp_script_is( 'wp-color-picker', 'enqueued' ) ) {
			throw new \RuntimeException( sprintf( 'Require enqueue wp-color-picker to use %s', get_class( $element ) ) );
		}

		ElementRenderer::appendCssClass( $element, ColorPicker::SELECTOR_CLASS );

		return parent::render( $element );
	}

	/**
	 * @param string $handle
	 * @param string $selector
	 */
	public static function registerInlineScript( $handle, $selector = ColorPicker::SELECTOR_CLASS ) {
		if ( array_key_exists( $selector, static::$inlineRegistry ) ) {
			return;
		}
		static::$inlineRegistry[ $selector ] = true;

		wp_add_inline_script( $handle, "jQuery(function($) {\$('.{$selector}').wpColorPicker();});" );
	}
}
