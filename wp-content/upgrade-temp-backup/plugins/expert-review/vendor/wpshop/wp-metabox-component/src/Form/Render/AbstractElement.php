<?php

namespace Wpshop\MetaBox\Form\Render;

use Wpshop\MetaBox\Form\Element\FormElementInterface;

abstract class AbstractElement {

	/**
	 * @var array
	 */
	protected $validGlobalAttributes = [
		'accesskey'          => true,
		'class'              => true,
		'contenteditable'    => true,
		'contextmenu'        => true,
		'dir'                => true,
		'draggable'          => true,
		'dropzone'           => true,
		'hidden'             => true,
		'id'                 => true,
		'lang'               => true,
		'onabort'            => true,
		'onblur'             => true,
		'oncanplay'          => true,
		'oncanplaythrough'   => true,
		'onchange'           => true,
		'onclick'            => true,
		'oncontextmenu'      => true,
		'ondblclick'         => true,
		'ondrag'             => true,
		'ondragend'          => true,
		'ondragenter'        => true,
		'ondragleave'        => true,
		'ondragover'         => true,
		'ondragstart'        => true,
		'ondrop'             => true,
		'ondurationchange'   => true,
		'onemptied'          => true,
		'onended'            => true,
		'onerror'            => true,
		'onfocus'            => true,
		'oninput'            => true,
		'oninvalid'          => true,
		'onkeydown'          => true,
		'onkeypress'         => true,
		'onkeyup'            => true,
		'onload'             => true,
		'onloadeddata'       => true,
		'onloadedmetadata'   => true,
		'onloadstart'        => true,
		'onmousedown'        => true,
		'onmousemove'        => true,
		'onmouseout'         => true,
		'onmouseover'        => true,
		'onmouseup'          => true,
		'onmousewheel'       => true,
		'onpause'            => true,
		'onplay'             => true,
		'onplaying'          => true,
		'onprogress'         => true,
		'onratechange'       => true,
		'onreadystatechange' => true,
		'onreset'            => true,
		'onscroll'           => true,
		'onseeked'           => true,
		'onseeking'          => true,
		'onselect'           => true,
		'onshow'             => true,
		'onstalled'          => true,
		'onsubmit'           => true,
		'onsuspend'          => true,
		'ontimeupdate'       => true,
		'onvolumechange'     => true,
		'onwaiting'          => true,
		'role'               => true,
		'spellcheck'         => true,
		'style'              => true,
		'tabindex'           => true,
		'title'              => true,
		'xml:base'           => true,
		'xml:lang'           => true,
		'xml:space'          => true,
	];

	/**
	 * @var array
	 */
	protected $validTagAttributePrefixes = [
		'data-',
		'aria-',
		'x-',
	];

	/**
	 * @var array
	 */
	protected $validTagAttributes = [];

	/**
	 * @var array
	 */
	protected $booleanAttributes = [
		'autofocus' => [ 'on' => 'autofocus', 'off' => '' ],
		'checked'   => [ 'on' => 'checked', 'off' => '' ],
		'disabled'  => [ 'on' => 'disabled', 'off' => '' ],
		'multiple'  => [ 'on' => 'multiple', 'off' => '' ],
		'readonly'  => [ 'on' => 'readonly', 'off' => '' ],
		'required'  => [ 'on' => 'required', 'off' => '' ],
		'selected'  => [ 'on' => 'selected', 'off' => '' ],
	];

	/**
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function createAttributesString( array $attributes ) {
		$attributes = $this->prepareAttributes( $attributes );
//		$escape     = 'tag_escape';
		$escapeAttr = 'esc_html';
		$strings    = [];
		foreach ( $attributes as $key => $value ) {
			$key = strtolower( $key );

			if ( ! $value && isset( $this->booleanAttributes[ $key ] ) ) {
				// Skip boolean attributes that expect empty string as false value
				if ( '' === $this->booleanAttributes[ $key ]['off'] ) {
					continue;
				}
			}

//			$strings[] = sprintf( '%s="%s"', $escape( $key ), $escapeAttr( $value ) );
			$strings[] = sprintf( '%s="%s"', $key, $escapeAttr( $value ) );
		}

		return implode( ' ', $strings );
	}

	/**
	 * @param array $attributes
	 *
	 * @return array
	 */
	protected function prepareAttributes( array $attributes ) {
		foreach ( $attributes as $key => $value ) {
			$attribute = strtolower( $key );

			if ( ! isset( $this->validGlobalAttributes[ $attribute ] )
			     && ! isset( $this->validTagAttributes[ $attribute ] )
			     && ! $this->hasAllowedPrefix( $attribute )
			) {
				unset( $attributes[ $key ] );
				continue;
			}

			if ( $attribute != $key ) {
				unset( $attributes[ $key ] );
				$attributes[ $attribute ] = $value;
			}

			if ( isset( $this->booleanAttributes[ $attribute ] ) ) {
				$attributes[ $attribute ] = $this->prepareBooleanAttributeValue( $attribute, $value );
			}
		}

		return $attributes;
	}

	/**
	 * Whether the passed attribute has a valid prefix or not
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	protected function hasAllowedPrefix( $attribute ) {
		foreach ( $this->validTagAttributePrefixes as $prefix ) {
			if ( substr( $attribute, 0, strlen( $prefix ) ) === $prefix ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return bool|mixed
	 */
	protected function prepareBooleanAttributeValue( $attribute, $value ) {
		if ( ! is_bool( $value ) && in_array( $value, $this->booleanAttributes[ $attribute ] ) ) {
			return $value;
		}

		$value = (bool) $value;

		return ( $value
			? $this->booleanAttributes[ $attribute ]['on']
			: $this->booleanAttributes[ $attribute ]['off']
		);
	}
}
