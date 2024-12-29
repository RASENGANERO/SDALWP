<?php

namespace Wpshop\SettingApi\OptionField;

interface FieldInterface {
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string|null
	 */
	public function getLabel();

	/**
	 * @return bool
	 */
	public function isEnabled();

	/**
	 * @param string      $id
	 * @param string|null $name
	 * @param string|null $value
	 *
	 * @return void
	 */
	public function render( $id, $name = null, $value = null );
}
