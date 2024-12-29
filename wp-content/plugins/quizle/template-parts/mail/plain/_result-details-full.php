<?php

/**
 * @version 1.3.0
 */


defined( 'WPINC' ) || die;


/**
 * @var array $args
 */

/** @var \Wpshop\Quizle\QuizleResult $result */
$result = $args['result'];

foreach ( $result->get_result_data_questions() as $question ):
    if ( ! empty( $question['conditional'] ) ):
        echo '> ';
    endif;
    echo esc_html( $question['title'] ) . ':' . PHP_EOL;
    foreach ( $question['answers'] ?? [] as $answer ):
        $answer_type = $answer['type'] ?? 'general';
        if ( '__text__' === $answer['answer_id'] ):
            esc_html__( $answer['value'] );
        elseif ( '__file__' === $answer['answer_id'] ):
            esc_html__( implode( ', ', $answer['value'] ) );
        else:
            if ( ! empty( $answer['image'] ) ):
                echo esc_html( $answer['image'] );
            endif;

            echo $question['is_multiple'] ? '[' : '(';
            echo ! empty( $answer['_checked'] ) ? 'x' : ' ';
            echo $question['is_multiple'] ? '] ' : ') ';

            if ( $answer_type === 'custom' ):
                echo __( 'custom answer', QUIZLE_TEXTDOMAIN ) ?>: <?php echo esc_html( $answer['_custom_answer'] ?? '' );
            else:
                echo esc_html( $answer['name'] );
            endif;
        endif;
        echo PHP_EOL; // end of answers
    endforeach;
    echo PHP_EOL; // end of question
endforeach;
