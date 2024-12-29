<?php

namespace Wpshop\MetaBox\Form\Element;

use WP_Post;
use Wpshop\MetaBox\ElementInterface;

class RawHtml implements ElementInterface {

	/**
	 * @var string|null
	 */
	protected $name;

	/**
	 * @var WP_Post|null
	 */
	protected $post;

	/**
	 * @var callable|null;
	 */
	protected $renderCallback;

	/**
	 * Set the name of this element
	 *
	 * @param string $name
	 *
	 * @return self
	 */
	public function setName( $name ) {
		$this->name = $name;

		return $this;
	}

	/**
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function setRenderCallback( $callback ) {
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( sprintf(
				'%s require callable, %s given',
				__METHOD__,
				is_object( $callback ) ? get_class( $callback ) : gettype( $callback )
			) );
		}
		$this->renderCallback = $callback;

		return $this;
	}

	/**
	 * Retrieve the element name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return WP_Post
	 */
	public function getPost() {
		return $this->post;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return self
	 */
	public function grabValue( WP_Post $post ) {
		$this->post = $post;

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		if ( $this->renderCallback ) {
			return call_user_func( $this->renderCallback, $this->post, $this );
		}

		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( $flag = null ) {
		return true;
	}
}
