<?php

namespace Wpshop\MetaBox\Form\Render;

use DomainException;
use InvalidArgumentException;
use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\Radio;

class FormRadio extends FormMultiCheckbox {

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	public function render( FormElementInterface $element ) {
		if ( ! $element instanceof Radio ) {
			throw new InvalidArgumentException( sprintf(
				'%s requires that the element is of type %s',
				Radio::class,
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
	 * @return string
	 */
	protected function getInputType() {
		return 'radio';
	}

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	protected static function getName( FormElementInterface $element ) {
		return $element->getName();
	}
}
