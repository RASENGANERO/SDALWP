<?php

namespace Wpshop\MetaBox\Provider;

use Wpshop\MetaBox\MetaBoxManager;

interface MetaBoxProviderInterface {

	/**
	 * @param MetaBoxManager $manager
	 *
	 * @return void
	 */
	public function initMetaBoxes( MetaBoxManager $manager );
}
