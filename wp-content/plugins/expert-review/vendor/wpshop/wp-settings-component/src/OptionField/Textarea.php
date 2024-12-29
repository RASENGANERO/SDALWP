<?php

namespace Wpshop\SettingApi\OptionField;

class Textarea extends AbstractOption {

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

		printf(
			'<textarea rows="5" cols="55" id="%1$s" name="%2$s">%3$s</textarea>',
			$id,
			$name,
			$value
		);
		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}
	}
}
