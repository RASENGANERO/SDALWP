<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\Checkbox;
use Wpshop\MetaBox\Form\Element\FormElementInterface;

class FormCheckbox extends FormInput {

	/**
	 * @inheritDoc
	 */
	public function render( FormElementInterface $element ) {
		if ( ! $element instanceof Checkbox ) {
			throw new \InvalidArgumentException( sprintf(
				'%s requires that the element is of type %s',
				Checkbox::class,
				__METHOD__
			) );
		}

		$name = $element->getName();
		if ( empty( $name ) && $name !== 0 ) {
			throw new \DomainException( sprintf(
				'%s requires that the element has an assigned name; none discovered',
				__METHOD__
			) );
		}

		$attributes          = $element->getAttributes();
		$attributes['name']  = $name;
		$attributes['type']  = $this->getInputType();
		$attributes['value'] = $element->getCheckedValue();

		if ( $element->isChecked() ) {
			$attributes['checked'] = 'checked';
		}

		$rendered = sprintf(
			'<input %s>',
			$this->createAttributesString( $attributes )
		);

		if ( $element->useHiddenElement() ) {
			$hiddenAttributes = [
				'disabled' => isset( $attributes['disabled'] ) ? $attributes['disabled'] : false,
				'name'     => $attributes['name'],
				'value'    => $element->getUncheckedValue(),
			];

			$rendered = sprintf(
				            '<input type="hidden" %s>',
				            $this->createAttributesString( $hiddenAttributes )
			            ) . $rendered;
		}

		return $rendered;
	}

	/**
	 * Return input type
	 *
	 * @return string
	 */
	protected function getInputType() {
		return 'checkbox';
	}
}
