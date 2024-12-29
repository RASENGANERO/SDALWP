<?php

namespace Wpshop\SettingApi\OptionField;

class MultiCheckbox extends AbstractOption {

	protected $options = [];

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

		echo '<fieldset>';
		foreach ( $this->options as $key => $label ) {
			$val = isset( $value[ $key ] ) ? $value[ $key ] : null;
			printf( '<label for="%s[%s]">', $id, $key );
			printf(
				'<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%3$s[%2$s]" value="1" %4$s>',
				$id,
				$key,
				$name,
				checked( $val, '1', false )
			);
			printf( '%s</label><br>', esc_html( $label ) );
		}

		echo '</fieldset>';

		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}

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
}
