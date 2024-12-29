<?php

namespace Wpshop\MetaBox;

use Pimple\Container;
use Pimple\ServiceIterator;
use Pimple\ServiceProviderInterface;
use Wpshop\MetaBox\Form\Render\ElementRenderer;
use Wpshop\MetaBox\Form\Render\LabelRenderer;
use Wpshop\MetaBox\Form\Render\RendererProvider;
use Wpshop\MetaBox\MetaBoxContainer\SimpleMetaBoxContainer;

class MetaBoxManagerProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register( Container $c ) {
		$c[ SimpleMetaBoxContainer::class ] = $c->factory( function ( $c ) {
			return new SimpleMetaBoxContainer(
				$c[ LabelRenderer::class ],
				$c[ ElementRenderer::class ]
			);
		} );

		$metaBoxProviders = [];
		if ( isset( $c['config']['metabox_providers'] ) && is_array( $c['config']['metabox_providers'] ) ) {
			$metaBoxProviders = $c['config']['metabox_providers'];
		}
		$c[ MetaBoxManager::class ] = function ( $c ) use ( $metaBoxProviders ) {
			return new MetaBoxManager( new ServiceIterator( $c, $metaBoxProviders ) );
		};

		$c->register( new RendererProvider() );
	}
}
