<?php

/**
 * @version 1.4.0
 */

defined( 'WPINC' ) || die;

/**
 * @var array $args
 */
$classes = 'quizle-image-screen';
$style   = '';

if ( ! empty( $args['image'] ) ) {
    $classes .= ' quizle-image-screen--img-position-' . $args['image_position'];
    if ( $args['image_position'] == 'background' ) {
        $style = 'background-image: url(\'' . $args['image'] . '\');';
    }
}
?>
<div class="<?php echo $classes ?>" style="<?php echo $style ?>">
    <?php if ( ! empty( $args['image'] ) && $args['image_position'] !== 'background' ): ?>
        <div class="quizle-image-screen__image">
            <img src="<?php echo esc_attr( $args['image'] ) ?>" alt="">
        </div>
    <?php endif ?>
    <div class="quizle-image-screen__container">
        <div class="quizle-image-screen__body">
            <?php if ( ! empty( $args['title'] ) ): ?>
                <div class="quizle-image-screen__title"><?php echo esc_html( $args['title'] ) ?></div>
            <?php endif ?>
            <?php if ( ! empty( $args['text'] ) ): ?>
                <div class="quizle-image-screen__description"><?php echo $args['text'] ?></div>
            <?php endif ?>
        </div>
    </div>
</div>
