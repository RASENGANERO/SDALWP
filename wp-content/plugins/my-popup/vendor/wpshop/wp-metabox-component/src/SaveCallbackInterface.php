<?php

namespace Wpshop\MetaBox;

interface SaveCallbackInterface {

	/**
	 * Callback format:
	 *
	 * <pre>
	 * function (WP_Post $post, array $data, $element = null){}
	 * </pre>
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function setSaveCallback( $callback );

	/**
	 * Callback format:
	 *
	 * <pre>
	 * * function (WP_Post $post, array $data, $element = null){}
	 * </pre>
	 *
	 * @return callable|null
	 */
	public function getSaveCallback();
}
