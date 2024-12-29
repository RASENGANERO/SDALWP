<?php

namespace Wpshop\SettingApi\OptionField;

class Color extends AbstractOption {

	/**
	 * @inheritDoc
	 */
	public function render( $id, $name = null, $value = null ) {
		$name  = $name ?: $id;

		printf(
			'<input type="text" class="regular-text wp-color-picker-field" id="%s" name="%s" value="%s" data-default-color="%s">',
			$id, $name, $value, $this->default
		);
		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}
	}
}
