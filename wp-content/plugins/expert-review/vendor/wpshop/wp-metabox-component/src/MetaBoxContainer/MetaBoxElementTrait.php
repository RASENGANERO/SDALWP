<?php

namespace Wpshop\MetaBox\MetaBoxContainer;

use SplPriorityQueue;
use Wpshop\MetaBox\ElementInterface;

trait MetaBoxElementTrait {

	/**
	 * @var int
	 */
	protected $elementSortCounter = 10;

	/**
	 * @var SplPriorityQueue|ElementInterface[]
	 */
	protected $_elements;

	/**
	 * @param ElementInterface|array $element
	 * @param int|null               $sortOrder
	 *
	 * @return $this
	 */
	public function addElement( $element, $sortOrder = null ) {
		if ( is_array( $element ) ) {
			$element = $this->configure( $element );
		}

		if ( ! $element instanceof ElementInterface ) {
			throw new \InvalidArgumentException( sprintf(
				'%s requires instance of %s', __METHOD__, ElementInterface::class
			) );
		}

		if ( ! $sortOrder ) {
			$sortOrder = ( $this->elementSortCounter += 10 );
		}

		list( $element, $sortOrder ) = apply_filters( 'wpshop_metabox_add_element', [ $element, $sortOrder ], $this );

		if ( ! $this->_elements ) {
			$this->_elements = new SplPriorityQueue();
		}

		$this->_elements->insert( $element, PHP_INT_MAX - $sortOrder );

		return $this;
	}

	/**
	 * @return SplPriorityQueue|ElementInterface[]
	 */
	public function getElements() {
		if ( $this->_elements ) {
			return clone $this->_elements;
		}

		return new SplPriorityQueue();
	}

	/**
	 * @param array $params
	 *
	 * @return ElementInterface
	 */
	protected function configure( array $params ) {
		throw new \RuntimeException( 'not implemented yet' );
	}
}
