<?php

namespace Wpshop\MetaBox\Form\Element;

use InvalidArgumentException;
use WP_Post;
use Wpshop\MetaBox\Element\RenderEventInterface;
use Wpshop\MetaBox\Element\SaveEventInterface;

abstract class FormElement implements
	AfterFieldInfoInterface,
	LabelAwareInterface,
	FormElementInterface,
	SaveEventInterface,
	RenderEventInterface {

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @var null|string
	 */
	protected $label;

	/**
	 * @var array
	 */
	protected $labelAttributes = [];

	/**
	 * Label specific options
	 *
	 * @var array
	 */
	protected $labelOptions = [];

	/**
	 * @var array custom options
	 */
	protected $options = [];

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var string|null
	 */
	protected $description;

	/**
	 * @var string|null;
	 */
	protected $afterFieldInfo;

	/**
	 * @var callable|null
	 */
	protected $onBeforeSave;

	/**
	 * @var callable|null
	 */
	protected $onAfterSave;

	/**
	 * @var callable|null
	 */
	protected $onBeforeRender;

	/**
	 * @var callable|null
	 */
	protected $onAfterRender;

	/**
	 * @var bool
	 */
	protected $shouldRender = true;

	/**
	 * FormElement constructor.
	 *
	 * @param string|null $name
	 * @param array       $options
	 */
	public function __construct( $name = null, array $options = [] ) {
		if ( null !== $name ) {
			$this->setName( $name );
		}

		if ( ! empty( $options ) ) {
			$this->setOptions( $options );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function setName( $name ) {
		$this->setAttribute( 'name', $name );

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return $this->getAttribute( 'name' );
	}

	/**
	 * @inheritDoc
	 */
	public function grabValue( WP_Post $post ) {
		$this->setValue( get_post_meta( $post->ID, $this->getName(), true ) );

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function setTitle( $title ) {
		$this->setAttribute( 'title', $title );

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getAttribute( 'title' );
	}

	/**
	 * @inheritDoc
	 */
	public function setOptions( $options ) {
		if ( ! is_array( $options ) ) {
			throw new InvalidArgumentException(
				'The options parameter must be an array'
			);
		}

		if ( isset( $options['label'] ) ) {
			$this->setLabel( $options['label'] );
		}

		if ( isset( $options['label_attributes'] ) ) {
			$this->setLabelAttributes( $options['label_attributes'] );
		}

		if ( isset( $options['label_options'] ) ) {
			$this->setLabelOptions( $options['label_options'] );
		}

		$this->options = $options;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function setOption( $key, $value ) {
		$this->options[ $key ] = $value;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @inheritDoc
	 */
	public function getOption( $option ) {
		if ( ! isset( $this->options[ $option ] ) ) {
			return null;
		}

		return $this->options[ $option ];
	}

	/**
	 * @inheritDoc
	 */
	public function setAttribute( $key, $value ) {
		if ( $key === 'value' ) {
			$this->setValue( $value );

			return $this;
		}

		$this->attributes[ $key ] = $value;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAttribute( $key ) {
		if ( ! isset( $this->attributes[ $key ] ) ) {
			return null;
		}

		return $this->attributes[ $key ];
	}

	/**
	 * @inheritDoc
	 */
	public function hasAttribute( $key ) {
		return array_key_exists( $key, $this->attributes );
	}

	/**
	 * @inheritDoc
	 */
	public function setAttributes( array $attributes ) {
		foreach ( $attributes as $key => $value ) {
			$this->setAttribute( $key, $value );
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @inheritDoc
	 */
	public function setValue( $value ) {
		$this->value = $value;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @inheritDoc
	 */
	public function setDescription( $description ) {
		$this->description = $description;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @inheritDoc
	 */
	public function setLabel( $label ) {
		$this->label = $label;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @inheritDoc
	 */
	public function setLabelAttributes( array $labelAttributes ) {
		$this->labelAttributes = $labelAttributes;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getLabelAttributes() {
		return $this->labelAttributes;
	}

	/**
	 * @inheritDoc
	 */
	public function setLabelOptions( array $options ) {
		foreach ( $options as $key => $value ) {
			$this->setLabelOption( $key, $value );
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getLabelOptions() {
		return $this->labelOptions;
	}

	/**
	 * @inheritDoc
	 */
	public function setLabelOption( $key, $value ) {
		$this->labelOptions[ $key ] = $value;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getLabelOption( $key ) {
		if ( ! isset( $this->labelOptions[ $key ] ) ) {
			return null;
		}

		return $this->labelOptions[ $key ];
	}

	/**
	 * @inheritDoc
	 */
	public function removeLabelOption( $key ) {
		unset( $this->labelOptions[ $key ] );

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function hasLabelOption( $key ) {
		return array_key_exists( $key, $this->labelOptions );
	}

	/**
	 * @inheritDoc
	 */
	public function removeLabelOptions( array $keys ) {
		foreach ( $keys as $key ) {
			unset( $this->labelOptions[ $key ] );
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function clearLabelOptions() {
		$this->labelOptions = [];

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function setAfterFieldInfo( $info ) {
		$this->afterFieldInfo = $info;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getAfterFieldInfo() {
		return $this->afterFieldInfo;
	}

	/**
	 * @inheritDoc
	 */
	public function setOnBeforeSave( $callback ) {
		$this->onBeforeSave = $callback;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getOnBeforeSave() {
		return $this->onBeforeSave;
	}

	/**
	 * @inheritDoc
	 */
	public function setOnAfterSave( $callback ) {
		$this->onAfterSave = $callback;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getOnAfterSave() {
		return $this->onAfterSave;
	}

	/**
	 * @inheritDoc
	 */
	public function setOnBeforeRender( $callback ) {
		$this->onBeforeRender = $callback;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getOnBeforeRender() {
		return $this->onBeforeRender;
	}

	/**
	 * @inheritDoc
	 */
	public function setOnAfterRender( $callback ) {
		$this->onAfterRender = $callback;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getOnAfterRender() {
		return $this->onAfterRender;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( $flag = null ) {
		if ( null !== $flag ) {
			$this->shouldRender = (bool) $flag;
		}

		return $this->shouldRender;
	}
}
