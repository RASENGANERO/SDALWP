<?php

namespace Wpshop\MetaBox\Form\Element;

interface AfterFieldInfoInterface {

	/**
	 * @param string|null $info
	 *
	 * @return self
	 */
	public function setAfterFieldInfo( $info );

	/**
	 * @return string|null
	 */
	public function getAfterFieldInfo();
}
