<?php

namespace Wpshop\MetaBox\Form\Element;

interface LabelAwareInterface {

	/**
	 * Set the label (if any) used for this element
	 *
	 * @param  $label
	 *
	 * @return self
	 */
	public function setLabel( $label );

	/**
	 * Retrieve the label (if any) used for this element
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Set the attributes to use with the label
	 *
	 * @param array $labelAttributes
	 *
	 * @return self
	 */
	public function setLabelAttributes( array $labelAttributes );

	/**
	 * Get the attributes to use with the label
	 *
	 * @return array
	 */
	public function getLabelAttributes();

	/**
	 * Set many label options at once
	 *
	 * Implementation will decide if this will overwrite or merge.
	 *
	 * @param array $options
	 *
	 * @return self
	 */
	public function setLabelOptions( array $options );

	/**
	 * Get label specific options
	 *
	 * @return array
	 */
	public function getLabelOptions();

	/**
	 * Set a single label optionn
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return self
	 */
	public function setLabelOption( $key, $value );

	/**
	 * Retrieve a single label option
	 *
	 * @param  $key
	 *
	 * @return mixed|null
	 */
	public function getLabelOption( $key );

	/**
	 * Remove a single label option
	 *
	 * @param string $key
	 *
	 * @return self
	 */
	public function removeLabelOption( $key );

	/**
	 * Does the element has a specific label option ?
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasLabelOption( $key );

	/**
	 * Remove many attributes at once
	 *
	 * @param array $keys
	 *
	 * @return self
	 */
	public function removeLabelOptions( array $keys );

	/**
	 * Clear all label options
	 *
	 * @return self
	 */
	public function clearLabelOptions();
}
