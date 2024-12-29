<?php

namespace Wpshop\MetaBox\Form\Element;

class Button extends FormElement {

	/**
	 * @var array
	 */
	protected $attributes = [
		'type' => 'button',
	];

	/**
	 * @param string $value
	 *
	 * @return Button
	 */
	public function setType( $value ) {
		return $this->setAttribute( 'type', $value );
	}
}
