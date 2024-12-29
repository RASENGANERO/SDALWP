<?php

/**
 * @version 1.3.0
 */

defined( 'WPINC' ) || die;

/**
 * @var array $args
 */

/** @var \Wpshop\Quizle\QuizleResult $result */
$result = $args['result']
?>

<div>
    <?php if ( $result_arr = $result->get_result_item() ): ?>
        <h1><?php esc_html_e( $result_arr['title'] ); ?></h1>
        <?php if ( $result_arr['description'] ): ?>
            <p><?php esc_html_e( $result_arr['description'] ); ?></p>
        <?php endif ?>
        <?php if ( $result_arr['image'] ): ?>
            <img src="<?php esc_attr_e( $result_arr['image'] ); ?>" alt="">
        <?php endif ?>
    <?php endif ?>
</div>
