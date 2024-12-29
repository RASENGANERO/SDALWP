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
        if ( empty( $answer['_checked'] ) ) {
            continue;
        }

        $answer_type = $answer['type'] ?? 'general';
        if ( '__text__' === $answer['answer_id'] ):
            esc_html__( $answer['value'] );
        elseif ( '__file__' === $answer['answer_id'] ):
            esc_html__( implode( ', ', $answer['value'] ) );
        else:
            if ( ! empty( $answer['image'] ) ):
                echo esc_html( $answer['image'] );
            endif;

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

if ( $result_arr = $result->get_result_item() ):
    echo __( 'Result', 'quizle' );
    echo PHP_EOL;
    if ( ! empty( $result_arr['redirect_link'] ) ):
        echo __( 'Redirect Link', QUIZLE_TEXTDOMAIN ) . ': ' . $result_arr['redirect_link'];
        echo PHP_EOL;
    endif;
    echo esc_html( $result_arr['title'] ?? '' );
    echo PHP_EOL;
    echo wp_strip_all_tags( $result_arr['description'] ?? '' );
    echo PHP_EOL;
    if ( ! empty( $result_arr['image'] ) ):
        echo 'image: ' . $result_arr['image'];
        echo PHP_EOL;
    endif;
endif;
