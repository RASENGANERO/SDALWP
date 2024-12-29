<?php

namespace Wpshop\SettingApi;

use Pimple\Container;
use Pimple\ServiceIterator;
use Pimple\ServiceProviderInterface;

class SettingsManagerProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register( Container $c ) {
		$providers = [];

		if ( $c['config']['settings_providers'] && is_array( $c['config']['settings_providers'] ) ) {
			$providers = $c['config']['settings_providers'];
		}

		foreach ( $providers as $provider ) {
			if ( ! isset( $c[ $provider ] ) ) {
				$c[ $provider ] = function () use ( $provider ) {
					return new $provider;
				};
			}
		}

		$c[ SettingsManager::class ] = function ( $c ) use ( $providers ) {
			return new SettingsManager( new ServiceIterator( $c, $providers ) );
		};
	}
}
