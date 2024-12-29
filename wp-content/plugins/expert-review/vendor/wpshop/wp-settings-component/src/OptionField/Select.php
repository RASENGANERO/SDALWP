<?php

namespace Wpshop\SettingApi\OptionField;

class Select extends AbstractOption {

	protected $options = [];

	/**
	 * @var string|null
	 */
	protected $emptyOption;

	/**
	 * @param string      $id
	 * @param string|null $name
	 * @param string|null $value
	 *
	 * @return void
	 */
	public function render( $id, $name = null, $value = null ) {
		$name  = $name ?: $id;
		$value = $this->prepareValue( $value );

		$options = $this->options;
		if ( $this->emptyOption !== null ) {
			$options = [ '' => $this->emptyOption ] + $options;
		}

		echo '<fieldset>';
		printf( '<select id="%s" name="%s">%s</select>', $id, $name, $this->renderOptions( $options, [ $value ] ) );
		echo '</fieldset>';

		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}
	}

	/**
	 * @param string|null $emptyOption
	 *
	 * @return $this
	 */
	public function setEmptyOption( $emptyOption ) {
		$this->emptyOption = $emptyOption;

		return $this;
	}

	protected function renderOptions( array $options, array $selectedOptions = [] ) {

		$optionStrings = [];

		foreach ( $options as $key => $optionSpec ) {
			$value    = '';
			$label    = '';
			$selected = false;
//			$disabled = false;

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
			if ( $this->inArray( $value, $selectedOptions ) ) {
				$selected = true;
			}

//			if ( isset( $optionSpec['disabled'] ) ) {
//				$disabled = $optionSpec['disabled'];
//			}

			$optionStrings[] = sprintf(
				'<option value="%s"%s>%s</option>',
				$value,
				$selected ? ' selected="selected"' : '',
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

		$options = [];
		if ( isset( $optgroup['options'] ) && is_array( $optgroup['options'] ) ) {
			$options = $optgroup['options'];
			unset( $optgroup['options'] );
		}
		$label = $optgroup['label'];

		return sprintf(
			'<optgroup label="%s">%s</optgroup>',
			$label,
			$this->renderOptions( $options, $selectedOptions )
		);
	}

	/**
	 * @param array $options
	 *
	 * @return $this
	 */
	public function setOptions( array $options ) {
		$this->options = $options;

		return $this;
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
