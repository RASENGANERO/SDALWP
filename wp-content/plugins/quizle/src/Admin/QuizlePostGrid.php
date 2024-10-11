<?php

namespace Wpshop\Quizle\Admin;

use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use function Wpshop\Quizle\get_quizle_type;

class QuizlePostGrid {

    /**
     * @return void
     */
    public function init() {
        add_filter( 'wp_list_table_class_name', function ( $class_name, $args ) {
            if ( $args['screen'] && $args['screen']->id === 'edit-quizle' ) {
                $class_name = QuizleListTable::class;
            }

            return $class_name;
        }, 10, 2 );

        $post_type = Quizle::POST_TYPE;
        add_filter( "manage_{$post_type}_posts_columns", [ $this, '_add_columns' ] );
        add_action( "manage_{$post_type}_posts_custom_column", [ $this, '_manage_custom_column' ], 10, 2 );

        add_filter( 'posts_where', function ( $where ) {
            global $pagenow, $wpdb;
            if ( is_admin() &&
                 $pagenow === 'edit.php' &&
                 ( $_GET['post_type'] ?? '' === Quizle::POST_TYPE ) &&
                 ! empty( $_GET['s'] )
            ) {
                $where = preg_replace(
                    "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                    "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->posts . ".post_name LIKE $1)", $where );
            }

            return $where;
        } );
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function _add_columns( array $columns ) {
        $before = array_slice( $columns, 0, 2 );
        $after  = array_slice( $columns, 2 );

        return $before + [
                'quizle_shortcode' => __( 'Shortcode', QUIZLE_TEXTDOMAIN ),
                'quizle_stats'     => __( 'Statistic', QUIZLE_TEXTDOMAIN ),
            ] + $after;
    }

    /**
     * @param string $column_key
     * @param int    $post_id
     *
     * @return void
     */
    public function _manage_custom_column( $column_key, $post_id ) {
        $post = get_post( $post_id );
        if ( $column_key === 'quizle_stats' ) {
            $stats = PluginContainer::get( Database::class )->get_quizle_stats( $post_id );
            echo '<div class="quizle_stats__grid">';
            printf( '<div class="quizle_stats__row"><div class="quizle_stats__title">%s:</div><div class="quizle_stats__value">%d</div></div>',
                __( 'Results Count', QUIZLE_TEXTDOMAIN ),
                $stats->result_count
            );
            printf( '<div class="quizle_stats__row"><div class="quizle_stats__title">%s:</div><div class="quizle_stats__value">%d</div></div>',
                __( 'Registered Users Count', QUIZLE_TEXTDOMAIN ),
                $stats->registered_users_count
            );
            printf( '<div class="quizle_stats__row"><div class="quizle_stats__title">%s:</div><div class="quizle_stats__value">%d</div></div>',
                __( 'Unique Users Count', QUIZLE_TEXTDOMAIN ),
                $stats->unique_users_count
            );
            echo '</div>';
        }

        if ( $column_key === 'quizle_shortcode' ) {
            printf( '<div style="cursor: pointer"><pre class="js-quizle-grid-shortcode">[quizle name="%s"]</pre></div>', esc_html( $post ? $post->post_name : '' ) );
        }
    }
}
