<?php

namespace Wpshop\SettingApi\OptionField;

class MultiSelect extends Select {

	/**
	 * @inheritDoc
	 */
	public function render( $id, $name = null, $value = null ) {
		$name  = $name ?: $id;
		$value = $this->prepareValue( $value );

		$value = is_array( $value ) ? $value : [ $value ];

		$options = $this->options;
		if ( $this->emptyOption !== null ) {
			$options = [ '' => $this->emptyOption ] + $options;
		}

		echo '<fieldset>';
		printf( '<select multiple id="%s" name="%s[]">%s</select>', $id, $name, $this->renderOptions( $options, $value ) );
		echo '</fieldset>';

		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}
	}
}
