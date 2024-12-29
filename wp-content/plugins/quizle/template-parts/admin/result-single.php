<?php

defined( 'WPINC' ) || die;

use Wpshop\Quizle\QuizleResult;
use function Wpshop\Quizle\sanitize_phone;

/**
 * @var array $args
 */

/**
 * @var QuizleResult|null $result
 */
$result = $args['result'];

if ( ! $result ) {
    echo __( 'Unable to show result data', QUIZLE_TEXTDOMAIN );

    return;
}

$quizle = get_post( $result->quiz_id );

$started     = '';
$finished    = '';
$finish_time = '';
$started_at  = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $result->created_at, wp_timezone() );
$started     = sprintf(
    __( '%1$s at %2$s' ),
    date_i18n( get_option( 'date_format' ), $started_at->getTimestamp() ),
    date_i18n( get_option( 'time_format' ), $started_at->getTimestamp() )
);
if ( $result->finished_at ) {
    $finished_at = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $result->finished_at, wp_timezone() );
    $finished    = sprintf(
        __( '%1$s at %2$s' ),
        date_i18n( get_option( 'date_format' ), $finished_at->getTimestamp() ),
        date_i18n( get_option( 'time_format' ), $finished_at->getTimestamp() )
    );

    $finish_time = human_time_diff( $started_at->getTimestamp(), $finished_at->getTimestamp() );
}
?>

<div class="wrap">

    <div class="quizle-admin-result-breadcrumbs">
        <a href="<?php echo admin_url( 'edit.php?post_type=quizle&page=quizle-results' ) ?>">&larr; <?php esc_html_e( 'Back to results', QUIZLE_TEXTDOMAIN ); ?></a>
    </div>
    <h1><?php esc_html_e( 'Result', QUIZLE_TEXTDOMAIN ) ?> #<?php echo $result->result_id ?>
        <a href="<?php echo get_edit_post_link( $result->quiz_id ) ?: '#' ?>"><?php esc_html_e( get_the_title( $result->quiz_id ) ?: '--' ) ?></a>
    </h1>

    <div class="quizle-admin-result-info">
        <div>
            <?php
            if ( $result->finished_at ) {
                echo _x( 'Finished', 'quizle result', QUIZLE_TEXTDOMAIN ); // Завершен
            } else {
                echo _x( 'Not Finished', 'quizle result', QUIZLE_TEXTDOMAIN ); // Не завершен
            }
            ?>
        </div>

        <div>
            <?php echo __( 'Started', QUIZLE_TEXTDOMAIN ) ?>: <?php echo $started ?>
            <?php echo __( 'Finished', QUIZLE_TEXTDOMAIN ) ?>: <?php echo $finished ?>
        </div>

        <div>
            <?php echo __( 'Name', QUIZLE_TEXTDOMAIN ) ?>: <?php esc_html_e( $result->name ) ?><br>
            <?php echo __( 'Email', QUIZLE_TEXTDOMAIN ) ?>:
            <?php if ( $result->email ): ?>
                <a href="mailto:<?php echo sanitize_email( $result->email ) ?>"><?php echo $result->email ?></a>
            <?php endif ?>
            <br>
            <?php echo __( 'Phone', QUIZLE_TEXTDOMAIN ) ?>:
            <?php if ( $result->phone ): ?>
                <a href="tel:<?php echo sanitize_phone( $result->phone ) ?>"><?php echo $result->phone ?></a>
            <?php endif ?>
            <br>
            <?php foreach ( $result->get_from_additional_data( 'messengers', [] ) as $messenger => $value ): ?>
                <?php if ( $value ): ?>
                    <?php echo esc_html( $messenger ) ?>: <?php echo $value ?><br>
                <?php endif ?>
            <?php endforeach ?>
        </div>

        <?php
        $context = $result->get_context();
        if ( $context && ! is_wp_error( $context ) ): ?>
            <hr>
            <?php echo __( 'Context', QUIZLE_TEXTDOMAIN ) ?>:
            <div class="quizle-admin-result-context">
                <div class="quizle-admin-result-context">
                    <?php
                    $context_items = array_filter( $context->jsonSerialize(), function ( $key ) {
                        return '_mac' !== $key;
                    }, ARRAY_FILTER_USE_KEY );
                    ?>
                    <?php foreach ( $context_items as $_key => $_value ): ?>
                        <div class="quizle-admin-result-context__item">
                            <div class="quizle-admin-result-context__item-key"><?php echo esc_html( $_key ) ?></div>
                            <div class="quizle-admin-result-context__item-value"><?php echo esc_html( $_value ) ?></div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endif ?>

    </div>

    <div class="quizle-admin-result-answers">
        <div class="quizle-admin-result-header"><?php esc_html_e( 'Answers', QUIZLE_TEXTDOMAIN ); ?>:</div>
        <?php
        $answer_value_title = __( 'bound value', QUIZLE_TEXTDOMAIN );
        if ( 'variable' === ( $result->get_result_data_array()['quizle_type'] ?? '' ) ) {
            $answer_value_title = __( 'bound result', QUIZLE_TEXTDOMAIN );
        }
        ?>
        <?php foreach ( $result->get_result_data_questions() as $question ): ?>
            <div class="quizle-admin-result-answer">
                <div class="quizle-admin-result-answer__title">
                    <?php if ( ! empty( $question['conditional'] ) ): ?>
                        <span>&gt;</span>
                    <?php endif ?>
                    <?php echo esc_html( $question['title'] ) ?>:
                </div>

                <?php foreach ( $question['answers'] ?? [] as $answer ): ?>
                    <?php $answer_type = $answer['type'] ?? 'general' ?>
                    <div class="quizle-admin-result-answer__item">
                        <?php if ( '__text__' === $answer['answer_id'] ): ?>
                            <?php esc_html_e( $answer['value'] ); ?>
                            <?php //echo $answer['_checked'] ?? false ? 'checked' : 'not checked'; ?>
                        <?php elseif ( '__file__' === $answer['answer_id'] ): ?>
                            <ul>
                                <?php foreach ( $answer['value'] as $url ): ?>
                                    <li>
                                        <a href="<?php echo esc_attr( $url ) ?>" target="_blank" rel="noopener"><?php echo esc_html( $url ) ?></a>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        <?php else: ?>
                            <?php

                            $wrong_class = '';
                            if ( $answer['value'] == 0 && ( $answer['_checked'] ?? false ) ) {
                                $wrong_class = ' wrong';
                            }

                            $answer_classes = [
                                'quizle-admin-result-answer__check',
                                'quizle-admin-result-answer--' . ( $question['is_multiple'] ? 'checkbox' : 'radio' ),
                            ];
                            if ( ! empty( $answer['_checked'] ) ) {
                                $answer_classes[] = 'checked';
                            }
                            if ( $answer['value'] == 0 && ( $answer['_checked'] ?? false ) ) {
                                $answer_classes[] = 'wrong';
                            } ?>

                            <span class="<?php echo implode( ' ', $answer_classes ) ?>"></span>
                            <?php if ( ! empty( $answer['image'] ) ): ?>
                                <img src="<?php echo $answer['image'] ?>" alt="" width="50" height="50">
                            <?php endif ?>
                            <span>
                                <?php if ( 'custom' === $answer_type ): ?>
                                    <i><?php echo __( 'custom answer', QUIZLE_TEXTDOMAIN ) ?>:</i>
                                    <?php echo esc_html( $answer['_custom_answer'] ?? '' ) ?>
                                <?php else: ?>
                                    <?php esc_html_e( $answer['name'] ); ?>
                                <?php endif ?>
                            </span>
                            <sup class="quizle-admin-result-answer__value" title="<?php echo $answer_value_title ?>"><?php esc_html_e( $answer['value'] ); ?></sup>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endforeach ?>
    </div>

    <div class="quizle-admin-result-text">
        <div class="quizle-admin-result-header"><?php echo __( 'Result', QUIZLE_TEXTDOMAIN ) ?>:</div>
        <?php if ( $result_arr = $result->get_result_item() ): ?>
            <?php if ( ! empty( $result_arr['redirect_link'] ) ): ?>
                <?php echo __( 'Redirect Link', QUIZLE_TEXTDOMAIN ) ?>:
                <?php echo $result_arr['redirect_link'] ?>
            <?php endif ?>
            <strong><?php echo esc_html( $result_arr['title'] ?? '' ); ?></strong>
            <?php echo wp_kses_post( $result_arr['description'] ?? '' ); ?>
            <?php if ( ! empty( $result_arr['image'] ) ): ?>
                <img src="<?php echo esc_attr( $result_arr['image'] ); ?>" alt="" width="300">
            <?php endif ?>
        <?php endif ?>
    </div>

</div><!--.wrap-->
