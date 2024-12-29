<?php

use Wpshop\ExpertReview\Plugin;

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<script type="text/javascript">
    var __expert_review_faq_preview_helper = {
        nl2br: function (str) {
            if (typeof str === 'string') {
                return str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>');
            }
            return str;
        }
    };
</script>
<script type="text/html" id="tmpl-expert-review-faq-live-preview">
    <div class="expert-review-faq-live-preview">
        <div class="expert-review-faq expert-review-faq--style-{{data.style}} expert-review-faq--color-{{data.color}}">

            <div class="expert-review-live-preview-notice"><?php echo _x( 'Preview may vary from actual', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>

            <# if (data.title && data.show_title) { #>
            <div class="expert-review-faq__header">{{data.title}}</div>
            <# } #>

            <# if (data.qa.length) { #>
            <# for (var i = 0; i < data.qa.length; i++) { #>
            <div class="expert-review-faq-item">
                <div class="expert-review-faq-item__question js-expert-review-faq-item-question">
                    {{{ __expert_review_faq_preview_helper.nl2br(data.qa[i].q) }}}
                </div>
                <div class="expert-review-faq-item__answer js-expert-review-faq-item-answer" style="display: {{data.expanded ? 'block' : 'none'}}">
                    {{{ __expert_review_faq_preview_helper.nl2br(data.qa[i].a) }}}
                </div>
            </div>
            <# } #>
            <# } #>

        </div>
    </div>
</script>
