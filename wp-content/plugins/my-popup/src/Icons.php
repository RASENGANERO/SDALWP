<?php

namespace Wpshop\PluginMyPopup;

class Icons {

    /**
     * @var array[]
     */
    protected $items = [
        'times-light'                => [
            'width'  => 512,
            'height' => 512,
            'path'   => 'M443.31 420.69a16 16 0 11-22.62 22.62L256 278.63 91.31 443.31a16 16 0 01-22.62-22.62L233.37 256 68.69 91.31a16 16 0 0122.62-22.62L256 233.37 420.69 68.69a16 16 0 0122.62 22.62L278.63 256z',
        ],
        'times-regular'              => [
            'width'  => 512,
            'height' => 512,
            'path'   => 'M441 407a24 24 0 11-34 34L256 290 105 441a24 24 0 01-34-34l151-151L71 105a24 24 0 0134-34l151 151L407 71a24 24 0 0134 34L290 256z',
        ],
        'times-bold'                 => [
            'width'  => 512,
            'height' => 512,
            'path'   => 'M438.63 393.37a32 32 0 01-45.26 45.26L256 301.25 118.63 438.63a32 32 0 01-45.26-45.26L210.75 256 73.37 118.63a32 32 0 0145.26-45.26L256 210.75 393.37 73.37a32 32 0 0145.26 45.26L301.25 256z',
        ],
        'times-circle-regular'       => [
            'width'  => 512,
            'height' => 512,
            'path'   => 'M377 169l-87 87 87 87a24 24 0 11-34 34l-87-87-87 87a24 24 0 01-34-34l87-87-87-87a24 24 0 0134-34l87 87 87-87a24 24 0 0134 34zm135 87a256.05 256.05 0 01-491.86 99.66 256.05 256.05 0 01471.72-199.32A254.47 254.47 0 01512 256zm-48 0c0-114.88-93.12-208-208-208S48 141.12 48 256s93.12 208 208 208 208-93.12 208-208z',
        ],
        'times-circle-solid-regular' => [
            'width'  => 512,
            'height' => 512,
            'path'   => 'M256 0C114.61 0 0 114.61 0 256s114.61 256 256 256 256-114.61 256-256S397.39 0 256 0zm121 343a24 24 0 11-34 34l-87-87-87 87a24 24 0 01-34-34l87-87-87-87a24 24 0 0134-34l87 87 87-87a24 24 0 0134 34l-87 87z',
        ],
        'times-square-regular'       => [
            'width'  => 512,
            'height' => 512,
            'path'   => 'M432 0H80A80.09 80.09 0 000 80v352a80.09 80.09 0 0080 80h352a80.09 80.09 0 0080-80V80a80.09 80.09 0 00-80-80zm32 432a32 32 0 01-32 32H80a32 32 0 01-32-32V80a32 32 0 0132-32h352a32 32 0 0132 32zm-87-263l-87 87 87 87a24 24 0 11-34 34l-87-87-87 87a24 24 0 01-34-34l87-87-87-87a24 24 0 0134-34l87 87 87-87a24 24 0 0134 34z',
        ],
        'times-square-solid-regular' => [
            'width'  => 512,
            'height' => 512,
            'path'   => 'M448 0H64A64 64 0 000 64v384a64 64 0 0064 64h384a64 64 0 0064-64V64a64 64 0 00-64-64zm-71 343a24 24 0 11-34 34l-87-87-87 87a24 24 0 01-34-34l87-87-87-87a24 24 0 0134-34l87 87 87-87a24 24 0 0134 34l-87 87z',
        ],
    ];

    /**
     * @return string[]
     */
    public function get_icon_list() {
        return array_keys( $this->items );
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return string
     */
    public function get_icon( $name, array $params = [] ) {
        if ( ! array_key_exists( $name, $this->items ) ) {
            return '';
        }

        $params = wp_parse_args( $params, [
            'width'       => null,
            'height'      => null,
            'color'       => 'currentColor',
            'view_width'  => $this->items[ $name ]['width'] ?? '32',
            'view_height' => $this->items[ $name ]['height'] ?? '32',
        ] );

        $props = array_filter( [
            'xmlns'   => 'http://www.w3.org/2000/svg',
            'viewBox' => "0 0 {$params['view_width']} {$params['view_height']}",
            'width'   => $params['width'],
            'height'  => $params['height'],
        ], function ( $val ) {
            return null !== $val;
        } );

        array_walk( $props, function ( &$val, $key ) {
            $val = $key . '="' . $val . '"';
        } );

        return '<svg ' . implode( ' ', $props ) . '><path d="' . $this->items[ $name ]['path'] . '" fill="' . $params['color'] . '"/></svg>';
    }
}
