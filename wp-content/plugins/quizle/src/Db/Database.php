<?php

namespace Wpshop\Quizle\Db;

use DateTimeInterface;
use Wpshop\Quizle\Data\QuizleStatData;
use Wpshop\Quizle\Data\ResultData;
use Wpshop\Quizle\QuizleResult;
use const Wpshop\Quizle\CACHE_GROUP;

class Database {

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * @var string
     */
    protected $opt_name;

    /**
     * @var string
     */
    protected $version_opt_name;

    /**
     * @var string[]
     */
    protected $results_order_by_columns = [
        'result_id',
        'quiz_id',
        'user_id',
        'name',
        'email',
        'phone',
        'created_at',
    ];

    /**
     * @param \wpdb $wpdb
     */
    public function __construct( $wpdb ) {
        $this->wpdb             = $wpdb;
        $this->opt_name         = '_quizle-db-installed';
        $this->version_opt_name = '_quizle-db-installed-version';
    }

    /**
     * @param QuizleResult $result
     *
     * @return false|QuizleResult|null
     */
    public function insert_quizle_result( QuizleResult $result ) {
        $result->set_created_time();

        for ( $i = 0 ; $i < 100 ; $i ++ ) {
            $result->generate_unique_token();
            if ( false !== $this->wpdb->insert( $this->get_results_table_name(), $result->to_array() ) ) {
                return $this->get_quizle_result( $this->wpdb->insert_id );
            }
        }

        return false;
    }

    /**
     * @param QuizleResult $result
     * @param array        $keys keys to update
     *
     * @return bool
     */
    public function update_quizle_result( QuizleResult $result, array $keys = [] ) {
        $to_update = array_filter( $result->to_array(), function ( $key ) use ( $keys ) {
            return in_array( $key, $keys );
        }, ARRAY_FILTER_USE_KEY );

        if ( empty( $to_update ) ) {
            return false;
        }

        return false !== $this->wpdb->update( $this->get_results_table_name(), $to_update, [ 'result_id' => $result->result_id ] );
    }

    /**
     * @param int $id
     *
     * @return QuizleResult|null
     */
    public function get_quizle_result( $id ) {
        if ( $id > 0 ) {
            $sql = $this->wpdb->prepare( "SELECT * FROM {$this->get_results_table_name()} WHERE result_id = %d", $id );
            if ( $row = $this->wpdb->get_row( $sql, ARRAY_A ) ) {
                return ( new QuizleResult() )->populate( $row );
            }
        }

        return null;
    }

    /**
     * @param int|int[] $id
     *
     * @return int|false
     */
    public function remove_quizle_result( $id ) {
        if ( is_array( $id ) ) {
            $ids = array_filter( $id, 'is_numeric' );
            $ids = array_map( 'absint', $ids );
            $ids = implode( ',', $ids );
            $sql = "DELETE FROM {$this->get_results_table_name()} WHERE result_id IN ($ids)";

            $this->wpdb->query( $sql );

            return ! $this->wpdb->last_error;

        }

        return $this->wpdb->delete( $this->get_results_table_name(), [ 'result_id' => $id ] );
    }

    /**
     * @param string $token
     *
     * @return QuizleResult|null
     */
    public function get_quizle_result_by_token( $token ) {
        if ( false !== ( $result = wp_cache_get( "quizle_result:{$token}", CACHE_GROUP ) ) ) {
            return $result;
        }

        $sql    = $this->wpdb->prepare( "SELECT * FROM {$this->get_results_table_name()} WHERE token = %s", $token );
        $result = null;
        if ( $row = $this->wpdb->get_row( $sql, ARRAY_A ) ) {
            $result = ( new QuizleResult() )->populate( $row );
        }

        wp_cache_add( "quizle_result:{$token}", $result, CACHE_GROUP );

        return $result;
    }

    /**
     * @param int|null    $quizle_id
     * @param int|null    $limit
     * @param int|null    $offset
     * @param string|null $orderby
     * @param string      $order
     * @param bool        $finished
     *
     * @return ResultData[]
     */
    public function get_quizle_results_for_list_table( $quizle_id = null, $limit = null, $offset = null, $orderby = null, $order = 'ASC', $finished = false ) {
        $sql = "SELECT r.*, pm.meta_value as questions FROM {$this->get_results_table_name()} r LEFT JOIN {$this->wpdb->postmeta} pm on r.quiz_id = pm.post_id AND pm.meta_key = 'quizle-questions'";

        $conditions = [];
        if ( $quizle_id ) {
            $conditions[] = 'r.quiz_id = ' . intval( $quizle_id );
        }
        if ( $finished ) {
            $conditions[] = 'r.finished_at IS NOT NULL';
        }

        $where = $conditions ? ' WHERE ' . implode( ' AND ', $conditions ) : '';

        $sql = <<<SQL
SELECT r.*, pm.meta_value as questions FROM {$this->get_results_table_name()} r 
    LEFT JOIN {$this->wpdb->postmeta} pm on r.quiz_id = pm.post_id AND pm.meta_key = 'quizle-questions'
$where
SQL;


        if ( $orderby && in_array( $orderby, $this->results_order_by_columns ) ) {
            $order = strtolower( $order ) === 'desc' ? 'DESC' : 'ASC';
            $sql   .= " ORDER BY r.{$orderby} $order";
        }

        if ( $limit ) {
            $sql .= ' LIMIT ' . absint( $limit );
        }
        if ( $offset ) {
            $sql .= ' OFFSET ' . absint( $offset );
        }

        return array_map( function ( $item ) {
            return new ResultData( $item );
        }, (array) $this->wpdb->get_results( $sql, ARRAY_A ) );
    }

    /**
     * @param int|null $quizle_id
     * @param bool     $finished
     *
     * @return int
     */
    public function get_quizle_total_count( $quizle_id = null, $finished = false ) {
        $quizle_id = intval( $quizle_id );

        $conditions = [];
        if ( $quizle_id ) {
            $conditions[] = 'quiz_id = ' . intval( $quizle_id );
        }
        if ( $finished ) {
            $conditions[] = 'finished_at IS NOT NULL';
        }
        $where = $conditions ? ' WHERE ' . implode( ' AND ', $conditions ) : '';

        return (int) $this->wpdb->get_var( "SELECT count(result_id) FROM {$this->get_results_table_name()}{$where}" );
    }

    /**
     * @param array    $in_ids
     * @param int|null $quizle_id
     * @param bool     $only_finished
     * @param int      $limit
     * @param int      $offset
     *
     * @return array
     */
    public function get_quizle_results(
        array $in_ids = [],
        $quizle_id = null,
        $only_finished = true,
        $limit = 1000,
        $offset = 0,
        $order_by = 'created_at',
        $order = 'DESC'
    ) {
        $conditions = [];

        if ( $in_ids ) {
            $in_ids = array_map( 'intval', $in_ids );
            $in_ids = implode( ',', $in_ids );

            $conditions[] = "r.result_id IN ({$in_ids})";
        } else {
            if ( $only_finished ) {
                $conditions[] = 'r.finished_at IS NOT NULL';
            }
            if ( $quizle_id ) {
                $conditions[] = 'r.quiz_id = ' . intval( $quizle_id );
            }
        }

        $where = $conditions ? 'WHERE ' . implode( ' AND ', $conditions ) : '';

        $order = strtoupper( $order );
        $order = $order === 'DESC' ? $order : 'ASC';

        $order_by = in_array( $order_by, [
            'quizle_title',
            'quizle_name',
            'created_at',
            'finished_at',
        ] ) ? $order_by : 'created_at';

        $sql = <<<SQL
SELECT p.post_title AS quizle_title,
       p.post_name as quizle_name,
       r.name as username,
       r.email,
       r.phone,
       r.result_data,
       r.additional_data,
       r.created_at,
       r.finished_at
FROM {$this->get_results_table_name()} r
    LEFT JOIN {$this->wpdb->posts} p ON r.quiz_id = p.ID
$where
ORDER BY $order_by $order
LIMIT $limit OFFSET $offset
SQL;

        return (array) $this->wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * @param array    $in_ids
     * @param int|null $quizle_id
     * @param bool     $only_finished
     *
     * @return int
     */
    public function get_quizle_results_count( $in_ids = [], $quizle_id = null, $only_finished = true ) {
        $conditions = [];
        if ( $in_ids ) {
            $in_ids = array_map( 'intval', $in_ids );
            $in_ids = implode( ',', $in_ids );

            $conditions[] = "result_id IN ({$in_ids})";
        } else {
            if ( $only_finished ) {
                $conditions[] = 'finished_at IS NOT NULL';
            }
            if ( $quizle_id ) {
                $conditions[] = 'quiz_id = ' . intval( $quizle_id );
            }
        }

        $where = $conditions ? ' WHERE ' . implode( ' AND ', $conditions ) : '';

        return (int) $this->wpdb->get_var( "SELECT count(result_id) FROM {$this->get_results_table_name()}{$where}" );
    }

    /**
     * @param int $quiz_id
     *
     * @return QuizleStatData
     */
    public function get_quizle_stats( $quiz_id ) {
        $sql = $this->wpdb->prepare(
            "SELECT count(result_id) as result_count, count(DISTINCT user_cookie) as unique_users_count FROM {$this->get_results_table_name()} WHERE quiz_id = %d",
            $quiz_id
        );

        $data = $this->wpdb->get_row( $sql, ARRAY_A );

        $data += $this->wpdb->get_row( $this->wpdb->prepare(
            "SELECT count(DISTINCT user_id) as registered_users_count  FROM {$this->get_results_table_name()} WHERE quiz_id = %d AND user_id <> 0",
            $quiz_id
        ), ARRAY_A );

        return new QuizleStatData( $data );
    }

    /**
     * @param int               $quizle_id
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     *
     * @return array
     */
    public function get_quizle_created_stat( $quizle_id, DateTimeInterface $from, DateTimeInterface $to ) {
        $sql = <<<"SQL"
SELECT count(result_id) as count, DATE_FORMAT(created_at, "%%Y-%%m-%%d") as date
FROM {$this->get_results_table_name()}
WHERE quiz_id = %d AND created_at >= '%s' AND created_at <= '%s'
GROUP BY DATE_FORMAT(created_at, "%%y-%%m-%%d")
ORDER BY result_id ASC

SQL;

        $sql = $this->wpdb->prepare( $sql, $quizle_id, $from->format( 'Y-m-d H:i:s' ), $to->format( 'Y-m-d H:i:s' ) );

        $result = [];
        $rows   = $this->wpdb->get_results( $sql, ARRAY_A );
        foreach ( $rows as $row ) {
            $result[ $row['date'] ] = $row['count'];
        }

        return $result;
    }

    /**
     * @param int               $quizle_id
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     *
     * @return array
     */
    public function get_quizle_finished_stat( $quizle_id, DateTimeInterface $from, DateTimeInterface $to ) {
        $sql = <<<"SQL"
SELECT count(result_id) as count, DATE_FORMAT(finished_at, "%%Y-%%m-%%d") as date
FROM {$this->get_results_table_name()}
WHERE quiz_id = %d AND finished_at >= '%s' AND finished_at <= '%s'
GROUP BY DATE_FORMAT(finished_at, "%%y-%%m-%%d")
ORDER BY result_id ASC

SQL;

        $sql = $this->wpdb->prepare( $sql, $quizle_id, $from->format( 'Y-m-d H:i:s' ), $to->format( 'Y-m-d H:i:s' ) );

        $result = [];
        $rows   = $this->wpdb->get_results( $sql, ARRAY_A );
        foreach ( $rows as $row ) {
            $result[ $row['date'] ] = $row['count'];
        }

        return $result;
    }

    /**
     * @param                   $quizle_id
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     *
     * @return \Generator
     */
    public function aggregate_quizle_results( $quizle_id, DateTimeInterface $from, DateTimeInterface $to ) {
        $sql = $this->wpdb->prepare(
            "SELECT count(result_id) FROM {$this->get_results_table_name()} WHERE quiz_id=%d AND created_at >= '%s' AND created_at <= '%s'",
            $quizle_id,
            $from->format( 'Y-m-d H:i:s' ),
            $to->format( 'Y-m-d H:i:s' )
        );

        $total = $this->wpdb->get_var( $sql );

        $limit  = 1000;
        $offset = 0;

        do {
            $sql  = $this->wpdb->prepare(
                "SELECT * FROM {$this->get_results_table_name()} WHERE quiz_id=%d AND created_at >= '%s' AND created_at <= '%s' LIMIT %d OFFSET %d",
                $quizle_id,
                $from->format( 'Y-m-d H:i:s' ),
                $to->format( 'Y-m-d H:i:s' ),
                $limit,
                $offset
            );
            $rows = $this->wpdb->get_results( $sql, ARRAY_A );
            foreach ( $rows as $row ) {
                yield $row;
            }
            $offset += $limit;
        } while ( $offset < $total );
    }

    /**
     * @return string
     */
    public function get_results_table_name() {
        return $this->wpdb->prefix . 'quizle_results';
    }

    /**
     * @return void
     */
    public function upgrade() {
        $old_version = $version = get_option( $this->version_opt_name, '1.0' );

        if ( version_compare( $version, '1.0', '<=' ) ) {
            $idx_prefix = 'quizle_results_';
            $this->wpdb->query( "CREATE INDEX {$idx_prefix}quiz_id ON {$this->get_results_table_name()} (quiz_id)" );
            $this->wpdb->query( "CREATE INDEX {$idx_prefix}user_id ON {$this->get_results_table_name()} (user_id)" );
            $this->wpdb->query( "CREATE INDEX {$idx_prefix}user_cookie ON {$this->get_results_table_name()} (user_cookie)" );
            $this->wpdb->query( "CREATE UNIQUE INDEX {$idx_prefix}token ON {$this->get_results_table_name()} (token)" );

            $version = '1.1';
        }

        if ( version_compare( $version, '1.1', '<=' ) ) {
            $this->wpdb->query( "ALTER TABLE {$this->get_results_table_name()} ADD context TEXT AFTER result_data" );
            $version = '1.2';
        }

        if ( $old_version !== $version ) {
            update_option( $this->version_opt_name, $version );
        }
    }

    /**
     * @return void
     */
    public function install() {
        if ( get_option( $this->opt_name ) ) {
            return;
        }

        $table_name      = $this->get_results_table_name();
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = <<<"SQL"
CREATE TABLE IF NOT EXISTS $table_name (
    result_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    quiz_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
    user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
    user_cookie VARCHAR(32) NOT NULL DEFAULT '',
    token VARCHAR(32) NOT NULL,
    result_data LONGTEXT NULL,
    name VARCHAR(255) NULL,
    email VARCHAR(100) NOT NULL DEFAULT '',
    phone VARCHAR(100) NULL,
    additional_data TEXT NULL,
    created_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    finished_at DATETIME NULL,
    PRIMARY KEY  (result_id)
) $charset_collate;
SQL;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( $this->opt_name, 1 );
        update_option( $this->version_opt_name, '1.0' );
    }

    /**
     * @return void
     */
    public function uninstall() {
        $this->wpdb->query( "DROP TABLE IF EXISTS {$this->get_results_table_name()}" );
        delete_option( $this->opt_name );
        delete_option( $this->version_opt_name );
    }
}
