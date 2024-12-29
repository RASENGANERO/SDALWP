<?php

namespace Wpshop\MetaBox;

use DomainException;
use Iterator;
use Wpshop\MetaBox\MetaBoxContainer\MetaBoxContainerInterface;
use Wpshop\MetaBox\Provider\MetaBoxProviderInterface;
use Wpshop\MetaBox\Provider\ScriptProviderInterface;
use Wpshop\MetaBox\Provider\StyleProviderInterface;

class MetaBoxManager {

	/**
	 * @var Iterator|MetaBoxProviderInterface[]
	 */
	protected $providers;

	/**
	 * @var array
	 */
	protected $metaBoxIds = [];

	/**
	 * MetaBoxManager constructor.
	 *
	 * @param Iterator $providers
	 */
	public function __construct( Iterator $providers ) {
		$this->providers = $providers;
	}

	/**
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', function () {
			$this->walkProviders( function ( MetaBoxProviderInterface $provider ) {
				$provider->initMetaBoxes( $this );
				if ( $provider instanceof ScriptProviderInterface ) {
					$provider->enqueueScripts();
				}
				if ( $provider instanceof StyleProviderInterface ) {
					$provider->enqueueStyles();
				}
			} );
		} );
	}

	/**
	 * @param callable $callback
	 *
	 * @return void
	 */
	protected function walkProviders( $callback ) {
		foreach ( $this->providers as $provider ) {
			if ( is_object( $provider ) && $provider instanceof MetaBoxProviderInterface ) {
				$callback( $provider );
			} else {
				throw new \RuntimeException( sprintf(
					'Unable to init metabox, provider must be instance of %s, %s given',
					MetaBoxProviderInterface::class,
					is_object( $provider ) ? get_class( $provider ) : gettype( $provider )
				) );
			}
		}
	}

	/**
	 * @param MetaBoxContainerInterface $box
	 * @param int                       $addPriority
	 * @param int                       $savePriority
	 *
	 * @return $this
	 */
	public function addMetaBox( MetaBoxContainerInterface $box, $addPriority = 10, $savePriority = 11 ) {
		$boxId = spl_object_hash( $box );
		if ( array_key_exists( $boxId, $this->metaBoxIds ) ) {
			throw new DomainException( sprintf(
				'%s requires that the box has an unique id; %s already exists',
				__METHOD__,
				get_class( $boxId )
			) );
		}
		$this->metaBoxIds[ $boxId ] = $boxId;

		add_action( 'load-post.php', $initMetaBox = function () use ( $box, $addPriority, $savePriority ) {
			add_action( 'add_meta_boxes', function () use ( $box ) {
				add_meta_box(
					$box->getId(),
					$box->getTitle(),
					[ $box, 'render' ],
					$box->getScreen(),
					$box->getContext(),
					$box->getPriority()
				);
			}, $addPriority );

			$tags = [];
			if ( $box->getScreen() ) {
				$screens = is_array( $box->getScreen() ) ? $box->getScreen() : [ $box->getScreen() ];
				foreach ( $screens as $screen ) {
					$tags[] = 'save_post_' . $screen;
				}
			} else {
				$tags[] = 'save_post';
			}
			foreach ( $tags as $tag ) {
				add_action( $tag, function ( $id, $post ) use ( $box ) {
					$box->save( $post );
				}, $savePriority, 2 );
			}
		} );
		add_action( 'load-post-new.php', $initMetaBox );

		return $this;
	}
}
