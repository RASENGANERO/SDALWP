<?php

namespace Wpshop\Quizle\Db;

use wpdb;
use Wpshop\Quizle\Quizle;

class Upgrade {

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var string
     */
    protected $data_version_opt_name = '_quizle-data-version';

    /**
     * @param Database $database
     */
    public function __construct( wpdb $wpdb, Database $database ) {
        $this->wpdb     = $wpdb;
        $this->database = $database;
    }

    /**
     * @return void
     */
    public function init() {
        $this->database->upgrade();
        add_action( 'init', function () {
            $this->upgrade();
        } );
    }

    /**
     * @return void
     */
    public function upgrade() {
        $old_version = $version = get_option( $this->data_version_opt_name, '1.0' );

        if ( version_compare( $version, '1.0', '<=' ) ) {
            $this->update_quizle_height();
            $version = '1.1';
        }

        if ( $old_version !== $version ) {
            update_option( $this->data_version_opt_name, $version );
        }
    }

    /**
     * @return void
     */
    protected function update_quizle_height() {
        foreach ( $this->get_quizles() as $quizle ) {
            if ( ! metadata_exists( 'post', $quizle->ID, 'quizle-height' ) ) {
                update_post_meta( $quizle->ID, 'quizle-height', '600px' );
            }
        }
    }

    /**
     * @return \Generator|\WP_Post[]
     */
    protected function get_quizles() {
        $total = $this->wpdb->get_var( $this->wpdb->prepare(
            "SELECT COUNT(ID) AS count FROM {$this->wpdb->posts} WHERE post_type = %s",
            Quizle::POST_TYPE
        ) );

        $limit  = 1000;
        $offset = 0;

        while ( $offset < $total ) {
            $posts = get_posts( [
                'post_status'    => 'any',
                'post_type'      => 'quizle',
                'orderby'        => 'ID',
                'posts_per_page' => $limit,
                'offset'         => $offset,
            ] );

            foreach ( $posts as $post ) {
                yield $post;
            }

            $offset += $limit;
        }
    }
}
