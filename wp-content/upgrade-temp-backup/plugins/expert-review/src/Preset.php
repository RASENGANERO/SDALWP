<?php

namespace Wpshop\ExpertReview;

class Preset {

    const OPTION_PREFIX = '_expert_review_preset:';

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * Preset constructor.
     *
     * @param \wpdb $wpdb
     */
    public function __construct( \wpdb $wpdb ) {
        $this->wpdb = $wpdb;
    }


    /**
     * @return void
     */
    public function init() {
        $this->setup_ajax();

        do_action( __METHOD__, $this );
    }

    /**
     * @return array
     */
    public function get_all_preses() {
        $table      = $this->wpdb->options;
        $opt_prefix = self::OPTION_PREFIX;
        $rows       = $this->wpdb->get_results(
            "SELECT option_value FROM $table WHERE option_name LIKE '{$opt_prefix}%'",
            ARRAY_A
        );

        $to_return = [];
        foreach ( $rows as $row ) {
            $to_return[] = maybe_unserialize( $row['option_value'] );
        }

        return $to_return;
    }

    /**
     * @return void
     */
    protected function setup_ajax() {
        $save_like_action = 'expert_review_preset_save';
        add_action( "wp_ajax_{$save_like_action}", [ $this, 'save_preset_ajax' ] );

        $save_like_action = 'expert_review_preset_remove';
        add_action( "wp_ajax_{$save_like_action}", [ $this, 'remove_preset_ajax' ] );

        $save_like_action = 'expert_review_preset_get';
        add_action( "wp_ajax_{$save_like_action}", [ $this, 'get_preset_ajax' ] );
    }

    /**
     * @return void
     */
    public function save_preset_ajax() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( empty( $_POST['name'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to save unnamed preset', Plugin::TEXT_DOMAIN ) ] );
        }

        $value = [
            'name' => $_POST['name'],
            'data' => $_POST['data'],
        ];

        if ( update_option( self::OPTION_PREFIX . md5( $_POST['name'] ), $value, false ) ) {
            wp_send_json_success();
        }

        wp_send_json_error( [ 'message' => __( 'Unable to save preset', Plugin::TEXT_DOMAIN ) ] );
    }

    /**
     * @return void
     */
    public function remove_preset_ajax() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( empty( $_POST['name'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to remove unnamed preset', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( delete_option( self::OPTION_PREFIX . md5( $_POST['name'] ) ) ) {
            wp_send_json_success();
        }

        wp_send_json_error( [ 'message' => __( 'Unable to remove preset', Plugin::TEXT_DOMAIN ) ] );
    }

    /**
     * @return void
     */
    public function get_preset_ajax() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshop-nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Forbidden', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( empty( $_POST['name'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Unable to retrieve unnamed preset', Plugin::TEXT_DOMAIN ) ] );
        }

        if ( $data = get_option( self::OPTION_PREFIX . md5( $_POST['name'] ) ) ) {
            wp_send_json_success( $data );
        }

        wp_send_json_error( [ 'message' => __( 'Unable to retrieve preset', Plugin::TEXT_DOMAIN ) ] );
    }
}
