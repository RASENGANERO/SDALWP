<?php

namespace Wpshop\PluginMyPopup;

class PostGrid {

    /**
     * @return void
     */
    public function init() {
        $post_type = MyPopup::POST_TYPE;
        add_filter( "manage_{$post_type}_posts_columns", [ $this, '_add_columns' ] );
        add_action( "manage_{$post_type}_posts_custom_column", [ $this, '_manage_custom_column' ], 10, 2 );

        add_action( 'admin_enqueue_scripts', [ $this, '_enqueue_scripts' ] );
        if ( wp_doing_ajax() ) {
            $action = 'my_popup_change_status';
            add_action( "wp_ajax_{$action}", [ $this, '_change_popup_status' ] );
        }
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function _add_columns( array $columns ) {
        $before = array_slice( $columns, 0, 2 );
        $after  = array_slice( $columns, 2 );

        return $before + [ 'my_popup_status' => __( 'Status', Plugin::TEXT_DOMAIN ) ] + $after;
    }

    /**
     * @param string $column_key
     * @param int    $post_id
     *
     * @return void
     */
    public function _manage_custom_column( $column_key, $post_id ) {
        if ( $column_key === 'my_popup_status' ) {
            ?>
            <label class="wpshop-meta-checkbox ">
                <input type="checkbox" class="js-my-popup-grid-action" data-post_id="<?php echo $post_id ?>" value="1"<?php checked( get_post_meta( $post_id, 'my_popup_enable', true ) ) ?>>
                <span class="wpshop-meta-checkbox__label"></span>
            </label>
            <?php
        }
    }

    /**
     * @return void
     */
    public function _enqueue_scripts() {
        $post_type = MyPopup::POST_TYPE;
        if ( ! get_current_screen() || get_current_screen()->id !== "edit-{$post_type}" ) {
            return;
        }
        wp_enqueue_script(
            'my-popup-post-grid',
            plugin_dir_url( MY_POPUP_FILE ) . 'assets/admin/js/post-grid.min.js',
            [ 'jquery' ],
            '1.0',
            true
        );
    }

    /**
     * @return void
     */
    public function _change_popup_status() {
        $data = wp_parse_args( $_REQUEST, [
            'post_id' => null,
            'status'  => 0,
        ] );
        if ( $data['post_id'] ) {
            update_post_meta( $data['post_id'], 'my_popup_enable', $data['status'] );
        }
        wp_send_json_success();
    }
}
