<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\FormElementInterface;

class FormHidden extends FormInput {

	/**
	 * @var array
	 */
	protected $validTagAttributes = [
		'name'     => true,
		'disabled' => true,
		'form'     => true,
		'type'     => true,
		'value'    => true,
	];

	/**
	 * Determine input type to use
	 *
	 * @param FormElementInterface $element
	 *
	 * @return string
	 */
	protected function getType( FormElementInterface $element ) {
		return 'hidden';
	}
}
