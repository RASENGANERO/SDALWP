<?php

namespace Wpshop\Quizle\Data;

/**
 * @property string[] $dates
 * @property int[]    $created_counts
 * @property int[]    $finished_counts
 * @property float[]  $conversion
 *
 * @property int      $total_created
 * @property int      $total_finished
 * @property float    $total_conversion
 */
class AnalyticsStats {

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct( array $data ) {
        $this->data = $data;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get( $name ) {
        return $this->data[ $name ] ?? null;
    }
}
