<?php

namespace Wpshop\SettingApi\OptionField;

abstract class AbstractOption implements FormFieldInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string|null
	 */
	protected $label;

	/**
	 * @var string|null
	 */
	protected $description;

	/**
	 * @var string|null
	 */
	protected $default;

	/**
	 * @var string|null
	 */
	protected $value;

	/**
	 * @var callable|null
	 */
	protected $sanitizeCallback;

	/**
	 * @var bool
	 */
	protected $enabled = true;

	/**
	 * AbstractOption constructor.
	 *
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setName( $name ) {
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string|null $label
	 *
	 * @return $this
	 */
	public function setLabel( $label ) {
		$this->label = $label;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string|null $description
	 *
	 * @return $this
	 */
	public function setDescription( $description ) {
		$this->description = $description;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getDefault() {
		return $this->default;
	}

	/**
	 * @param string|null $default
	 *
	 * @return $this
	 */
	public function setDefault( $default ) {
		$this->default = $default;

		return $this;
	}

	/**
	 * @return callable|null
	 */
	public function getSanitizeCallback() {
		return $this->sanitizeCallback;
	}

	/**
	 * @param callable|null $sanitizeCallback
	 *
	 * @return $this
	 */
	public function setSanitizeCallback( $sanitizeCallback ) {
		$this->sanitizeCallback = $sanitizeCallback;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * @param bool $flag
	 *
	 * @return $this
	 */
	public function setEnabled( $flag ) {
		$this->enabled = (bool) $flag;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param string|null $value
	 *
	 * @return $this
	 */
	public function setValue( $value ) {
		$this->value = $value;

		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return string|null
	 */
	protected function prepareValue( $value ) {
		if ( $this->value !== null ) {
			return $this->value;
		}

		return $value;
	}
}
