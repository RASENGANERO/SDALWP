<?php

/**
 * @version 1.8.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Settings\QaOptions;
use Wpshop\ExpertReview\PluginContainer;

/**
 * @var array    $atts
 * @var callable $nl2br
 * @var string   $expert_avatar
 */


$use_microdata = ! PluginContainer::get( AdvancedOptions::class )->use_json_ld_faq_microdata;

$qa_options = PluginContainer::get( QaOptions::class );

$q_tag = apply_filters( 'expert_review/qa/question_tag', $qa_options->qa_question_tag ?: 'div', $atts );
$a_tag = apply_filters( 'expert_review/qa/answer_tag', $qa_options->qa_answer_tag ?: 'div', $atts );

if ( ! empty( $atts['qa'] ) ): ?>
    <?php do_action( 'expert_review:before_qa', $atts ); ?>

    <div class="expert-review-qa">
        <?php if ( ! empty( $atts['qa_title'] ) && $atts['qa_show_title'] == 1 ): ?>
            <div class="expert-review-qa-header"><?php echo $atts['qa_title'] ?></div>
        <?php endif ?>
        <?php foreach ( $atts['qa'] as $qa ): ?>
            <?php
            $q = apply_filters( 'expert_review_qa:question', $nl2br( $qa['q'] ), $atts );
            $a = apply_filters( 'expert_review_qa:answer', $nl2br( $qa['a'] ), $atts );

            do_action( 'expert_review_questions_and_answers', [ $q, $a ] );
            ?>
            <?php if ( $use_microdata ): ?>
                <div class="expert-review-qa-container" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <<?php echo $q_tag ?> class="expert-review-qa__question" itemprop="name"><?php echo $q ?></<?php echo $q_tag ?>>
                    <<?php echo $a_tag ?> class="expert-review-qa__answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <div class="expert-review-qa__avatar"><?php echo $expert_avatar ?></div>
                        <div class="expert-review-qa__text" itemprop="text"><?php echo $a ?></div>
                    </<?php echo $a_tag ?>>
                </div>
            <?php else: ?>
                <div class="expert-review-qa-container">
                    <<?php echo $q_tag ?> class="expert-review-qa__question"><?php echo $q ?></<?php echo $q_tag ?>>
                    <<?php echo $a_tag ?> class="expert-review-qa__answer">
                        <div class="expert-review-qa__avatar"><?php echo $expert_avatar ?></div>
                        <div class="expert-review-qa__text"><?php echo $a ?></div>
                    </<?php echo $a_tag ?>>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    </div><!--.expert-review-qa-->

    <?php do_action( 'expert_review:after_qa', $atts ); ?>
<?php endif ?>
