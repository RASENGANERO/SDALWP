<?php

/**
 * @version 1.8.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\PluginContainer;
use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Settings\QaOptions;

/**
 * @var array  $atts
 * @var string $content
 */

do_action( 'expert_review:faq_before', $atts );

$use_microdata = ! PluginContainer::get( AdvancedOptions::class )->use_json_ld_faq_microdata;

$qa_options = PluginContainer::get( QaOptions::class );

$q_tag = apply_filters( 'expert_review/faq/question_tag', $qa_options->qa_question_tag ?: 'div', $atts );
$a_tag = apply_filters( 'expert_review/faq/answer_tag', $qa_options->qa_answer_tag ?: 'div', $atts );

?>
    <div class="expert-review-faq expert-review-faq--style-<?php echo esc_attr( $atts['style'] ) ?> expert-review-faq--color-<?php echo esc_attr( $atts['color'] ) ?>">
        <?php if ( $atts['title'] && $atts['show_title'] ): ?>
            <?php if ( has_filter( 'expert_review_faq:wrap_header' ) ): ?>
                <?php echo apply_filters( 'expert_review_faq:wrap_header', $atts['title'], $atts ) ?>
            <?php else: ?>
                <div class="expert-review-faq__header"><?php echo $atts['title'] ?></div>
            <?php endif ?>
        <?php endif ?>
        <?php foreach ( $atts['qa'] as $item ): ?>
            <?php
            $q = nl2br( $item['q'] );
            $a = nl2br( $item['a'] );

            $q = apply_filters( 'expert_review_faq_answers:question', $q, $atts );
            $a = apply_filters( 'expert_review_faq_answers:answer', $a, $atts );

            do_action( 'expert_review_questions_and_answers', [ $q, $a ] );

            $a_style = '';
            $expand  = ' expand';
            if ( empty( $atts['expanded'] ) ) {
                $expand  = '';
                $a_style = ' style="display:none"';
            }
            ?>
            <?php if ( $use_microdata ): ?>
                <div class="expert-review-faq-item<?php echo $expand ?>" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <<?php echo $q_tag ?> class="expert-review-faq-item__question js-expert-review-faq-item-question" itemprop="name"><?php echo $q ?></<?php echo $q_tag ?>>
                    <<?php echo $a_tag ?> class="expert-review-faq-item__answer js-expert-review-faq-item-answer"<?php echo $a_style ?> itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <span itemprop="text"><?php echo $a ?></span>
                    </<?php echo $a_tag ?>>
                </div>
            <?php else: ?>
                <div class="expert-review-faq-item<?php echo $expand ?>">
                    <<?php echo $q_tag ?> class="expert-review-faq-item__question js-expert-review-faq-item-question"><?php echo $q ?></<?php echo $q_tag ?>>
                    <<?php echo $a_tag ?> class="expert-review-faq-item__answer js-expert-review-faq-item-answer"<?php echo $a_style ?>><?php echo $a ?></<?php echo $a_tag ?>>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    </div><!--.expert-review-faq-->
<?php do_action( 'expert_review:faq_after', $atts );
