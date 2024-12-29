<?php

namespace Wpshop\SettingApi\OptionField;

class File extends AbstractOption {

	/**
	 * @var string|null
	 */
	protected $buttonLabel = 'File';

	/**
	 * @inheritDoc
	 */
	public function render( $id, $name = null, $value = null ) {
		$name  = $name ?: $id;
		$value = $this->prepareValue( $value );

		printf(
			'<input type="text" class="regular-text wpshop-settings-url" id="%s" name="%s" value="%s">',
			$id, $name, $value
		);
		printf( '<input type="button" class="button wpshop-settings-browse" value="%s" />', $this->buttonLabel );
		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}
	}

	/**
	 * @param string|null $buttonLabel
	 *
	 * @return $this
	 */
	public function setButtonLabel( $buttonLabel ) {
		$this->buttonLabel = $buttonLabel;

		return $this;
	}
}
