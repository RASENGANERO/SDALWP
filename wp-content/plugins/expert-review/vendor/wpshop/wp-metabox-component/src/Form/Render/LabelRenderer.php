<?php

namespace Wpshop\MetaBox\Form\Render;

use DomainException;
use InvalidArgumentException;
use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\LabelAwareInterface;

class LabelRenderer extends AbstractElement {

	const APPEND  = 'append';
	const PREPEND = 'prepend';

	/**
	 * @var array
	 */
	protected $validTagAttributes = [
		'for'  => true,
		'form' => true,
	];

	/**
	 * @param FormElementInterface|null $element
	 * @param string|null               $labelContent
	 * @param string|null               $position
	 *
	 * @return string
	 */
	public function render( FormElementInterface $element = null, $labelContent = null, $position = null ) {
		$openTag = $this->openTag( $element );
		$label   = '';
		if ( $element instanceof LabelAwareInterface ) {
			$label = $element->getLabel();
		}

		if ( $labelContent === null || $position !== null ) {
			if ( empty( $label ) ) {
				throw new DomainException(
					sprintf(
						'%s expects either label content as the second argument, ' .
						'or that the element provided has a label attribute; neither found',
						__METHOD__
					)
				);
			}


			if ( ! $element instanceof LabelAwareInterface || ! $element->getLabelOption( 'disable_html_escape' ) ) {
				$label = esc_html( $label );
			}
		}

		if ( $label && $labelContent ) {
			switch ( $position ) {
				case self::APPEND:
					$labelContent .= $label;
					break;
				case self::PREPEND:
				default:
					$labelContent = $label . $labelContent;
					break;
			}
		}

		if ( $label && null === $labelContent ) {
			$labelContent = $label;
		}

		return $openTag . $labelContent . $this->closeTag();
	}

	/**
	 * @param FormElementInterface|array|null $attributesOrElement
	 *
	 * @return string
	 */
	public function openTag( $attributesOrElement = null ) {
		if ( null === $attributesOrElement ) {
			return '<label>';
		}

		if ( is_array( $attributesOrElement ) ) {
			$attributes = $this->createAttributesString( $attributesOrElement );

			return sprintf( '<label %s>', $attributes );
		}

		if ( ! $attributesOrElement instanceof FormElementInterface ) {
			throw new InvalidArgumentException( sprintf(
				'%s expects an array or %s instance; received "%s"',
				__METHOD__,
				FormElementInterface::class,
				( is_object( $attributesOrElement ) ? get_class( $attributesOrElement ) : gettype( $attributesOrElement ) )
			) );
		}

		$id = $this->getId( $attributesOrElement );
		if ( null === $id ) {
			throw new DomainException( sprintf(
				'%s expects the Element provided to have either a name or an id present; neither found',
				__METHOD__
			) );
		}

		$labelAttributes = [];
		if ( $attributesOrElement instanceof LabelAwareInterface ) {
			$labelAttributes = $attributesOrElement->getLabelAttributes();
		}

		$attributes = [ 'for' => $id ];

		if ( ! empty( $labelAttributes ) ) {
			$attributes = array_merge( $labelAttributes, $attributes );
		}

		$attributes = $this->createAttributesString( $attributes );

		return sprintf( '<label %s>', $attributes );
	}

	/**
	 * Return a closing label tag
	 *
	 * @return string
	 */
	public function closeTag() {
		return '</label>';
	}

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	public function getId( FormElementInterface $element ) {
		$id = $element->getAttribute( 'id' );
		if ( null !== $id ) {
			return $id;
		}

		return $element->getName();
	}
}
