<?php

namespace Wpshop\MetaBox\MetaBoxContainer;

use InvalidArgumentException;

abstract class AbstractMetaBoxContainer implements MetaBoxContainerInterface {
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $screen;

	/**
	 * @var string
	 */
	protected $context = self::CONTEXT_NORMAL;

	/**
	 * @var string
	 */
	protected $priority = self::PRIORITY_DEFAULT;

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return $this
	 */
	public function setId( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle( $title ) {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getScreen() {
		return $this->screen;
	}

	/**
	 * @param string $screen
	 *
	 * @return $this
	 */
	public function setScreen( $screen ) {
		$this->screen = $screen;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @param string $context
	 *
	 * @return $this
	 */
	public function setContext( $context ) {
		if ( ! in_array( $context, [
			self::CONTEXT_NORMAL,
			self::CONTEXT_ADVANCED,
			self::CONTEXT_SIDE,
		] ) ) {
			throw new InvalidArgumentException( sprintf(
				'Unable to set context, use one of %s::CONTEXT_* const', MetaBoxContainerInterface::class
			) );
		}

		$this->context = $context;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @param string $priority
	 *
	 * @return $this
	 */
	public function setPriority( $priority ) {
		if ( ! in_array( $priority, [
			self::PRIORITY_DEFAULT,
			self::PRIORITY_HIGH,
			self::PRIORITY_LOW,
		] ) ) {
			throw new InvalidArgumentException( sprintf(
				'Unable to set priority, use one of %s::PRIORITY_* const', MetaBoxContainerInterface::class
			) );
		}

		$this->priority = $priority;

		return $this;
	}
}
