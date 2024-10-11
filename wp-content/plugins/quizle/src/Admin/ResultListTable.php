<?php

namespace Wpshop\Quizle\Admin;

use DateTimeImmutable;
use WP_List_Table;
use Wpshop\Quizle\Data\ResultData;
use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use function Wpshop\Quizle\get_edit_quizle_result_url;
use function Wpshop\Quizle\get_quizle_result_url;
use function Wpshop\Quizle\get_quizle_type;

class ResultListTable extends WP_List_Table {

    /**
     * @param array $args
     */
    public function __construct( $args = [] ) {
        $args['ajax'] = true;
        parent::__construct( $args );
    }

    /**
     * @inheridoc
     */
    protected function get_bulk_actions() {
        return [
            'remove' => __( 'Remove', QUIZLE_TEXTDOMAIN ),
            //            'export_csv' => __( 'Export CSV', QUIZLE_TEXTDOMAIN ),
        ];
    }

    /**
     * @inheridoc
     */
    protected function get_sortable_columns() {
        return [
            'info'       => [ 'result_id', true ],
            'quiz_id'    => 'quiz_id',
            'created_at' => [ 'created_at', true ],
        ];
    }

    /**
     * @inheridoc
     */
    public function get_columns() {
        return [
            'cb'          => '<input type="checkbox" />',
            'info'        => __( 'Info', QUIZLE_TEXTDOMAIN ),
            'quiz_id'     => __( 'Quiz', QUIZLE_TEXTDOMAIN ),
            'user_info'   => __( 'User Info', QUIZLE_TEXTDOMAIN ),
            'created_at'  => __( 'Created At', QUIZLE_TEXTDOMAIN ),
            'finished_at' => __( 'Finished At', QUIZLE_TEXTDOMAIN ),
        ];
    }

    /**
     * @inheridoc
     */
    public function ajax_user_can() {
        return current_user_can( 'edit_posts' );
    }

    /**
     * @inheridoc
     */
    public function prepare_items() {
        $db = PluginContainer::get( Database::class );

        $offset      = null;
        $per_page    = apply_filters( 'quizle/result_table/per_page', 20 );
        $total_count = $db->get_quizle_total_count();
        if ( $per_page < $total_count ) {
            $offset = ( $this->get_pagenum() - 1 ) * $per_page;
        }

        $firs_sortable_key = array_key_first( $this->get_sortable_columns() );

        $firs_sortable = $this->get_sortable_columns()[ $firs_sortable_key ];
        if ( is_array( $firs_sortable ) ) {
            [ $orderby, $order ] = $firs_sortable;
            if ( is_bool( $order ) ) {
                $order = $order ? 'desc' : 'asc';
            }
        } else {
            $orderby = $firs_sortable;
            $order   = 'asc';
        }

        $this->items = $db->get_quizle_results_for_list_table( $per_page, $offset, $_REQUEST['orderby'] ?? $orderby, $_REQUEST['order'] ?? $order );
        $this->set_pagination_args( [
            'total_items' => $db->get_quizle_total_count(),
            'per_page'    => $per_page,
        ] );
    }

    /**
     * @param ResultData $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="result_id[]" value="%d" />', $item->result_id
        );
    }

    /**
     * @param ResultData $item
     *
     * @return string
     */
    function column_info( $item ) {
        $result_data = (array) \Wpshop\Quizle\json_decode( $item->result_data );

        $types      = PluginContainer::get( Quizle::class )->get_types();
        $empty_type = $types[ get_quizle_type( $item->quiz_id ) ] ?? [
                'icon'  => '-',
                'title' => '',
            ];

        $result = '<strong>';

        if ( $item->finished_at ) {
            $result .= '<span class="dashicons dashicons-saved" style="color: #00c400;font-weight: 700;"></span>';
        } else {
            $result .= '<span class="dashicons dashicons-no" style="color:orange;"></span>';
        }
        if ( $type = $types[ $result_data['quizle_type'] ?? null ] ?? $empty_type ) {
            $result .= '<span class="quizle-results-grid__icon" title="' . esc_attr( $type['title'] ) . '">';
            $result .= $type['icon'];
            $result .= '</span>';
        }
        $result .= sprintf( '<a class="row-title" href="%s">%s [%d]</a>', get_edit_quizle_result_url( $item->result_id ), __( 'Result', QUIZLE_TEXTDOMAIN ), $item->result_id );
        $result .= '</strong>';


        $result .= '<div class="row-actions">';

        $result .= sprintf( '<span class="edit"><a href="%s">%s</a> | </span>', get_edit_quizle_result_url( $item->result_id ), _x( 'View', 'list_table', QUIZLE_TEXTDOMAIN ) );
        $result .= sprintf( '<span class="trash"><a href="%s" class="submitdelete">%s</a></span>', QuizleResultActions::get_remove_url( $item->result_id ), __( 'Remove' ) );

        if ( in_array( get_quizle_type( $item->quiz_id ), [ Quizle::TYPE_VARIABLE, Quizle::TYPE_TEST ] ) ) {
            $result .= sprintf(
                ' | <span class="view"><a href="%s" rel="bookmark" >%s</a></span>',
                get_quizle_result_url( $item->token, $item->quiz_id ),
                __( 'View' )
            );
        }

        $result .= '</div>';


        return $result;
    }

    /**
     * @param ResultData $item
     *
     * @return string
     */
    function column_user_info( $item ) {
        $result = '';

        if ( $user_id = $item->user_id ) {
            $user   = get_user_by( 'ID', $user_id );
            $result .= sprintf( '<span class="dashicons dashicons-admin-users"></span> <a href="%s">%s</a>', get_edit_user_link( $user_id ), $user->user_email );
        }

        foreach (
            [
                'name'  => __( 'Name', QUIZLE_TEXTDOMAIN ),
                'email' => __( 'Email', QUIZLE_TEXTDOMAIN ),
                'phone' => __( 'Phone', QUIZLE_TEXTDOMAIN ),
            ] as $key => $label
        ) {
            if ( $value = $item->{$key} ) {
                $result .= '<div>';
                $result .= esc_html( $label );
                $result .= ': <strong>' . esc_html( $value ) . '</strong>';
                $result .= '</div>';
            }
        }

        return $result;
    }

    /**
     * @param ResultData $item
     *
     * @return string
     */
    protected function column_quiz_id( $item ) {
        return sprintf( '<a href="%s">%s [%s]</a>',
            get_edit_post_link( $item->quiz_id ),
            get_the_title( $item->quiz_id ) ?: '--',
            $item->quiz_id
        );
    }

    /**
     * @param ResultData $item
     *
     * @return string
     */
    protected function column_created_at( $item ) {
        $datetime = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $item->created_at );

        return sprintf(
            __( '%1$s at %2$s' ),
            wp_date( __( 'Y/m/d' ), $datetime->getTimestamp() ),
            wp_date( __( 'g:i:s a' ), $datetime->getTimestamp() )
        );
    }

    /**
     * @param ResultData $item
     *
     * @return string
     */
    protected function column_finished_at( $item ) {
        if ( ! $item->finished_at ) {
            return '';
        }

        $result = '';

        $datetime = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $item->finished_at );

        $result .= sprintf(
            __( '%1$s at %2$s' ),
            wp_date( __( 'Y/m/d' ), $datetime->getTimestamp() ),
            wp_date( __( 'g:i:s a' ), $datetime->getTimestamp() )
        );

        $finish_time = human_time_diff(
            DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $item->created_at )->getTimestamp(),
            DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $item->finished_at )->getTimestamp()
        );

        if ( $finish_time ) {
            $result .= '<div title="' . __( 'spent time', QUIZLE_TEXTDOMAIN ) . '" style="margin-top: 5px;"><span class="dashicons dashicons-clock"></span> ' . $finish_time . '</div>';
        }

        return $result;
    }

    /**
     * @param ResultData $item
     * @param string     $column_name
     *
     * @return string
     */
    protected function column_default( $item, $column_name ) {
        return esc_html( $item->$column_name );
    }
}
