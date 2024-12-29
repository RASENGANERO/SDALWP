<?php

use Wpshop\ExpertReview\Plugin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<script type="text/html" id="tmpl-expert-review-likes-rate-live-preview">
    <div class="expert-review-likes-rate-live-preview">
        <div class="expert-review-live-preview-notice"><?php echo _x( 'Preview may vary from actual', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>

        <div class="expert-review-like-rating expert-review-like-rating--{{ data.style }}">
            <div class="expert-review-like-rating__header">{{ data.title }}</div>
            <div class="expert-review-like-rating__list">
                <div class="expert-review-like-rating-item">
                    <div class="expert-review-like-rating-item__position"></div>
                    <div class="expert-review-like-rating-item__text"><a href="http://example1.com"><?php echo _x( 'Example', 'tpl', Plugin::TEXT_DOMAIN ) ?> 1</a></div>
                    <div class="expert-review-like-rating-item__count">
                        <span>15</span>
                    </div>
                </div>
                <div class="expert-review-like-rating-item">
                    <div class="expert-review-like-rating-item__position"></div>
                    <div class="expert-review-like-rating-item__text"><a href="http://example2.com"><?php echo _x( 'Example', 'tpl', Plugin::TEXT_DOMAIN ) ?> 2</a></div>
                    <div class="expert-review-like-rating-item__count">
                        <span>19</span>
                    </div>
                </div>
                <div class="expert-review-like-rating-item">
                    <div class="expert-review-like-rating-item__position"></div>
                    <div class="expert-review-like-rating-item__text"><a href="http://example2.com"><?php echo _x( 'Example', 'tpl', Plugin::TEXT_DOMAIN ) ?> 3</a></div>
                    <div class="expert-review-like-rating-item__count">
                        <span>9</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
