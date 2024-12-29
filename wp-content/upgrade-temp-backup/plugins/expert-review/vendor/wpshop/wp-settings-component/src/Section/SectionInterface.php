<?php

namespace Wpshop\SettingApi\Section;

use Wpshop\SettingApi\OptionField\FieldInterface;
use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

interface SectionInterface {

	/**
	 * @param string $name
	 *
	 * @return FieldInterface|null
	 */
	public function getFieldByName( $name );

	/**
	 * @return FieldInterface[]
	 */
	public function getFields();

	/**
	 * @return string
	 */
	public function getId();


	/**
	 * @return string
	 */
	public function getTitle();

	/**
	 * @return callable|null
	 */
	public function getRenderCallback();

	/**
	 * @return AbstractOptionStorage
	 */
	public function getOptionStorage();
}
