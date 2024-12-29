<?php

/**
 * @var array $args
 */

defined( 'WPINC' ) || die;

use function Wpshop\Quizle\sanitize_phone;

/**
 * @version 1.4
 */

/**
 * @var array $args
 */

/** @var \Wpshop\Quizle\QuizleResult $result */
$result = $args['result'];

printf( "*%s*\n\n", esc_html__( 'New Quizle Contacts', 'quizle' ) );
printf( "%s: %s\n", esc_html__( 'Name', 'quizle' ), esc_html( $result->name ) );
printf( "%s: %s\n", esc_html__( 'Email', 'quizle' ), esc_html( $result->email ) );
if ( $result->phone ):
    printf( "[%s](tel:%s)\n", esc_html( $result->phone ), esc_html( $result->phone ) );
endif;
foreach ( $result->get_from_additional_data( 'messengers', [] ) as $messenger => $value ):
    printf( "%s: %s\n", esc_html( $messenger ), esc_html( $value ) );
endforeach;
printf( "\n*%s:*\n\n", esc_html__( 'Result Details', 'quizle' ) );
printf( "%s:\n", esc_html__( 'Answers', 'quizle' ) );
foreach ( $result->get_result_data_questions() as $question ):
    printf( "\n_%s%s_:\n", ( ! empty( $question['conditional'] ) ? '> ' : '' ), esc_html( $question['title'] ) );
    foreach ( $question['answers'] ?? [] as $answer ):
        $answer_type = $answer['type'] ?? 'general';
        if ( '__text__' === $answer['answer_id'] ):
            echo esc_html( $answer['value'] ) . "\n";
        elseif ( '__file__' === $answer['answer_id'] ):
            foreach ( $answer['value'] as $url ):
                printf( "file: [%s](%s)\n", esc_html( $url ), esc_html( $url ) );
            endforeach;
        else:
            if ( ! empty( $answer['image'] ) ):
                printf( "[%s](%s)\n", esc_html( $answer['image'] ), esc_html( $answer['image'] ) );
            endif;
            echo $question['is_multiple'] ? '[' : '(';
            echo ! empty( $answer['_checked'] ) ? 'x' : ' ';
            echo $question['is_multiple'] ? '] ' : ') ';
            if ( $answer_type === 'custom' ):
                printf( "_%s_: %s\n", esc_html__( 'custom answer', 'quizle' ), esc_html( $answer['_custom_answer'] ?? '' ) );
            else:
                echo esc_html( $answer['name'] ) . "\n";
            endif;
        endif;
    endforeach;
endforeach;

if ( $result_arr = $result->get_result_item() ):
    printf( "\n*%s*\n\n", __( 'Result', 'quizle' ) );
    if ( ! empty( $result_arr['redirect_link'] ) ):
        printf( "%s: %s\n", __( 'Redirect Link', 'quizle' ), $result_arr['redirect_link'] );
    endif;
    printf( "_%s_\n\n", $result_arr['title'] ?? '' );
    printf( "%s\n", wp_strip_all_tags( $result_arr['description'] ?? '' ) );
    if ( ! empty( $result_arr['image'] ) ):
        printf( "\nimage: %s\n", $result_arr['image'] );
    endif;
endif;
