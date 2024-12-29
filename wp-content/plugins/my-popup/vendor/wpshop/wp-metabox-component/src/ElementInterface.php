<?php

namespace Wpshop\MetaBox;

use WP_Post;

interface ElementInterface {

	/**
	 * Set the name of this element
	 *
	 * @param string $name
	 *
	 * @return self
	 */
	public function setName( $name );

	/**
	 * Retrieve the element name
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * @param WP_Post $post
	 *
	 * @return self
	 */
	public function grabValue( WP_Post $post );

	/**
	 * @param bool|null $flag if passed boolean set the flag if not just return value
	 *
	 * @return bool
	 */
	public function shouldRender( $flag = null );
}
