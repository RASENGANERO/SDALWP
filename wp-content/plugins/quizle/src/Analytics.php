<?php

namespace Wpshop\Quizle;

use DateInterval;
use DatePeriod;
use DateTimeInterface;
use Wpshop\Quizle\Data\AnalyticsStats;
use Wpshop\Quizle\Db\Database;

class Analytics {

    /**
     * @var Database
     */
    protected $db;

    /**
     * @param Database $db
     */
    public function __construct( Database $db ) {
        $this->db = $db;
    }

    /**
     * @return void
     */
    public function init() {
        add_filter( 'post_row_actions', [ $this, '_add_grid_action_link' ], 10, 2 );
    }

    /**
     * @param array    $actions
     * @param \WP_Post $post
     *
     * @return array
     */
    public function _add_grid_action_link( $actions, $post ) {
        if ( $post->post_type !== Quizle::POST_TYPE ) {
            return $actions;
        }
        $actions['analytics'] = sprintf( '<a href="%s">%s</a>',
            get_quizle_analytic_url( $post->ID ),
            __( 'Analytics', QUIZLE_TEXTDOMAIN )
        );

        return $actions;
    }

    /**
     * @param int               $quizle_id
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     *
     * @return AnalyticsStats
     */
    public function get_created_of_current_month( $quizle_id, DateTimeInterface $from, DateTimeInterface $to ) {
        $created  = $this->db->get_quizle_created_stat( $quizle_id, $from, $to );
        $finished = $this->db->get_quizle_finished_stat( $quizle_id, $from, $to );

        $dates           = [];
        $created_counts  = [];
        $finished_counts = [];
        $conversion      = [];

        $total_created  = 0;
        $total_finished = 0;

        $period = new DatePeriod( $from, new DateInterval( 'P1D' ), $to );
        foreach ( $period as $period_item ) {
            $date = $period_item->format( 'Y-m-d' );

            $created_counts[]  = $created_count = array_key_exists( $date, $created ) ? (int) $created[ $date ] : 0;
            $finished_counts[] = $finished_count = array_key_exists( $date, $finished ) ? (int) $finished[ $date ] : 0;
            $conversion[]      = round( $created_count ? $finished_count / $created_count * 100 : 0, 1 );

            $total_created  += $created_count;
            $total_finished += $finished_count;

            $dates[] = date_i18n( 'j M \'y', $period_item->getTimestamp() );
        }

        $total_conversion = round( $total_created ? $total_finished / $total_created * 100 : 0, 1 );

        return new AnalyticsStats( compact(
            'dates',
            'created_counts',
            'finished_counts',
            'conversion',
            'total_created',
            'total_finished',
            'total_conversion'
        ) );
    }

    /**
     * @param int               $quizle_id
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     *
     * @return array
     */
    public function get_answers_data( $quizle_id, DateTimeInterface $from, DateTimeInterface $to ) {
        $total_results = 0;
        $aggregated    = [];
        foreach ( $this->db->aggregate_quizle_results( $quizle_id, $from, $to ) as $item ) {
            $total_results ++;
            if ( $item['result_data'] ) {
                $result_data = json_decode( $item['result_data'], true );
                if ( json_last_error() !== JSON_ERROR_NONE ) {
                    continue;
                }

                $questions = $result_data['questions'] ?? [];
                foreach ( $questions as $question ) {
//                    var_dump( $question );
                    if ( ! array_key_exists( $question['question_id'], $aggregated ) ) {
                        $aggregated[ $question['question_id'] ] = [
                            'title'   => $question['title'],
                            'answers' => [],
                        ];
                    }

                    $answers = $question['answers'] ?? [];
                    foreach ( $answers as $answer ) {
                        $answer_type = $answer['type'] ?? 'general';


                        if ( ! array_key_exists( $answer['answer_id'], $aggregated[ $question['question_id'] ]['answers'] ) ) {
                            if ( 'custom' === $answer_type ) {
                                $answer_name = '(' . __( 'custom answer', QUIZLE_TEXTDOMAIN ) . ')';
                            } else {
                                $answer_name = $answer['answer_id'] === '__text__' ? __( '(text input)', QUIZLE_TEXTDOMAIN ) : $answer['name'];
                            }
                            $aggregated[ $question['question_id'] ]['answers'][ $answer['answer_id'] ] = [
                                'name'  => $answer_name,
                                'count' => 0,
                                'total' => 0,
                            ];
                        }

                        $aggregated[ $question['question_id'] ]['answers'][ $answer['answer_id'] ]['count'] += ( ( $answer['_checked'] ?? false ) ? 1 : 0 );
                        $aggregated[ $question['question_id'] ]['answers'][ $answer['answer_id'] ]['total'] ++;
                    }
                }
            }
        }

        return $aggregated;
    }
}
