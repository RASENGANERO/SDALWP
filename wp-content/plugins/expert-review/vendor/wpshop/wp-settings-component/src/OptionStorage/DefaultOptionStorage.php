<?php

namespace Wpshop\SettingApi\OptionStorage;

class DefaultOptionStorage extends AbstractOptionStorage {

	/**
	 * @var string
	 */
	protected $sectionId;

	/**
	 * DefaultOptionStorage constructor.
	 *
	 * @param string $sectionId
	 */
	public function __construct( $sectionId ) {
		$this->sectionId = $sectionId;
	}

	/**
	 * @return string
	 */
	public function getSection() {
		return $this->sectionId;
	}
}
