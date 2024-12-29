<?php

namespace Wpshop\ExpertReview;

use Pimple\Psr11\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class PluginContainer {

    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public static function get( $id ) {
        return static::container()->get( $id );
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public static function has( $id ) {
        return static::container()->has( $id );
    }

    /**
     * @return ContainerInterface
     */
    protected static function container() {
        if ( ! static::$container ) {
            $config            = require_once dirname( __DIR__ ) . '/config/config.php';
            $init              = require_once dirname( __DIR__ ) . '/config/container.php';
            static::$container = new Container( $init( $config ) );
        }

        return static::$container;
    }
}
