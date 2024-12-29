<?php

use Wpshop\ExpertReview\Plugin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * @var array $icons
 */

?>
<script type="text/javascript">
    var expertPreviewLikeHelper = {
        icons: <?php echo json_encode( $icons ?: [], JSON_PRETTY_PRINT ) ?>,
        icon: function (icons, type) {
            if (this.icons.hasOwnProperty(icons) && this.icons[icons].hasOwnProperty(type)) {
                return this.icons[icons][type];
            }
            return '';
        },
        styles: function (data) {
            var results = [];
            if (data.style) results.push('expert-review-likes--style-' + data.style);
            if (data.size) results.push('expert-review-likes--size-' + data.size);
            if (data.alignment) results.push('expert-review-likes--alignment-' + data.alignment);

            if (results.length) {
                return ' ' + results.join(' ')
            }
            return '';
        },
        link: function (data) {
            var result = '';
            if (data.link) {
                if (data.link.length > 20) {
                    result = data.link.substring(0, 20) + '...';
                } else {
                    result = data.link;
                }
                return ' ' + expert_review_globals.i18n.link + ': ' + result;
            }
            return '';
        }
    };
</script>
<script type="text/html" id="tmpl-expert-review-likes-live-preview">
    <div class="expert-review-likes-live-preview">
        <div class="expert-review-live-preview-notice">
            <# if (data.name) { #>
            <?php echo _x( 'Name', 'tpl_', Plugin::TEXT_DOMAIN ) ?>: <strong>{{ data.name }}</strong>
            <# } else if (data.post_id) { #>
            ID: {{ data.post_id }}
            <# } #>
            {{expertPreviewLikeHelper.link(data)}}
        </div>

        <div class="expert-review-likes{{ expertPreviewLikeHelper.styles(data) }}">
            <button class="expert-review-likes__button expert-review-likes__button--like" data-type="like">
                <# if (parseInt(data.show_icon)) { #>
                <span class="expert-review-likes__icon">{{{ expertPreviewLikeHelper.icon(data.icons, 'like') }}}</span>
                <# } #>
                <# if (parseInt(data.show_label)) { #>
                <span class="expert-review-likes__label">{{ data.label_like }} </span>
                <# } #>
                <# if (parseInt(data.show_count)) { #>
                <span class="expert-review-likes__count" data-count="10">10</span>
                <# } #>
            </button>
            <# if (!parseInt(data.hide_dislikes)) { #>
            <button class="expert-review-likes__button expert-review-likes__button--dislike" data-type="like">
                <# if (parseInt(data.show_icon)) { #>
                <span class="expert-review-likes__icon">{{{ expertPreviewLikeHelper.icon(data.icons, 'dislike') }}}</span>
                <# } #>
                <# if (parseInt(data.show_label)) { #>
                <span class="expert-review-likes__label">{{ data.label_dislike }} </span>
                <# } #>
                <# if (parseInt(data.show_count)) { #>
                <span class="expert-review-likes__count" data-count="2">2</span>
                <# } #>
            </button>
            <# } #>
        </div>
    </div>
</script>
