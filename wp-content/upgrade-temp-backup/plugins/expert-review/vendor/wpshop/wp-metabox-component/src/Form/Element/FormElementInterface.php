<?php

namespace Wpshop\MetaBox\Form\Element;

use Wpshop\MetaBox\ElementInterface;

interface FormElementInterface extends ElementInterface {

	/**
	 * Set the title of this element
	 *
	 * @param string $title
	 *
	 * @return self
	 */
	public function setTitle( $title );

	/**
	 * Retrieve the element title
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Set options for an element
	 *
	 * @param array $options
	 *
	 * @return self
	 */
	public function setOptions( $options );

	/**
	 * Set a single option for an element
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return self
	 */
	public function setOption( $key, $value );

	/**
	 * get the defined options
	 *
	 * @return array
	 */
	public function getOptions();

	/**
	 * return the specified option
	 *
	 * @param string $option
	 *
	 * @return null|mixed
	 */
	public function getOption( $option );

	/**
	 * Set a single element attribute
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return self
	 */
	public function setAttribute( $key, $value );

	/**
	 * Retrieve a single element attribute
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getAttribute( $key );

	/**
	 * Return true if a specific attribute is set
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasAttribute( $key );

	/**
	 * Set many attributes at once
	 *
	 * Implementation will decide if this will overwrite or merge.
	 *
	 * @param array $attributes
	 *
	 * @return self
	 */
	public function setAttributes( array $attributes );

	/**
	 * Retrieve all attributes at once
	 *
	 * @return array
	 */
	public function getAttributes();

	/**
	 * Set the value of the element
	 *
	 * @param mixed $value
	 *
	 * @return self
	 */
	public function setValue( $value );

	/**
	 * Retrieve the element value
	 *
	 * @return mixed
	 */
	public function getValue();

	/**
	 * @param string $value
	 *
	 * @return self
	 */
	public function setDescription( $value );

	/**
	 * @return string
	 */
	public function getDescription();
}
