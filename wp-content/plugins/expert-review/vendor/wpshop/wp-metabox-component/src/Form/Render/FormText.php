<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\FormElementInterface;

class FormText extends FormInput {

	/**
	 * Attributes valid for the input tag type="text"
	 *
	 * @var array
	 */
	protected $validTagAttributes = [
		'name'         => true,
		'autocomplete' => true,
		'autofocus'    => true,
		'dirname'      => true,
		'disabled'     => true,
		'form'         => true,
		'list'         => true,
		'maxlength'    => true,
		'minlength'    => true,
		'pattern'      => true,
		'placeholder'  => true,
		'readonly'     => true,
		'required'     => true,
		'size'         => true,
		'type'         => true,
		'value'        => true,
	];

	/**
	 * @inheritDoc
	 */
	protected function getType( FormElementInterface $element ) {
		return 'text';
	}
}
