<?php

namespace Wpshop\Quizle\Css;

class CssBuilder {
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param string $prefix
     */
    public function __construct( $prefix ) {
        $this->prefix = $prefix;
    }

    /**
     * @param string $selector
     * @param array  $properties
     *
     * @return $this
     */
    public function add( $selector, array $properties ) {
        foreach ( $properties as $key => $value ) {
            $this->items[ $selector ][ $key ] = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        $styles = '';
        foreach ( $this->items as $selector => $properties ) {
            $rows = [];

            $properties = array_filter( $properties, function ( $val ) {
                return null !== $val;
            } );

            foreach ( $properties as $property => $value ) {
                $rows[] = "$property:$value";
            }

            if ( $rows ) {
                $rows = implode( WP_DEBUG ? ";\n  " : ';', $rows );
                $rows = ( WP_DEBUG ? "{\n  " : '{' ) . $rows . ( WP_DEBUG ? "}\n" : '}' );

                $styles .= $this->prefix . ' ' . $selector . $rows;
            }
        }

        return $styles;
    }
}
