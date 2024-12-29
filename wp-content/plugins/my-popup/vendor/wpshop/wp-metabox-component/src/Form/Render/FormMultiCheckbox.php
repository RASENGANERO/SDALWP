<?php

namespace Wpshop\MetaBox\Form\Render;

use DomainException;
use InvalidArgumentException;
use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\LabelAwareInterface;
use Wpshop\MetaBox\Form\Element\MultiCheckbox;

class FormMultiCheckbox extends FormInput {

	/**
	 * @var LabelRenderer
	 */
	protected $labelRenderer;

	/**
	 * FormMultiCheckbox constructor.
	 *
	 * @param LabelRenderer $labelRenderer
	 */
	public function __construct( LabelRenderer $labelRenderer ) {
		$this->labelRenderer = $labelRenderer;
	}

	/**
	 * @inheritDoc
	 */
	public function render( FormElementInterface $element ) {
		if ( ! $element instanceof MultiCheckbox ) {
			throw new InvalidArgumentException( sprintf(
				'%s requires that the element is of type %s',
				MultiCheckbox::class,
				__METHOD__
			) );
		}

		$name = $element->getName();
		if ( empty( $name ) && $name !== 0 ) {
			throw new DomainException( sprintf(
				'%s requires that the element has an assigned name; none discovered',
				__METHOD__
			) );
		}

		$options = $element->getValueOptions();

		$attributes         = $element->getAttributes();
		$attributes['type'] = $this->getInputType();
		$selectedOptions    = (array) $element->getValue();

		$rendered = $this->renderOptions( $element, $options, $selectedOptions, $attributes );

		return $rendered;
	}

	/**
	 * @param MultiCheckbox $element
	 * @param array         $options
	 * @param array         $selectedOptions
	 * @param array         $attributes
	 *
	 * @return string
	 */
	protected function renderOptions(
		MultiCheckbox $element,
		array $options,
		array $selectedOptions,
		array $attributes
	) {

		$escapeHtmlHelper = 'esc_html';
		$labelHelper      = $this->labelRenderer;
		$labelPosition    = $element->getLabelPosition();

		if ( $element instanceof LabelAwareInterface ) {
			$globalLabelAttributes = $element->getLabelAttributes();
		}

		$combinedMarkup = [];

		foreach ( $options as $key => $optionSpec ) {
			unset( $attributes['id'] );

			$value           = '';
			$label           = '';
			$inputAttributes = $attributes;
			$labelAttributes = $globalLabelAttributes;
			$selected        = ( isset( $inputAttributes['selected'] )
			                     && $inputAttributes['type'] != 'radio'
			                     && $inputAttributes['selected'] );
			$disabled        = ( isset( $inputAttributes['disabled'] ) && $inputAttributes['disabled'] );

			$inputAttributes['name'] = static::getName( $element );

			if ( is_scalar( $optionSpec ) ) {
				$optionSpec = [
					'label' => $optionSpec,
					'value' => $key,
				];
			}

			if ( isset( $optionSpec['value'] ) ) {
				$value = $optionSpec['value'];
			}
			if ( isset( $optionSpec['label'] ) ) {
				$label = $optionSpec['label'];
			}
			if ( isset( $optionSpec['selected'] ) ) {
				$selected = $optionSpec['selected'];
			}
			if ( isset( $optionSpec['disabled'] ) ) {
				$disabled = $optionSpec['disabled'];
			}
			if ( isset( $optionSpec['label_attributes'] ) ) {
				$labelAttributes = ( isset( $labelAttributes ) )
					? array_merge( $labelAttributes, $optionSpec['label_attributes'] )
					: $optionSpec['label_attributes'];
			}
			if ( isset( $optionSpec['attributes'] ) ) {
				$inputAttributes = array_merge( $inputAttributes, $optionSpec['attributes'] );
			}

			if ( in_array( $value, $selectedOptions ) ) {
				$selected = true;
			}

			$inputAttributes['value']    = $value;
			$inputAttributes['checked']  = $selected;
			$inputAttributes['disabled'] = $disabled;

			$input = sprintf(
				'<input %s>',
				$this->createAttributesString( $inputAttributes )
			);

			if ( ! $element instanceof LabelAwareInterface || ! $element->getLabelOption( 'disable_html_escape' ) ) {
				$label = $escapeHtmlHelper( $label );
			}

			$labelOpen = $labelHelper->openTag( $labelAttributes );
			$template  = $labelOpen . '%s%s</label>';
			switch ( $labelPosition ) {
				case MultiCheckbox::LABEL_PREPEND:
					$markup = sprintf( $template, $label, $input );
					break;
				case MultiCheckbox::LABEL_APPEND:
				default:
					$markup = sprintf( $template, $input, $label );
					break;
			}

			$combinedMarkup[] = $markup;
		}

		return implode( $element->getSeparator(), $combinedMarkup );
	}

	/**
	 * @return string
	 */
	protected function getInputType() {
		return 'checkbox';
	}

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	protected static function getName( FormElementInterface $element ) {
		return $element->getName() . '[]';
	}
}
