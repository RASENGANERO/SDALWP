<?php

namespace Wpshop\MetaBox\Form\Render;

use DomainException;
use Wpshop\MetaBox\Form\Element\FormElementInterface;

class FormInput extends AbstractElement {

	/**
	 * @var array
	 */
	protected $validTagAttributes = [
		'name'           => true,
		'accept'         => true,
		'alt'            => true,
		'autocomplete'   => true,
		'autofocus'      => true,
		'checked'        => true,
		'dirname'        => true,
		'disabled'       => true,
		'form'           => true,
		'formaction'     => true,
		'formenctype'    => true,
		'formmethod'     => true,
		'formnovalidate' => true,
		'formtarget'     => true,
		'height'         => true,
		'list'           => true,
		'max'            => true,
		'maxlength'      => true,
		'min'            => true,
		'multiple'       => true,
		'pattern'        => true,
		'placeholder'    => true,
		'readonly'       => true,
		'required'       => true,
		'size'           => true,
		'src'            => true,
		'step'           => true,
		'type'           => true,
		'value'          => true,
		'width'          => true,
	];

	/**
	 * Valid values for the input type
	 *
	 * @var array
	 */
	protected $validTypes = [
		'text'           => true,
		'button'         => true,
		'checkbox'       => true,
		'file'           => true,
		'hidden'         => true,
		'image'          => true,
		'password'       => true,
		'radio'          => true,
		'reset'          => true,
		'select'         => true,
		'submit'         => true,
		'color'          => true,
		'date'           => true,
		'datetime'       => true,
		'datetime-local' => true,
		'email'          => true,
		'month'          => true,
		'number'         => true,
		'range'          => true,
		'search'         => true,
		'tel'            => true,
		'time'           => true,
		'url'            => true,
		'week'           => true,
	];

	/**
	 * Render a form <input> element from the provided $element
	 *
	 * @param FormElementInterface $element
	 *
	 * @return string
	 * @throws DomainException
	 */
	public function render( FormElementInterface $element ) {
		$name = $element->getName();
		if ( $name === null || $name === '' ) {
			throw new DomainException( sprintf(
				'%s requires that the element has an assigned name; none discovered',
				__METHOD__
			) );
		}

		$attributes          = $element->getAttributes();
		$attributes['name']  = $name;
		$type                = $this->getType( $element );
		$attributes['type']  = $type;
		$attributes['value'] = $element->getValue();
		if ( 'password' == $type ) {
			$attributes['value'] = '';
		}

		return sprintf(
			'<input %s>',
			$this->createAttributesString( $attributes )
		);
	}

	/**
	 * Determine input type to use
	 *
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	protected function getType( FormElementInterface $element ) {
		$type = $element->getAttribute( 'type' );
		if ( empty( $type ) ) {
			return 'text';
		}

		$type = strtolower( $type );
		if ( ! isset( $this->validTypes[ $type ] ) ) {
			return 'text';
		}

		return $type;
	}
}
