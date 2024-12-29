<?php

use Wpshop\ExpertReview\Plugin;

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<script type="text/html" id="tmpl-expert-review-poll-live-preview">
    <div class="expert-review-poll-live-preview">
        <div class="expert-review-live-preview-notice"><?php echo _x( 'Preview may vary from actual', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>

        <div class="expert-review-poll expert-review-poll--style-{{data.style}} expert-review-poll--color-{{data.color}}">
            <# if (data.title && data.show_title) { #>
            <div class="expert-review-poll__header">{{ data.title }}</div>
            <# } #>

            <# for (var i = 0; i < data.answers.length; i++) { #>
            <div class="expert-review-poll-item">
                <div class="expert-review-poll-item__answer">{{ data.answers[i].text }}</div>
                <div class="expert-review-poll-item__num">30</div>
                <div class="expert-review-poll-item__progress" style="width: 30%"></div>
            </div>
            <# } #>

            <# if (data.show_results_button) { #>
            <button class="button expert-review-poll__result-button">
                <?php echo esc_html( apply_filters( 'expert_review_poll:show_result_text', __( 'Show Results', Plugin::TEXT_DOMAIN ) ) ) ?>
            </button>
            <# } #>

            <# if (data.show_count) { #>
            <div class="expert-review-poll__count"><?php echo _x( 'Voted', 'tpl', Plugin::TEXT_DOMAIN ) ?>:
                <span class="js-expert-review-poll-count">0</span>
            </div>
            <# } #>
        </div>

    </div>
</script>
