<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\LabelAwareInterface;

class FormButton extends FormInput {

	/**
	 * @var array
	 */
	protected $validTagAttributes = [
		'name'           => true,
		'autofocus'      => true,
		'disabled'       => true,
		'form'           => true,
		'formaction'     => true,
		'formenctype'    => true,
		'formmethod'     => true,
		'formnovalidate' => true,
		'formtarget'     => true,
		'type'           => true,
		'value'          => true,
	];

	/**
	 * Valid values for the button type
	 *
	 * @var array
	 */
	protected $validTypes = [
		'button' => true,
		'reset'  => true,
		'submit' => true,
	];

	/**
	 * @param FormElementInterface $element
	 * @param string|null          $buttonContent
	 *
	 * @return string
	 */
	public function render( FormElementInterface $element, $buttonContent = null ) {
		$openTag = $this->openTag( $element );

		$buttonContent = $buttonContent ?: $element->getAttribute( 'title' );
		if ( $buttonContent === null && $element instanceof LabelAwareInterface ) {
			$buttonContent = $element->getLabel();
		}

		if ( null === $buttonContent ) {
			throw new \DomainException(
				sprintf(
					'%s expects either button content as the second argument, ' .
					'or that the element provided has a label value; neither found',
					__METHOD__
				)
			);
		}

		if ( ! $element instanceof LabelAwareInterface || ! $element->getLabelOption( 'disable_html_escape' ) ) {
			$buttonContent = esc_html( $buttonContent );
		}

		return $openTag . $buttonContent . $this->closeTag();
	}

	/**
	 * @param mixed $attributesOrElement
	 *
	 * @return string
	 */
	public function openTag( $attributesOrElement = null ) {
		if ( null === $attributesOrElement ) {
			return '<button>';
		}

		if ( is_array( $attributesOrElement ) ) {
			$attributes = $this->createAttributesString( $attributesOrElement );

			return sprintf( '<button %s>', $attributes );
		}

		if ( ! $attributesOrElement instanceof FormElementInterface ) {
			throw new \InvalidArgumentException( sprintf(
				'%s expects an array or %s instance; received "%s"',
				FormElementInterface::class,
				__METHOD__,
				( is_object( $attributesOrElement ) ? get_class( $attributesOrElement ) : gettype( $attributesOrElement ) )
			) );
		}

		$element = $attributesOrElement;
		$name    = $element->getName();
		if ( empty( $name ) && $name !== 0 ) {
			throw new \DomainException( sprintf(
				'%s requires that the element has an assigned name; none discovered',
				__METHOD__
			) );
		}

		$attributes          = $element->getAttributes();
		$attributes['name']  = $name;
		$attributes['type']  = $this->getType( $element );
		$attributes['value'] = $element->getValue();

		return sprintf(
			'<button %s>',
			$this->createAttributesString( $attributes )
		);
	}

	/**
	 * Return a closing button tag
	 *
	 * @return string
	 */
	public function closeTag() {
		return '</button>';
	}

	/**
	 * Determine button type to use
	 *
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	protected function getType( FormElementInterface $element ) {
		$type = $element->getAttribute( 'type' );
		if ( empty( $type ) ) {
			return 'button';
		}

		$type = strtolower( $type );
		if ( ! isset( $this->validTypes[ $type ] ) ) {
			return 'button';
		}

		return $type;
	}
}
