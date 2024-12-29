<?php

/**
 * @version 1.8.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\PluginContainer;
use Wpshop\ExpertReview\Settings\QaOptions;

/**
 * @var array    $atts
 * @var callable $nl2br
 */

$pluses  = $atts['pluses'];
$minuses = $atts['minuses'];

$show_general_title = apply_filters( 'expert_review:show_general_title', $atts['pluses_minuses_show_title'], $atts );
$show_pluses_title  = apply_filters( 'expert_review:show_pluses_title', 1, $atts );
$show_minuses_title = apply_filters( 'expert_review:show_minuses_title', 1, $atts );

$qa_options = PluginContainer::get( QaOptions::class );

$tag = apply_filters( 'expert_review/plus_minus/header_tag', $qa_options->pluses_header_tag ?: 'div', $atts );
?>

<?php if ( ! empty( $pluses ) || ! empty( $minuses ) ): ?>
    <?php do_action( 'expert_review:before_plus_minus', $atts ); ?>

    <div class="expert-review-pluses-minuses">
        <?php if ( ! empty( $atts['pluses_minuses_title'] ) && $show_general_title ): ?>
            <<?php echo $tag ?> class="expert-review-pluses-minuses-header"><?php echo $atts['pluses_minuses_title'] ?></<?php echo $tag ?>>
        <?php endif ?>
        <?php if ( ! empty( $pluses ) ): ?>
            <div class="expert-review-pluses">
                <?php if ( $atts['pluses_title'] && $show_pluses_title ): ?>
                    <<?php echo $tag ?> class="expert-review-pluses-minuses-header"><?php echo $atts['pluses_title'] ?></<?php echo $tag ?>>
                <?php endif ?>
                <?php foreach ( $pluses as $plus ): ?>
                    <div class="expert-review-plus">
                        <?php echo $plus ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
        <?php if ( ! empty( $minuses ) ): ?>
            <div class="expert-review-minuses">
                <?php if ( $atts['minuses_title'] && $show_minuses_title ): ?>
                    <<?php echo $tag ?> class="expert-review-pluses-minuses-header"><?php echo $atts['minuses_title'] ?></<?php echo $tag ?>>
                <?php endif ?>
                <?php foreach ( $minuses as $minus ): ?>
                    <div class="expert-review-minus">
                        <?php echo $minus ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div><!--.expert-review-pluses-minuses-->

    <?php do_action( 'expert_review:after_plus_minus', $atts ); ?>
<?php endif ?>
