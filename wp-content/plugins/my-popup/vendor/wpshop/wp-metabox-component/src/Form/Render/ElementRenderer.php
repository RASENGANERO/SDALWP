<?php

namespace Wpshop\MetaBox\Form\Render;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Wpshop\MetaBox\Form\Element\ColorPicker;
use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\MediaFile;
use Wpshop\MetaBox\Form\Element\MultiCheckbox;
use Wpshop\MetaBox\Form\Element\Radio;

class ElementRenderer {

	/**
	 * @var ContainerInterface
	 */
	protected $helpers;

	/**
	 * @var array
	 */
	protected $classMap = [
		Radio::class         => FormRadio::class,
		MultiCheckbox::class => FormMultiCheckbox::class,
		ColorPicker::class   => FormColorPicker::class,
		MediaFile::class     => FormMediaFile::class,
	];

	/**
	 * @var string
	 */
	protected $elementHelperPrefix = RendererProvider::ELEMENT_PREFIX;

	/**
	 * ElementRenderer constructor.
	 *
	 * @param ContainerInterface $helpers
	 */
	public function __construct( ContainerInterface $helpers ) {
		$this->helpers = $helpers;
	}

	/**
	 * @param FormElementInterface $element
	 * @param string               $class
	 *
	 * @return void
	 */
	public static function appendCssClass( FormElementInterface $element, $class ) {
		if ( $current = $element->getAttribute( 'class' ) ) {
			$current .= ' ' . $class;
			$class   = $current;
		}
		$element->setAttribute( 'class', $class );
	}

	/**
	 * @param FormElementInterface $element
	 */
	public static function setUniqueId( FormElementInterface $element ) {
		$id = $element->getAttribute( 'id' );
		$element->setAttribute( 'id', uniqid( $id ? $id . ':' : $id ) );
	}

	/**
	 * @param string $elType
	 * @param string $rendererType
	 *
	 * @return $this
	 */
	public function registerRenderer( $elType, $rendererType ) {
		$this->classMap[ $elType ] = $rendererType;

		return $this;
	}

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string|null
	 * @throws RuntimeException
	 */
	public function render( FormElementInterface $element ) {

		$renderedInstance = $this->renderInstance( $element );

		if ( $renderedInstance !== null ) {
			return $renderedInstance;
		}

		$renderedType = $this->renderType( $element );

		if ( $renderedType !== null ) {
			return $renderedType;
		}

		throw new RuntimeException( 'Unable to render element' );
	}

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string|null
	 */
	protected function renderInstance( FormElementInterface $element ) {
		foreach ( $this->classMap as $class => $instance ) {
			if ( $element instanceof $class ) {
				if ( $this->helpers->has( $instance ) ) {
					return $this->helpers->get( $instance )->render( $element ) ?: '';
				}
				$instance = new $instance();
				if ( method_exists( $instance, 'render' ) ) {
					return $instance->render( $element ) ?: '';
				}
			}
		}

		return null;
	}

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string|null
	 */
	protected function renderType( FormElementInterface $element ) {
		$type = $this->elementHelperPrefix . $element->getAttribute( 'type' );
		if ( $this->helpers->has( $type ) ) {
			return $this->helpers->get( $type )->render( $element );
		}

		return null;
	}
}
