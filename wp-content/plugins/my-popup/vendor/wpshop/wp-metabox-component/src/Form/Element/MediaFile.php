<?php

namespace Wpshop\MetaBox\Form\Element;

class MediaFile extends Text {

	protected $buttonContent = 'File';

	/**
	 * @return mixed
	 */
	public function getButtonContent() {
		return $this->buttonContent;
	}

	/**
	 * @param mixed $buttonContent
	 *
	 * @return $this
	 */
	public function setButtonContent( $buttonContent ) {
		$this->buttonContent = $buttonContent;

		return $this;
	}
}
