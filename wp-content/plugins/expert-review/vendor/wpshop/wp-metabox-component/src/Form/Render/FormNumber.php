<?php

namespace Wpshop\MetaBox\Form\Render;

class FormNumber extends FormInput {

	/**
	 * @internal
	 */
	protected $validTagAttributes = [
		'name'         => true,
		'autocomplete' => true,
		'autofocus'    => true,
		'disabled'     => true,
		'form'         => true,
		'list'         => true,
		'max'          => true,
		'min'          => true,
		'step'         => true,
		'placeholder'  => true,
		'readonly'     => true,
		'required'     => true,
		'type'         => true,
		'value'        => true,
	];
}
