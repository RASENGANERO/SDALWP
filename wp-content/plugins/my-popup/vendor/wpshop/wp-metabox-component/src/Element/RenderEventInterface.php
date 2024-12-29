<?php

namespace Wpshop\MetaBox\Element;

interface RenderEventInterface {

	/**
	 * callback signature:
	 * <pre>
	 * function($element, WP_Post $post, $metaboxContainer) {
	 *      return ''
	 * }
	 * </pre>
	 *
	 * @param callable $callback
	 *
	 * @return self
	 */
	public function setOnBeforeRender( $callback );

	/**
	 * callback signature:
	 * <pre>
	 * function($element, WP_Post $post, $metaboxContainer) {
	 *      return ''
	 * }
	 * </pre>
	 *
	 * @return callable|null
	 */
	public function getOnBeforeRender();

	/**
	 * callback signature:
	 * <pre>
	 * function($element, WP_Post $post, $metaboxContainer) {
	 *      return ''
	 * }
	 * </pre>
	 *
	 * @param callable $callback
	 *
	 * @return self
	 */
	public function setOnAfterRender( $callback );

	/**
	 * callback signature:
	 * <pre>
	 * function($element, WP_Post $post, $metaboxContainer) {
	 *      return ''
	 * }
	 * </pre>
	 *
	 * @return callable|null
	 */
	public function getOnAfterRender();
}
