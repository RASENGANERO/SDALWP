<?php

namespace Wpshop\Quizle\Admin;

use JetBrains\PhpStorm\NoReturn;
use WP_Screen;
use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use Wpshop\Quizle\QuizleResultExport;
use ZipArchive;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\retreive_answers;

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

        // handle export csv
        add_action( 'admin_init', function () {
            if ( wp_doing_ajax() ) {
                return;
            }

            $post_data = wp_parse_args( $_POST, [
                'page'      => '',
                'post_type' => '',
                'action'    => '',
                'result_id' => [],
                'quizle_id' => '',
                'finished'  => false,
                '_wpnonce'  => '',
            ] );

            if ( $post_data['page'] !== MenuPage::RESULT_LIST_SLUG ||
                 $post_data['post_type'] !== Quizle::POST_TYPE ||
                 $post_data['action'] !== 'export_csv'
            ) {
                return;
            }

            $this->export_csv( $post_data );
        } );
    }

    /**
     * @param array $post_data
     *
     * @return void
     */
    #[NoReturn]
    protected function export_csv( $post_data ) {
        container()
            ->get( QuizleResultExport::class )
            ->export_csv( $post_data['result_id'], $post_data['quizle_id'], $post_data['finished'] )
        ;
    }

    /**
     * @param string data
     *
     * @return string
     */
    protected function format_additional_data( $data ) {
        if ( $data ) {
            $result = [];
            $data   = \Wpshop\Quizle\json_decode( $data, true );
            if ( is_array( $data['messengers'] ) ) {
                foreach ( $data['messengers'] as $name => $value ) {
                    $result[] = "$name: $value";
                }

                return implode( ', ', $result );
            }
        }

        return $data;
    }

    /**
     * @return void
     */
    public function _prepare_quizle_grid() {
        add_filter( "manage_{$this->screen_id}_columns", function () {
            return container()->get( ResultListTable::class )->get_columns();
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
