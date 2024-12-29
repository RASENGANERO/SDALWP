<?php

/**
 * @version 1.8.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array $atts
 * @var array $named_likes
 */

?>

<?php
if ( $named_likes ):

    do_action( 'expert_review_like_rate_before', $atts, $named_likes ); ?>

    <div class="expert-review-like-rating expert-review-like-rating--<?php echo esc_attr( $atts['style'] ) ?>">
        <?php if ( ! empty( $atts['title'] ) ): ?>
            <div class="expert-review-like-rating__header"><?php echo $atts['title'] ?></div>
        <?php endif ?>
        <div class="expert-review-like-rating__list">
            <?php foreach ( $named_likes as $item ): ?>
                <?php
                $identity = md5( $item['name'] );
                ?>
                <div class="expert-review-like-rating-item">
                    <div class="expert-review-like-rating-item__position"></div>
                    <div class="expert-review-like-rating-item__text">
                        <?php echo apply_filters( 'expert_review_like_rate:item_text', er_ob_get_content( function ( $item, $text ) {
                            if ( $item['link'] ): ?>
                                <a href="<?php echo $item['link'] ?>"><?php echo $text ?></a>
                            <?php else:
                                echo $text;
                            endif;
                        }, $item, esc_html( $item['text'] ) ), $item, $atts ) ?>
                    </div>
                    <div class="expert-review-like-rating-item__count">
                        <span class="js-expert-review-like-rate" data-name="<?php echo $identity ?>"><?php echo $item['rate'] ?></span>
                        <?php if ( $atts['output_total_score'] ): ?>
                            <span class="js-expert-review-like-activity" data-name="<?php echo $identity ?>">/<?php echo $item['activity'] ?></span>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div><!--.expert-review-like-rate-->

    <?php do_action( 'expert_review_like_rate_after', $atts, $named_likes );

endif;
