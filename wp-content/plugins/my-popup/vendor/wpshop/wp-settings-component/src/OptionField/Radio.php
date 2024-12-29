<?php

namespace Wpshop\SettingApi\OptionField;

class Radio extends AbstractOption {

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
			printf( '<label for="%s[%s]">', $id, $key );
			printf( '<input type="radio" id="%1$s[%3$s]" name="%2$s" value="%3$s" %4$s>',
				$id,
				$name,
				$key,
				checked( $value, $key, false )
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
