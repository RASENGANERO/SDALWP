<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\FormElementInterface;
use Wpshop\MetaBox\Form\Element\Select;

class FormSelect extends AbstractElement {

	/**
	 * Attributes valid for select
	 *
	 * @var array
	 */
	protected $validSelectAttributes = [
		'name'         => true,
		'autocomplete' => true,
		'autofocus'    => true,
		'disabled'     => true,
		'form'         => true,
		'multiple'     => true,
		'required'     => true,
		'size'         => true,
	];

	/**
	 * Attributes valid for options
	 *
	 * @var array
	 */
	protected $validOptionAttributes = [
		'disabled' => true,
		'selected' => true,
		'label'    => true,
		'value'    => true,
	];
	/**
	 * Attributes valid for option groups
	 *
	 * @var array
	 */
	protected $validOptgroupAttributes = [
		'disabled' => true,
		'label'    => true,
	];

	/**
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	public function render( FormElementInterface $element ) {
		if ( ! $element instanceof Select ) {
			throw new \InvalidArgumentException( sprintf(
				'%s requires that the element is of type %s',
				__METHOD__,
				Select::class
			) );
		}

		$name = $element->getName();
		if ( empty( $name ) && $name !== 0 ) {
			throw new \DomainException( sprintf(
				'%s requires that the element has an assigned name; none discovered',
				__METHOD__
			) );
		}

		$options = $element->getValueOptions();

		if ( ( $emptyOption = $element->getEmptyOption() ) !== null ) {
			$options = [ '' => $emptyOption ] + $options;
		}

		$attributes = $element->getAttributes();
		$value      = $this->validateMultiValue( $element->getValue(), $attributes );

		$attributes['name'] = $name;
		if ( array_key_exists( 'multiple', $attributes ) && $attributes['multiple'] ) {
			$attributes['name'] .= '[]';
		}
		$this->validTagAttributes = $this->validSelectAttributes;

		$rendered = sprintf(
			'<select %s>%s</select>',
			$this->createAttributesString( $attributes ),
			$this->renderOptions( $options, $value )
		);

		return $rendered;
	}

	/**
	 * @param array $options
	 * @param array $selectedOptions
	 *
	 * @return string
	 */
	public function renderOptions( array $options, array $selectedOptions = [] ) {
		$template      = '<option %s>%s</option>';
		$optionStrings = [];

		foreach ( $options as $key => $optionSpec ) {
			$value    = '';
			$label    = '';
			$selected = false;
			$disabled = false;

			if ( is_scalar( $optionSpec ) ) {
				$optionSpec = [
					'label' => $optionSpec,
					'value' => $key,
				];
			}

			if ( isset( $optionSpec['options'] ) && is_array( $optionSpec['options'] ) ) {
				$optionStrings[] = $this->renderOptgroup( $optionSpec, $selectedOptions );
				continue;
			}

			if ( isset( $optionSpec['value'] ) ) {
				$value = $optionSpec['value'];
			}
			if ( isset( $optionSpec['label'] ) ) {
				$label = $optionSpec['label'];
			}
			if ( isset( $optionSpec['selected'] ) ) {
				$selected = $optionSpec['selected'];
			}
			if ( isset( $optionSpec['disabled'] ) ) {
				$disabled = $optionSpec['disabled'];
			}

			if ( $this->inArray( $value, $selectedOptions ) ) {
				$selected = true;
			}

			$attributes = compact( 'value', 'selected', 'disabled' );

			if ( isset( $optionSpec['attributes'] ) && is_array( $optionSpec['attributes'] ) ) {
				$attributes = array_merge( $attributes, $optionSpec['attributes'] );
			}

			$this->validTagAttributes = $this->validOptionAttributes;
			$optionStrings[]          = sprintf(
				$template,
				$this->createAttributesString( $attributes ),
				esc_html( $label )
			);
		}

		return implode( "\n", $optionStrings );
	}

	/**
	 * @param array $optgroup
	 * @param array $selectedOptions
	 *
	 * @return string
	 */
	public function renderOptgroup( array $optgroup, array $selectedOptions = [] ) {
		$template = '<optgroup%s>%s</optgroup>';

		$options = [];
		if ( isset( $optgroup['options'] ) && is_array( $optgroup['options'] ) ) {
			$options = $optgroup['options'];
			unset( $optgroup['options'] );
		}

		$this->validTagAttributes = $this->validOptgroupAttributes;
		$attributes               = $this->createAttributesString( $optgroup );
		if ( ! empty( $attributes ) ) {
			$attributes = ' ' . $attributes;
		}

		return sprintf(
			$template,
			$attributes,
			$this->renderOptions( $options, $selectedOptions )
		);
	}

	/**
	 * @param mixed $value
	 * @param array $attributes
	 *
	 * @return array
	 */
	protected function validateMultiValue( $value, array $attributes ) {
		if ( null === $value ) {
			return [];
		}

		if ( ! is_array( $value ) ) {
			return [ $value ];
		}

		if ( ! isset( $attributes['multiple'] ) || ! $attributes['multiple'] ) {
			throw new \DomainException( sprintf(
				'%s does not allow specifying multiple selected values when the element does not have a multiple '
				. 'attribute set to a boolean true',
				__CLASS__
			) );
		}

		return $value;
	}

	/**
	 * @param mixed $needle
	 * @param array $haystack
	 * @param bool  $strict
	 *
	 * @return bool
	 */
	protected function inArray( $needle, array $haystack, $strict = false ) {
		if ( ! $strict ) {
			if ( is_int( $needle ) || is_float( $needle ) ) {
				$needle = (string) $needle;
			}
			if ( is_string( $needle ) ) {
				foreach ( $haystack as &$h ) {
					if ( is_int( $h ) || is_float( $h ) ) {
						$h = (string) $h;
					}
				}
			}
		}

		return in_array( $needle, $haystack, $strict );
	}
}
