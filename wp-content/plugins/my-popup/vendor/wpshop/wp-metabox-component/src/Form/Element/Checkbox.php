<?php

namespace Wpshop\MetaBox\Form\Element;

class Checkbox extends FormElement {

	/**
	 * @var array
	 */
	protected $attributes = [
		'type' => 'checkbox',
	];

	/**
	 * @var bool
	 */
	protected $useHiddenElement = true;

	/**
	 * @var string
	 */
	protected $uncheckedValue = '0';

	/**
	 * @var string
	 */
	protected $checkedValue = '1';

	/**
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setOptions( $options ) {
		parent::setOptions( $options );

		if ( isset( $options['use_hidden_element'] ) ) {
			$this->setUseHiddenElement( $options['use_hidden_element'] );
		}

		if ( isset( $options['unchecked_value'] ) ) {
			$this->setUncheckedValue( $options['unchecked_value'] );
		}

		if ( isset( $options['checked_value'] ) ) {
			$this->setCheckedValue( $options['checked_value'] );
		}

		return $this;
	}

	/**
	 * Do we render hidden element?
	 *
	 * @param bool $useHiddenElement
	 *
	 * @return Checkbox
	 */
	public function setUseHiddenElement( $useHiddenElement ) {
		$this->useHiddenElement = (bool) $useHiddenElement;

		return $this;
	}

	/**
	 * Do we render hidden element?
	 *
	 * @return bool
	 */
	public function useHiddenElement() {
		return $this->useHiddenElement;
	}

	/**
	 * Set the value to use when checkbox is unchecked
	 *
	 * @param $uncheckedValue
	 *
	 * @return Checkbox
	 */
	public function setUncheckedValue( $uncheckedValue ) {
		$this->uncheckedValue = $uncheckedValue;

		return $this;
	}

	/**
	 * Get the value to use when checkbox is unchecked
	 *
	 * @return string
	 */
	public function getUncheckedValue() {
		return $this->uncheckedValue;
	}

	/**
	 * Set the value to use when checkbox is checked
	 *
	 * @param $checkedValue
	 *
	 * @return Checkbox
	 */
	public function setCheckedValue( $checkedValue ) {
		$this->checkedValue = $checkedValue;

		return $this;
	}

	/**
	 * Get the value to use when checkbox is checked
	 *
	 * @return string
	 */
	public function getCheckedValue() {
		return $this->checkedValue;
	}

	/**
	 * Checks if this checkbox is checked.
	 *
	 * @return bool
	 */
	public function isChecked() {
		return $this->value === $this->getCheckedValue();
	}

	/**
	 * Checks or unchecks the checkbox.
	 *
	 * @param bool $value The flag to set.
	 *
	 * @return Checkbox
	 */
	public function setChecked( $value ) {
		$this->value = $value ? $this->getCheckedValue() : $this->getUncheckedValue();

		return $this;
	}

	/**
	 * Checks or unchecks the checkbox.
	 *
	 * @param mixed $value A boolean flag or string that is checked against the "checked value".
	 *
	 * @return FormElement
	 */
	public function setValue( $value ) {
		// Cast to strings because POST data comes in string form
		$checked     = (string) $value === (string) $this->getCheckedValue();
		$this->value = $checked ? $this->getCheckedValue() : $this->getUncheckedValue();

		return $this;
	}
}
