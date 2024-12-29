<?php

namespace Wpshop\PluginMyPopup\MetaBox;

use Wpshop\MetaBox\MetaBoxContainer\MetaBoxElementTrait;

class Tab {

    use MetaBoxElementTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * Tab constructor.
     *
     * @param string $id
     * @param string $title
     */
    public function __construct( $id, $title ) {
        $this->id    = $id;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
}
