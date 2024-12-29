<?php

namespace Wpshop\MetaBox\Element;

interface SaveEventInterface {

	/**
	 * callback signature:
	 * <pre>
	 * function(\WP_Post $post, array $data, $element, $metaboxContainer) {}
	 * </pre>
	 *
	 * @param callable $callback
	 *
	 * @return self
	 */
	public function setOnBeforeSave( $callback );

	/**
	 * callback signature:
	 * <pre>
	 * function(\WP_Post $post, array $data, $element, $metaboxContainer) {}
	 * </pre>
	 *
	 * @return callable|null
	 */
	public function getOnBeforeSave();

	/**
	 * callback signature:
	 * <pre>
	 * function(\WP_Post $post, array $data, $element, $metaboxContainer) {}
	 * </pre>
	 *
	 * @param callable $callback
	 *
	 * @return self
	 */
	public function setOnAfterSave( $callback );

	/**
	 * callback signature:
	 * <pre>
	 * function(\WP_Post $post, array $data, $element, $metaboxContainer) {}
	 * </pre>
	 *
	 * @return callable|null
	 */
	public function getOnAfterSave();
}
