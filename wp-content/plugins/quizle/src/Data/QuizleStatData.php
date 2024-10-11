<?php

namespace Wpshop\Quizle\Data;

/**
 * @property int $result_count
 * @property int $unique_users_count
 * @property int $registered_users_count
 */
class QuizleStatData {

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
