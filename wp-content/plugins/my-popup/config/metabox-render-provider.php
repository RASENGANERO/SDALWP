<?php

use WPShop\Container\Psr11\ServiceLocator;
use Wpshop\MetaBox\Form\Render\ElementRenderer;
use Wpshop\MetaBox\Form\Render\FormButton;
use Wpshop\MetaBox\Form\Render\FormCheckbox;
use Wpshop\MetaBox\Form\Render\FormEmail;
use Wpshop\MetaBox\Form\Render\FormHidden;
use Wpshop\MetaBox\Form\Render\FormMediaFile;
use Wpshop\MetaBox\Form\Render\FormMultiCheckbox;
use Wpshop\MetaBox\Form\Render\FormNumber;
use Wpshop\MetaBox\Form\Render\FormRadio;
use Wpshop\MetaBox\Form\Render\FormSelect;
use Wpshop\MetaBox\Form\Render\FormText;
use Wpshop\MetaBox\Form\Render\FormTextarea;
use Wpshop\MetaBox\Form\Render\LabelRenderer;
use Wpshop\MetaBox\Form\Render\RendererProvider;

/**
 * @see \Wpshop\MetaBox\Form\Render\RendererProvider::register()
 */
return function ( $c ) {
    $services = [
        RendererProvider::ELEMENT_PREFIX . 'button'         => function () {
            return new FormButton();
        },
        RendererProvider::ELEMENT_PREFIX . 'checkbox'       => function () {
            return new FormCheckbox();
        },
        RendererProvider::ELEMENT_PREFIX . 'color'          => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'date'           => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'datetime'       => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'email'          => function () {
            return new FormEmail();
        },
        RendererProvider::ELEMENT_PREFIX . 'file'           => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'hidden'         => function () {
            return new FormHidden();
        },
        RendererProvider::ELEMENT_PREFIX . 'image'          => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'month'          => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'multi_checkbox' => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'number'         => function () {
            return new FormNumber();
        },
        RendererProvider::ELEMENT_PREFIX . 'password'       => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'range'          => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'reset'          => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'search'         => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'select'         => function () {
            return new FormSelect();
        },
        RendererProvider::ELEMENT_PREFIX . 'submit'         => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'tel'            => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'text'           => function () {
            return new FormText();
        },
        RendererProvider::ELEMENT_PREFIX . 'textarea'       => function () {
            return new FormTextarea();
        },
        RendererProvider::ELEMENT_PREFIX . 'time'           => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'url'            => function () {
            throw new \Exception( 'not implemented yet' );
        },
        RendererProvider::ELEMENT_PREFIX . 'week'           => function () {
            throw new \Exception( 'not implemented yet' );
        },
        FormMediaFile::class                                => function ( $c ) {
            return new FormMediaFile(
                $c[ RendererProvider::ELEMENT_PREFIX . 'button' ],
                $c[ RendererProvider::ELEMENT_PREFIX . 'text' ]
            );
        },
        FormMultiCheckbox::class                            => function ( $c ) {
            return new FormMultiCheckbox( $c[ LabelRenderer::class ] );
        },
        FormRadio::class                                    => function ( $c ) {
            return new FormRadio( $c[ LabelRenderer::class ] );
        },
    ];

    foreach ( $services as $key => $value ) {
        $c[ $key ] = $value;
    }

    $additional_class_map = $c['config']['metabox_render_classmap'] ?? [];

    $service_ids = array_merge( array_keys( $services ), array_values( $additional_class_map ) );

    $c[ ElementRenderer::class ] = function ( $c ) use ( $service_ids, $additional_class_map ) {
        $elementRenderer = new ElementRenderer( new ServiceLocator( $c, $service_ids ) );

        foreach ( $additional_class_map as $type => $renderer ) {
            $elementRenderer->registerRenderer( $type, $renderer );
        }

        return $elementRenderer;
    };
    $c[ LabelRenderer::class ]   = function () {
        return new LabelRenderer();
    };
};
