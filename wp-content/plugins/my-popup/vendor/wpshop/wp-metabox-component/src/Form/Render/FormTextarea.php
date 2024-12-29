<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\FormElementInterface;

class FormTextarea extends AbstractElement {

	/**
	 * @var array
	 */
	protected $validTagAttributes = [
		'autocomplete' => true,
		'autofocus'    => true,
		'cols'         => true,
		'dirname'      => true,
		'disabled'     => true,
		'form'         => true,
		'maxlength'    => true,
		'name'         => true,
		'placeholder'  => true,
		'readonly'     => true,
		'required'     => true,
		'rows'         => true,
		'wrap'         => true,
	];

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	public function render( FormElementInterface $element ) {
		$name = $element->getName();
		if ( empty( $name ) && $name !== 0 ) {
			throw new \DomainException( sprintf(
				'%s requires that the element has an assigned name; none discovered',
				__METHOD__
			) );
		}

		$attributes         = $element->getAttributes();
		$attributes['name'] = $name;
		$content            = (string) $element->getValue();

		return sprintf(
			'<textarea %s>%s</textarea>',
			$this->createAttributesString( $attributes ),
			$content
//			esc_textarea( $content )
		);
	}
}
