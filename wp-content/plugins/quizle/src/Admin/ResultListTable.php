<?php

namespace Wpshop\Quizle\Admin;

use DateTimeImmutable;
use WP_List_Table;
use Wpshop\Quizle\Data\ResultData;
use Wpshop\Quizle\Db\Database;
use Wpshop\Quizle\PluginContainer;
use Wpshop\Quizle\Quizle;
use function Wpshop\Quizle\container;
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
            'remove'     => __( 'Remove', QUIZLE_TEXTDOMAIN ),
            'export_csv' => __( 'Export CSV', QUIZLE_TEXTDOMAIN ),
        ];
    }

    /**
     * @param $which
     *
     * @return void
     */
    protected function extra_tablenav( $which ) {
        if ( 'top' !== $which ) {
            return;
        }

        $base_url = add_query_arg( [
            'post_type' => Quizle::POST_TYPE,
            'page'      => 'quizle-results',
        ], admin_url( 'edit.php' ) );

        $current_quizle_id = $_GET['quizle_id'] ?? '';

        $status_labels = [
            'draft'   => _x( 'Draft', 'list_table', QUIZLE_TEXTDOMAIN ),
            'publish' => _x( 'Publish', 'list_table', QUIZLE_TEXTDOMAIN ),
        ];
        $opt_items     = [];
        foreach ( $this->get_quizles_for_options() as $quizle ) {
            $opt_items[ $status_labels[ $quizle->post_status ] ?: $quizle->post_status ][] = [
                'label' => $quizle->post_title ?: __( '(no title)', QUIZLE_TEXTDOMAIN ),
                'value' => $quizle->ID,
            ];
        }

        ?>
        <div class="alignleft actions" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap">
            <select name="quizle_id" id="filter-quizle-results-by-quizle">
                <option value=""><?php echo __( '-- filter by quizle --', QUIZLE_TEXTDOMAIN ) ?></option>
                <?php if ( count( $opt_items ) > 1 ): ?>
                    <?php foreach ( $opt_items as $optgroup_label => $items ): ?>
                        <optgroup label="<?php echo $optgroup_label ?>">
                            <?php foreach ( $items as $item ): ?>
                                <option value="<?php echo $item['value'] ?>"<?php selected( $current_quizle_id, $item['value'] ) ?>><?php echo $item['label'] ?></option>
                            <?php endforeach ?>
                        </optgroup>
                    <?php endforeach ?>
                <?php else: ?>
                    <?php foreach ( $opt_items as $optgroup_label => $items ): ?>
                        <?php foreach ( $items as $item ): ?>
                            <option value="<?php echo $item['value'] ?>"<?php selected( $current_quizle_id, $item['value'] ) ?>><?php echo $item['label'] ?></option>
                        <?php endforeach ?>
                    <?php endforeach ?>
                <?php endif ?>
            </select>
            <label>
                <input type="hidden" name="finished" value="0">
                <input type="checkbox" name="finished" value="1"<?php checked( $_GET['finished'] ?? '' ) ?> id="filter-quizle-results-finished">
                <?php echo __( 'Only Finished', QUIZLE_TEXTDOMAIN ) ?>
            </label>
            <a href="<?php echo $base_url ?>" class="button" id="filter-quizle-results-btn"><?php echo __( 'Apply Filters', QUIZLE_TEXTDOMAIN ) ?></a>
            <a href="<?php echo $base_url ?>" class=""><?php echo __( 'Reset', QUIZLE_TEXTDOMAIN ) ?></a>
            <script type="text/javascript">
                document.getElementById('filter-quizle-results-btn').addEventListener('click', function (e) {
                    e.preventDefault();
                    var url = new URL(e.target.getAttribute('href'));
                    var quizleId = document.getElementById('filter-quizle-results-by-quizle').value;
                    if (quizleId) {
                        url.searchParams.append('quizle_id', quizleId);
                    }
                    if (document.getElementById('filter-quizle-results-finished').checked) {
                        url.searchParams.append('finished', 1);
                    }

                    window.location.href = url;
                });
            </script>
        </div>
        <?php
    }

    /**
     * @return \Generator|\WP_Post[]
     */
    protected function get_quizles_for_options() {
        $counts = wp_count_posts( Quizle::POST_TYPE );
        $total  = $counts->publish + $counts->draft;

        $per_page = 1000;
        $offset   = 0;

        while ( $offset < $total ) {

            add_filter( 'posts_orderby', $filter = function () {
                return 'post_status ASC, post_title ASC';
            } );

            $posts = get_posts( [
                'post_type'        => Quizle::POST_TYPE,
                'post_status'      => [ 'publish', 'draft' ],
                'posts_per_page'   => $per_page,
                'offset'           => $offset,
                'suppress_filters' => false,
            ] );

            remove_filter( 'posts_orderby', $filter );

            foreach ( $posts as $post ) {
                yield $post;
            }

            $offset += $per_page;
        }
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
        $db = container()->get( Database::class );

        $quizle_id = $_REQUEST['quizle_id'] ?? null;

        $offset      = null;
        $per_page    = apply_filters( 'quizle/result_table/per_page', 20 );
        $total_count = $db->get_quizle_total_count( $quizle_id, ! empty( $_REQUEST['finished'] ) );
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


        $this->items = $db->get_quizle_results_for_list_table(
            $quizle_id,
            $per_page,
            $offset,
            $_REQUEST['orderby'] ?? $orderby,
            $_REQUEST['order'] ?? $order,
            ! empty( $_REQUEST['finished'] )
        );
        $this->set_pagination_args( [
            'total_items' => $total_count,
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

        $types      = container()->get( Quizle::class )->get_types();
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
        $title = get_the_title( $item->quiz_id ) ?: '--';
        if ( $edit_link = get_edit_post_link( $item->quiz_id ) ) {
            return sprintf( '<a href="%s">%s [%s]</a>',
                $edit_link,
                $title,
                $item->quiz_id
            );
        }

        return sprintf( '%s [%s]', $title, $item->quiz_id );
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
