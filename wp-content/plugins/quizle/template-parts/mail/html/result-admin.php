<?php

/**
 * @version 1.3.0
 */


defined( 'WPINC' ) || die;

use function Wpshop\Quizle\sanitize_phone;

/**
 * @var array $args
 */

/** @var \Wpshop\Quizle\QuizleResult $result */
$result = $args['result'];

?>

<h1><?php esc_html_e( 'New Quizle Contacts', QUIZLE_TEXTDOMAIN ); ?></h1>
<ul>
    <li><?php esc_html_e( 'Name', QUIZLE_TEXTDOMAIN ); ?>: <?php echo $result->name ?></li>
    <li><?php esc_html_e( 'Email', QUIZLE_TEXTDOMAIN ); ?>: <?php echo $result->email ?></li>
    <?php if ( $result->phone ): ?>
        <li><?php esc_html_e( 'Phone', QUIZLE_TEXTDOMAIN ); ?>:
            <a href="tel:<?php echo sanitize_phone( $result->phone ) ?>"><?php echo $result->phone ?></a>
        </li>
    <?php endif ?>
    <?php foreach ( $result->get_from_additional_data( 'messengers', [] ) as $messenger => $value ): ?>
        <li>
            <span style="text-transform: capitalize"><?php echo esc_html( $messenger ) ?></span>: <?php echo $value ?>
        </li>
    <?php endforeach ?>
</ul>

<div>
    <h3><?php echo __( 'Result Details', QUIZLE_TEXTDOMAIN ) ?></h3>
    <div class="quizle-admin-result-answers">
        <div class="quizle-admin-result-header"><?php esc_html_e( 'Answers', QUIZLE_TEXTDOMAIN ); ?>:</div>
        <?php foreach ( $result->get_result_data_questions() as $question ): ?>

            <?php
            $answers = $question['answers'] ?? [];
            $answers = array_filter( $answers, function ( $answer ) {
                if ( '__text__' === $answer['answer_id'] ) {
                    return true;
                }

                $answer_type = $answer['type'] ?? 'general';
                if ( $answer_type === 'custom' ) {
                    return true;
                }

                if ( empty( $answer['_checked'] ) ) {
                    return false;
                }

                return true;
            } );

            if ( ! $answers ) {
                continue;
            }
            ?>

            <div class="quizle-admin-result-answer">
                <div class="quizle-admin-result-answer__title">
                    <?php if ( ! empty( $question['conditional'] ) ): ?>
                        <span>&gt;</span>
                    <?php endif ?>
                    <?php echo esc_html( $question['title'] ) ?>:
                </div>

                <ul>
                    <?php foreach ( $answers as $answer ): ?>
                        <?php $answer_type = $answer['type'] ?? 'general'; ?>
                        <li>
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
                                <?php if ( ! empty( $answer['image'] ) ): ?>
                                    <img src="<?php echo $answer['image'] ?>" alt="" width="50" height="50">
                                <?php endif ?>

                                <?php
                                echo $question['is_multiple'] ? '<span>[' : '<span>(';
                                echo ! empty( $answer['_checked'] ) ? 'x' : '&nbsp;';
                                echo $question['is_multiple'] ? ']</span>' : ')</span>';
                                ?>

                                <?php if ( $answer_type === 'custom' ): ?>
                                    <i><?php echo __( 'custom answer', QUIZLE_TEXTDOMAIN ) ?>:</i>
                                    <?php echo esc_html( $answer['_custom_answer'] ?? '' ); ?>
                                <?php else: ?>
                                    <?php echo esc_html( $answer['name'] ); ?>
                                <?php endif ?>

                            <?php endif ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endforeach ?>
    </div>
</div>

<?php if ( $result_arr = $result->get_result_item() ): ?>
    <div>
        <h3><?php echo __( 'Result Details', 'quizle' ) ?></h3>

        <?php if ( ! empty( $result_arr['redirect_link'] ) ): ?>
            <?php echo __( 'Redirect Link', QUIZLE_TEXTDOMAIN ) ?>:
            <?php echo $result_arr['redirect_link'] ?>
        <?php endif ?>
        <h4><?php echo esc_html( $result_arr['title'] ?? '' ); ?></h4>
        <?php echo wp_kses_post( $result_arr['description'] ?? '' ); ?>
        <?php if ( ! empty( $result_arr['image'] ) ): ?>
            <img src="<?php echo esc_attr( $result_arr['image'] ); ?>" alt="" width="300">
        <?php endif ?>
    </div>
<?php endif ?>