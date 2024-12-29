<?php

namespace Wpshop\MetaBox\MetaBoxContainer;

use WP_Post;

interface MetaBoxContainerInterface {

	const PRIORITY_DEFAULT = 'default';
	const PRIORITY_HIGH    = 'high';
	const PRIORITY_LOW     = 'low';

	const CONTEXT_NORMAL   = 'normal';
	const CONTEXT_ADVANCED = 'advanced';
	const CONTEXT_SIDE     = 'side';

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getTitle();

	/**
	 * Usually this is post type
	 *
	 * @return string|string[]
	 */
	public function getScreen();

	/**
	 * @return string
	 */
	public function getContext();

	/**
	 * @return string
	 */
	public function getPriority();

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function render( WP_Post $post );

	/**
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function save( WP_Post $post );
}
