<?php

namespace Wpshop\MetaBox\Form\Element;

class Select extends FormElement {

	/**
	 * @var array
	 */
	protected $attributes = [
		'type' => 'select',
	];

	/**
	 * @var array
	 */
	protected $valueOptions = [];

	/**
	 * @var string|null
	 */
	protected $emptyOption = null;

	/**
	 * @var bool
	 */
	protected $useHiddenElement = false;

	/**
	 * @var string
	 */
	protected $unselectedValue = '';

	/**
	 * @return array
	 */
	public function getValueOptions() {
		return $this->valueOptions;
	}

	/**
	 * @param array $valueOptions
	 *
	 * @return $this
	 */
	public function setValueOptions( array $valueOptions ) {
		$this->valueOptions = $valueOptions;

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return $this
	 */
	public function unsetValueOption( $key ) {
		if ( isset( $this->valueOptions[ $key ] ) ) {
			unset( $this->valueOptions[ $key ] );
		}

		return $this;
	}

	/**
	 * @param string $emptyOption
	 *
	 * @return $this
	 */
	public function setEmptyOption( $emptyOption ) {
		$this->emptyOption = $emptyOption;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getEmptyOption() {
		return $this->emptyOption;
	}

	/**
	 * @return bool
	 */
	public function isUseHiddenElement() {
		return $this->useHiddenElement;
	}

	/**
	 * @param bool $useHiddenElement
	 *
	 * @return $this
	 */
	public function setUseHiddenElement( $useHiddenElement ) {
		$this->useHiddenElement = $useHiddenElement;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUnselectedValue() {
		return $this->unselectedValue;
	}

	/**
	 * @param string $unselectedValue
	 *
	 * @return $this
	 */
	public function setUnselectedValue( $unselectedValue ) {
		$this->unselectedValue = $unselectedValue;

		return $this;
	}

	/**
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setOptions( $options ) {
		parent::setOptions( $options );

		if ( isset( $this->options['value_options'] ) ) {
			$this->setValueOptions( $this->options['value_options'] );
		}

		if ( isset( $this->options['options'] ) ) {
			$this->setValueOptions( $this->options['options'] );
		}

		if ( isset( $this->options['empty_option'] ) ) {
			$this->setEmptyOption( $this->options['empty_option'] );
		}

		if ( isset( $options['use_hidden_element'] ) ) {
			$this->setUseHiddenElement( $options['use_hidden_element'] );
		}

		if ( isset( $options['unselected_value'] ) ) {
			$this->setUnselectedValue( $options['unselected_value'] );
		}

		return $this;
	}
}
