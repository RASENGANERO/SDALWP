<?php

namespace Wpshop\SettingApi\OptionField;

class Editor extends AbstractOption {

	/**
	 * @var int
	 */
	protected $size = 500;

	/**
	 * @var array
	 */
	protected $editorOptions = [];

	/**
	 * @inheritDoc
	 */
	public function render( $id, $name = null, $value = null ) {
		$name     = $name ?: $id;
		$settings = array_merge( [
			'teeny'         => true,
			'textarea_name' => $name,
			'textarea_rows' => 10,
		], $this->editorOptions );

		printf( '<div style="max-width: %dpx;">', $this->size );

		wp_editor( $value, str_replace( [ '[', ']' ], '-', $id ), $settings );

		echo '</div>';

		if ( $this->description ) {
			printf( '<p class="description">%s</p>', $this->description );
		}
	}

	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function setSize( $size ) {
		$this->size = $size;

		return $this;
	}

	/**
	 * @param array $editorOptions
	 *
	 * @return $this
	 */
	public function setEditorOptions( $editorOptions ) {
		$this->editorOptions = $editorOptions;

		return $this;
	}
}
