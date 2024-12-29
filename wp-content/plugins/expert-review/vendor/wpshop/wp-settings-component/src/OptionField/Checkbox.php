<?php

namespace Wpshop\SettingApi\OptionField;

class Checkbox extends AbstractOption {

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
		printf( '<label for="wpuf-%s">', $id );
		printf( '<input type="hidden" name="%s" value="0" />', $id );
		printf( '<input type="checkbox" class="checkbox" id="wpuf-%s" name="%s" value="1" %s/>',
			$id,
			$name,
			checked( $value, '1', false )
		);
		printf( '%1$s</label>', $this->description );
		echo '</fieldset>';
	}
}
