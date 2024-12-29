<?php

/**
 * @version 1.3.0
 */

defined( 'WPINC' ) || die;

use Wpshop\Quizle\Social;
use function Wpshop\Quizle\build_attributes;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\get_quizle_result_url;

/**
 * @var array $args
 */

/** @var \Wpshop\Quizle\QuizleResult $result */
$result      = $args['result'];
$result_item = $args['result_item'];

$btn_text = $args['btn_text'] ?? apply_filters( 'quizle/result/default_btn_text', __( 'Nice', QUIZLE_TEXTDOMAIN ), $result );
$btn_link = $args['link'] ?: '#';

$social = container()->get( Social::class );

?>
<div class="<?php esc_attr_e( $args['classes'] ) ?>" style="<?php esc_attr_e( $args['style'] ); ?>">
    <?php if ( $args['image'] && $args['image_position'] != 'background' ): ?>
        <div class="quizle-image-screen__image">
            <img src="<?php esc_html_e( $args['image'] ); ?>" alt="">
        </div>
    <?php endif ?>
    <div class="quizle-image-screen__container">
        <div class="quizle-image-screen__body">
            <div class="quizle-image-screen__title"><?php echo apply_filters( 'quizle/result/title', $args['title'], $result ) ?></div>
            <div class="quizle-image-screen__description"><?php echo apply_filters( 'quizle/result/description', $args['description'], $result ) ?></div>
            <?php if ( $btn_text ): ?>
                <div class="quizle-image-screen__button">
                    <?php if ( has_action( 'quizle/result/result_button' ) ): ?>
                        <?php
                        /**
                         * Allows to change output of quizle result button
                         *
                         * @since 1.2
                         */
                        do_action( 'quizle/result/result_button', compact( 'btn_text', 'btn_link', 'result', 'result_item' ) )
                        ?>
                    <?php else: ?>
                        <a class="quizle-button js-quizle-result-button" href="<?php esc_attr_e( $btn_link ) ?>"><?php esc_html_e( $btn_text ); ?></a>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <?php if ( $providers = $social->get_quizle_providers( $result->quiz_id ) ): ?>
            <div class="social-share-providers js-social-share">
                <?php foreach ( $providers as $provider => $item ): ?>
                    <?php
                    $data_attributes = build_attributes( [
                        'data-social'      => $provider,
                        'data-url'         => get_quizle_result_url( $result->token, $result->quiz_id ),
                        'data-title'       => $result_item['title'],
                        'data-description' => $result_item['description'] ?? null,
                        'data-image'       => $result_item['image'] ?: null,
                    ] );
                    ?>
                    <div class="social-share-provider">
                        <a href="#" <?php echo $data_attributes ?> class="social-share-provider__link">
                    <span class="social-share-provider__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 <?php echo "{$item['width']} {$item['height']}" ?>">
                            <path d="<?php echo $item['path'] ?>" fill="currentColor"/>
                        </svg>
                    </span>
                        </a>
                    </div>
                <?php endforeach ?>
                <?php endif ?>
            </div>
        </div>
    </div><!--.quizle-image-screen__container-->
</div>
