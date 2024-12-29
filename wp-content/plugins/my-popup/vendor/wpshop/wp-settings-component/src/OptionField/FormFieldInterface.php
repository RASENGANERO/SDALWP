<?php

namespace Wpshop\SettingApi\OptionField;

interface FormFieldInterface extends FieldInterface {

	/**
	 * @return string|null
	 */
	public function getDescription();

	/**
	 * @return string|null
	 */
	public function getDefault();

	/**
	 * @return callable|null
	 */
	public function getSanitizeCallback();
}
