<?php

namespace Wpshop\MetaBox\Form\Render;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Pimple\ServiceProviderInterface;

class RendererProvider implements ServiceProviderInterface {

	const ELEMENT_PREFIX = 'form_element_';

	/**
	 * @inheritDoc
	 */
	public function register( Container $c ) {
		$services = [
			self::ELEMENT_PREFIX . 'button'         => function () {
				return new FormButton();
			},
			self::ELEMENT_PREFIX . 'checkbox'       => function () {
				return new FormCheckbox();
			},
			self::ELEMENT_PREFIX . 'color'          => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'date'           => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'datetime'       => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'email'          => function () {
				return new FormEmail();
			},
			self::ELEMENT_PREFIX . 'file'           => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'hidden'         => function () {
				return new FormHidden();
			},
			self::ELEMENT_PREFIX . 'image'          => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'month'          => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'multi_checkbox' => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'number'         => function () {
				return new FormNumber();
			},
			self::ELEMENT_PREFIX . 'password'       => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'range'          => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'reset'          => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'search'         => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'select'         => function () {
				return new FormSelect();
			},
			self::ELEMENT_PREFIX . 'submit'         => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'tel'            => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'text'           => function () {
				return new FormText();
			},
			self::ELEMENT_PREFIX . 'textarea'       => function () {
				return new FormTextarea();
			},
			self::ELEMENT_PREFIX . 'time'           => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'url'            => function () {
				throw new \Exception( 'not implemented yet' );
			},
			self::ELEMENT_PREFIX . 'week'           => function () {
				throw new \Exception( 'not implemented yet' );
			},
			FormMediaFile::class                    => function ( $c ) {
				return new FormMediaFile(
					$c[ self::ELEMENT_PREFIX . 'button' ],
					$c[ self::ELEMENT_PREFIX . 'text' ]
				);
			},
			FormMultiCheckbox::class                => function ( $c ) {
				return new FormMultiCheckbox( $c[ LabelRenderer::class ] );
			},
			FormRadio::class                        => function ( $c ) {
				return new FormRadio( $c[ LabelRenderer::class ] );
			},
		];

		foreach ( $services as $key => $value ) {
			$c[ $key ] = $value;
		}

		$additionalClassMap = isset( $c['config']['metabox_render_classmap'] )
			? $c['config']['metabox_render_classmap']
			: [];

		$serviceIds = array_merge( array_keys( $services ), array_values( $additionalClassMap ) );

		$c[ ElementRenderer::class ] = function ( $c ) use ( $serviceIds, $additionalClassMap ) {
			$elementRenderer = new ElementRenderer( new ServiceLocator( $c, $serviceIds ) );

			foreach ( $additionalClassMap as $type => $renderer ) {
				$elementRenderer->registerRenderer( $type, $renderer );
			}

			return $elementRenderer;
		};
		$c[ LabelRenderer::class ]   = function () {
			return new LabelRenderer();
		};
	}
}
