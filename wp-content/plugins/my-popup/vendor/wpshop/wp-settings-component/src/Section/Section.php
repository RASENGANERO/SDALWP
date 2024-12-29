<?php

namespace Wpshop\SettingApi\Section;

use Wpshop\SettingApi\OptionField\FieldInterface;
use Wpshop\SettingApi\OptionStorage\AbstractOptionStorage;

class Section implements SectionInterface {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var callable|null
	 */
	protected $renderCallback;

	/**
	 * @var string|null;
	 */
	protected $description;

	/**
	 * @var FieldInterface[]
	 */
	protected $fields = [];

	/**
	 * @var AbstractOptionStorage
	 */
	protected $optionStorage;

	/**
	 * @var bool
	 */
	protected $enabled = true;

	/**
	 * Section constructor.
	 *
	 * @param string                       $id
	 * @param string                       $title
	 * @param string|AbstractOptionStorage $optionStorage
	 */
	public function __construct( $id, $title, $optionStorage ) {
		$this->id    = $id;
		$this->title = $title;
		if ( ! is_object( $optionStorage ) && class_exists( $optionStorage ) ) {
			$optionStorage = new $optionStorage;
		}
		$this->optionStorage = $optionStorage;
	}

	/**
	 * @return AbstractOptionStorage
	 */
	public function getOptionStorage() {
		return $this->optionStorage;
	}

	/**
	 * @param FieldInterface $field
	 *
	 * @return $this
	 */
	public function addField( FieldInterface $field ) {
		$this->fields[ $field->getName() ] = $field;

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return FieldInterface|null
	 */
	public function getFieldByName( $name ) {
		return isset( $this->fields[ $name ] ) ? $this->fields[ $name ] : null;
	}

	/**
	 * @return FieldInterface[]
	 */
	public function getFields() {
		return $this->fields;
	}

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
	 * @return callable|null
	 */
	public function getRenderCallback() {
		return $this->renderCallback
			?: function () {
				if ( $this->description ) {
					printf( '<p>%s</p>', esc_html( $this->description ) );
				}
			};
	}

	/**
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function setRenderCallback( $callback ) {
		$this->renderCallback = $callback;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string|null $description
	 *
	 * @return $this
	 */
	public function setDescription( $description ) {
		$this->description = $description;

		return $this;
	}
}
