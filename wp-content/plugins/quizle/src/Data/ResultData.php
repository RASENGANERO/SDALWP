<?php

namespace Wpshop\Quizle\Data;

/**
 * @property int        $result_id
 * @property int        $quiz_id
 * @property int        $user_id
 * @property string     $user_coolie
 * @property string     $token
 * @property string     $result_data
 * @property string     $name
 * @property string     $email
 * @property string     $phone
 * @property string     $additional_data
 * @property string     $created_at
 * @property string     $finished_at
 *
 *
 * @property array|null $questions
 */
class ResultData {

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
