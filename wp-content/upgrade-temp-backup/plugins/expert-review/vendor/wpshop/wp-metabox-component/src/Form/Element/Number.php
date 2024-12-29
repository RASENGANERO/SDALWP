<?php

namespace Wpshop\MetaBox\Form\Element;

class Number extends FormElement {

	/**
	 * @var array
	 */
	protected $attributes = [
		'type' => 'number',
	];

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setMin( $value ) {
		return $this->setAttribute( 'min', $value );
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setMax( $value ) {
		return $this->setAttribute( 'max', $value );
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setStep( $value ) {
		return $this->setAttribute('step', $value);
	}
}
