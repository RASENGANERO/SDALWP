<?php

namespace Wpshop\SettingApi\OptionField;

class RawHtml implements FieldInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string|null
	 */
	protected $label;

	/**
	 * @var callable|null;
	 */
	protected $renderCallback;

	/**
	 * @var bool
	 */
	protected $enabled = true;

	/**
	 * RawHtml constructor.
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
	 * @param string      $id
	 * @param string|null $name
	 * @param string|null $value
	 *
	 * @return void
	 */
	public function render( $id, $name = null, $value = null ) {
		if ( $this->renderCallback ) {
			call_user_func( $this->renderCallback, $id, $name, $value );
		}
	}

	/**
	 * @param callable $renderCallback
	 *
	 * @return $this
	 */
	public function setRenderCallback( $renderCallback ) {
		$this->renderCallback = $renderCallback;

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
}
