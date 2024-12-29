<?php

/**
 * @version 1.6.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Plugin;

/**
 * @var array    $atts
 * @var callable $nl2br
 */

$score_symbol = $atts['score_symbol'];
$score_symbol = str_replace( '%%max%%', $atts['score_max'], $score_symbol );

if ( ! empty( $atts['score'] ) ): ?>
    <?php do_action( 'expert_review:before_rating', $atts ); ?>

    <div class="expert-review-score">
        <?php if ( $atts['score_show_title'] && $atts['score_title'] ): ?>
            <div class="expert-review-score-header"><?php echo $atts['score_title'] ?></div>
        <?php endif ?>
        <?php foreach ( $atts['score'] as $score_item ): ?>
            <?php $score_percent = ceil( ( floatval( $score_item['s'] ) * 100 ) / $atts['score_max'] ); ?>
            <div class="expert-review-score-line">
                <div class="expert-review-score-line__name"><?php echo $score_item['n'] ?></div>
                <div class="expert-review-score-line__progress">
                    <div class="expert-review-score-line__progress-container">
                        <div class="expert-review-score-line__progress-fill" style="width: <?php echo $score_percent ?>%"></div>
                    </div>
                </div>
                <div class="expert-review-score-line__score"><?php echo $score_item['s'] ?><?php echo $score_symbol ?></div>
            </div>
        <?php endforeach ?>
        <?php if ( ! empty( $atts['score_summary_text'] ) || ( $atts['score_summary_average'] && count( $atts['score'] ) > 1 ) ): ?>
            <?php
            $score_total = array_reduce( $atts['score'], function ( $sum, $item ) {
                return $sum + floatval( $item['s'] );
            }, 0 );
            $score_avg   = round( $score_total / count( $atts['score'] ), 1 );
            ?>
            <div class="expert-review-score-summary">
                <div class="expert-review-score-summary__label"><?php echo __( 'Result', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="expert-review-score-summary__content">
                    <?php if ( $atts['score_summary_average'] == 1 ) : ?>
                        <div class="expert-review-score-summary__average"><?php echo $score_avg ?></div>
                    <?php endif ?>
                    <?php if ( ! empty( $atts['score_summary_text'] ) ): ?>
                        <div class="expert-review-score-summary__text"><?php echo $nl2br( $atts['score_summary_text'] ) ?></div>
                    <?php endif ?>
                </div>
            </div>
        <?php endif ?>
    </div><!--.expert-review-score-->

    <?php do_action( 'expert_review:after_rating', $atts ); ?>
<?php endif ?>
