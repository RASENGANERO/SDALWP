<?php

namespace Wpshop\SettingApi\OptionField;

class Text extends Input {

	/**
	 * @var string|null;
	 */
	protected $placeholder;

	/**
	 * @var string
	 */
	protected $type = 'text';

	/**
	 * @param string $placeholder
	 *
	 * @return $this
	 */
	public function setPlaceholder( $placeholder ) {
		$this->placeholder = $placeholder;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function render( $id, $name = null, $value = null ) {
		$name = $name ?: $id;

		$value = $this->prepareValue( $value );

		printf(
			'<input type="%s" class="regular-text" id="%s" name="%s" value="%s" placeholder="%s" autocomplete="new-password">',
			$this->type,
			$id,
			$name,
			$value,
			$this->placeholder
		);
		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}
	}
}
