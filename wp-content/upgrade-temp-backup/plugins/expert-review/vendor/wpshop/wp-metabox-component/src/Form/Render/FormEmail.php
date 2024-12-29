<?php

namespace Wpshop\MetaBox\Form\Render;

class FormEmail extends FormInput {

	/**
	 * Attributes valid for the input tag type="email"
	 *
	 * @var array
	 */
	protected $validTagAttributes = [
		'name'         => true,
		'autocomplete' => true,
		'autofocus'    => true,
		'disabled'     => true,
		'form'         => true,
		'list'         => true,
		'maxlength'    => true,
		'minlength'    => true,
		'multiple'     => true,
		'pattern'      => true,
		'placeholder'  => true,
		'readonly'     => true,
		'required'     => true,
		'size'         => true,
		'type'         => true,
		'value'        => true,
	];
}
