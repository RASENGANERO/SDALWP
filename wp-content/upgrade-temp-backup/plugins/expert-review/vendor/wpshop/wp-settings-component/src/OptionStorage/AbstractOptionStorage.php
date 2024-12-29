<?php

namespace Wpshop\SettingApi\OptionStorage;

use ArrayAccess;

abstract class AbstractOptionStorage implements ArrayAccess {

	const MODE_ADD    = 'add';
	const MODE_UPDATE = 'update';

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var bool
	 */
	protected $isLoaded = false;

	/**
	 * @return string
	 */
	abstract public function getSection();

	/**
	 * @return $this
	 */
	public function destroy() {
		delete_option( $this->getSection() );

		return $this;
	}

	/**
	 * @param string $mode
	 *
	 * @return $this
	 */
	public function save( $mode = self::MODE_UPDATE ) {
		$this->load();

		switch ( $mode ) {
			case self::MODE_UPDATE:
				update_option( $this->getSection(), $this->data );
				break;
			case self::MODE_ADD:
				add_option( $this->getSection(), $this->data );
				break;
			default:
				break;
		}

		return $this;
	}

	/**
	 *
	 * @return $this
	 */
	protected function load() {
		if ( ! $this->isLoaded ) {
			$this->data     = (array) get_option( $this->getSection(), [] );
			$this->isLoaded = true;
		}

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
		$this->load();

		// $this->get_some_value($name)
		$method = 'get_' . $name;
		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}

		// $this->getSomeValue($name)
		$method = 'get' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $name ) ) );
		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}

		return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : null;
	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @return void
	 */
	public function __set( $name, $value ) {
		$this->load();

		// $this->set_some_value($value)
		$method = 'set_' . $name;
		if ( method_exists( $this, $method ) ) {
			$this->$method( $value );

			return;
		}

		// $this->setSomeValue($value)
		$method = 'set' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $name ) ) );
		if ( method_exists( $this, $method ) ) {
			$this->$method( $value );

			return;
		}

		if ( $value === null ) {
			unset( $this->data[ $name ] );
		} else {
			$this->data[ $name ] = $value;
		}
	}

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function __unset( $name ) {
		$this->load();

		// $this->unset_some_value()
		$method = 'unset_' . $name;
		if ( method_exists( $this, $method ) ) {
			$this->$method();

			return;
		}

		// $this->unsetSomeValue()
		$method = 'unset' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $name ) ) );
		if ( method_exists( $this, $method ) ) {
			$this->$method();

			return;
		}

		unset( $this->data[ $name ] );
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return array_key_exists( $name, $this->data );
	}


	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {
		return $this->__isset( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return $this->__get( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ) {
		$this->__set( $offset, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ) {
		$this->__unset( $offset );
	}
}
