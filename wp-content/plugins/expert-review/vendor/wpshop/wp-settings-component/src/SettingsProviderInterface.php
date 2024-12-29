<?php

namespace Wpshop\SettingApi;

use Wpshop\SettingApi\SettingsPage\SettingsPageInterface;

interface SettingsProviderInterface {

	/**
	 * @return SettingsPageInterface
	 */
	public function getSettingsSubmenu();
}
