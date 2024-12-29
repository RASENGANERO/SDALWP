<?php

namespace Wpshop\MetaBox\Form\Element;

use WP_Post;
use Wpshop\MetaBox\SaveCallbackInterface;

class MultiCheckbox extends Checkbox implements SaveCallbackInterface {

	const LABEL_APPEND  = 'append';
	const LABEL_PREPEND = 'prepend';

	/**
	 * @var array
	 */
	protected $valueOptions = [];

	/**
	 * @var string
	 */
	protected $labelPosition = self::LABEL_APPEND;

	/**
	 * @var \Closure|null
	 */
	protected $saveCallback;

	/**
	 * @var string
	 */
	protected $separator = '<br>';

	/**
	 * MultiCheckbox constructor.
	 *
	 * @param null  $name
	 * @param array $options
	 */
	public function __construct( $name = null, array $options = [] ) {
		parent::__construct( $name, $options );
		$this->saveCallback = function ( WP_Post $post, $data ) {
			$value = isset( $data[ $this->getName() ] ) ? $data[ $this->getName() ] : [];
			update_post_meta( $post->ID, $this->getName(), $value );
		};
	}

	/**
	 * @return string
	 */
	public function getLabelPosition() {
		return $this->labelPosition;
	}

	/**
	 * @param string $labelPosition
	 *
	 * @return $this
	 */
	public function setLabelPosition( $labelPosition ) {
		$this->labelPosition = $labelPosition;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getValueOptions() {
		return $this->valueOptions;
	}

	/**
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setValueOptions( array $options ) {
		$this->valueOptions = $options;

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return $this
	 */
	public function unsetValueOption( $key ) {
		if ( isset( $this->valueOptions[ $key ] ) ) {
			unset( $this->valueOptions[ $key ] );
		}

		return $this;
	}

	/**
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setOptions( $options ) {
		parent::setOptions( $options );

		if ( isset( $this->options['value_options'] ) ) {
			$this->setValueOptions( $this->options['value_options'] );
		}
		// Alias for 'value_options'
		if ( isset( $this->options['options'] ) ) {
			$this->setValueOptions( $this->options['options'] );
		}

		return $this;
	}

	/**
	 * Get only the values from the options attribute
	 *
	 * @return array
	 */
	protected function getValueOptionsValues() {
		$values  = [];
		$options = $this->getValueOptions();
		foreach ( $options as $key => $optionSpec ) {
			$value    = ( is_array( $optionSpec ) ) ? $optionSpec['value'] : $key;
			$values[] = $value;
		}
		if ( $this->useHiddenElement() ) {
			$values[] = $this->getUncheckedValue();
		}

		return $values;
	}

	/**
	 * @inheritDoc
	 */
	public function setValue( $value ) {
		$this->value = $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSeparator() {
		return $this->separator;
	}

	/**
	 * @param string $separator
	 *
	 * @return $this
	 */
	public function setSeparator( $separator ) {
		$this->separator = $separator;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function setSaveCallback( $callback ) {
		$this->saveCallback = $callback;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSaveCallback() {
		return $this->saveCallback;
	}
}
