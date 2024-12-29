<?php

/**
 * @version 1.8.1
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Settings\AdvancedOptions;
use function Wpshop\ExpertReview\container;

/**
 * @var array    $atts
 * @var callable $nl2br
 * @var string   $expert_name
 * @var string   $expert_description
 * @var string   $expert_avatar
 */

$advanced_options = container()->get( AdvancedOptions::class );

?>

<?php if ( $atts['expert_show'] && (
        ! empty( $atts['expert_name'] ) ||
        ! empty( $atts['expert_text'] ) ||
        ! empty( $atts['expert_id'] ) ||
        ! empty( $atts['expert_user_id'] ) ) ): ?>

    <?php do_action( 'expert_review:before_expert', $atts ); ?>

    <div class="expert-review-expert"<?php echo 'schema' == $advanced_options->expert_microdata_type ? ' itemscope itemtype="https://schema.org/Person"' : '' ?>>
        <?php if ( ! empty( $atts['expert_title'] ) && $atts['expert_show_title'] == 1 ) : ?>
            <div class="expert-review-expert-header"><?php echo $atts['expert_title'] ?></div>
        <?php endif ?>
        <div class="expert-review-expert-bio">
            <div class="expert-review-expert-bio__avatar">
                <?php if ( $expert_avatar ): ?>
                    <?php echo $expert_avatar ?>
                <?php endif ?>
            </div>
            <div class="expert-review-expert-bio__body">
                <div class="expert-review-expert-bio-name"<?php echo 'schema' == $advanced_options->expert_microdata_type ? ' itemprop="name"' : '' ?>><?php echo $expert_name ?></div>
                <div class="expert-review-expert-bio-description"<?php echo 'schema' == $advanced_options->expert_microdata_type ? ' itemprop="description"' : '' ?>><?php echo $expert_description ?></div>
            </div>
            <?php if ( $atts['expert_show_button'] && isset( $button_settings ) ): ?>
                <div class="expert-review-expert-bio__button">
                    <?php echo apply_filters( 'expert_review:question_button', er_ob_get_content( function ( $button_settings, $atts ) {
                        ?>
                        <span class="expert-review-button js-expert-review-button" data-settings="<?php echo $button_settings ?>"><?php echo $atts['expert_question_button_text'] ?></span>
                        <?php
                    }, $button_settings, $atts ) ) ?>
                </div>
            <?php endif ?>
        </div>
        <?php if ( ! empty( $atts['expert_text'] ) ) : ?>
            <div class="expert-review-expert-text">
                <?php echo $nl2br( $atts['expert_text'] ) ?>
            </div>
        <?php endif ?>
    </div><!--.expert-review-expert-->

    <?php do_action( 'expert_review:after_expert', $atts ); ?>
<?php endif ?>
