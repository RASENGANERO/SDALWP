<?php

namespace Wpshop\Quizle\Admin;

use WP_Screen;
use Wpshop\Quizle\PluginContainer;

class QuizleResultGrid {

    /**
     * @var string
     */
    protected $screen_id;

    /**
     * @param string $screen_id
     */
    public function __construct( $screen_id ) {
        $this->screen_id = $screen_id;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'admin_menu', [ $this, '_prepare_quizle_grid' ], 9 );
        add_filter( 'screen_options_show_submit', [ $this, '_shop_screen_options_submit' ], 10, 2 );
    }

    /**
     * @return void
     */
    public function _prepare_quizle_grid() {
        add_filter( "manage_{$this->screen_id}_columns", function () {
            return PluginContainer::get( ResultListTable::class )->get_columns();
        } );
    }

    /**
     * @param bool      $result
     * @param WP_Screen $screen
     *
     * @return bool
     */
    public function _shop_screen_options_submit( $result, $screen ) {
        if ( $screen->base === $this->screen_id ) {
            return true;
        }

        return $result;
    }
}
