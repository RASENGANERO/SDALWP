<?php

use Wpshop\ExpertReview\Plugin;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array $experts
 * @var array $users
 */


?>
<script type="text/javascript">
    var expertPreviewDataHelper = {
        users: <?php echo json_encode( $users ) ?>,
        experts: <?php echo json_encode( $experts ) ?>,
        name: function (data) {
            var result, link = '';
            if (data.expert_type === 'self') {
                result = data.expert_name;
                link = data.expert_link;
            } else {
                var item = this._item(data);
                result = item.name;
                link = item.link;
            }

            if (result) {
                if (link) {
                    result = '<a href="' + link + '" onclick="return false;">' + result + '</a>';
                }
            }

            return result;
        },
        description: function (data) {
            if (data.expert_type === 'self' || data.expert_description) {
                return data.expert_description;
            }
            return this._item(data).description;

        },
        avatar: function (data) {
            var result = '', link = '';
            if (data.expert_type === 'self') {
                result = data.expert_avatar;
                link = data.expert_link;
            } else {
                var item = this._item(data);
                result = item.avatar;
                link = item.link;
            }

            if (result) {
                result = '<img src="' + result + '">';
            }
            if (link) {
                result = '<a href="' + link + '" onclick="return false;">' + result + '</a>';
            }

            return result;
        },
        scoreAverage: function (data) {
            if (data.score.length) {
                return Number(data.score.reduce(function (total, item) {
                    return total + parseInt(item.s);
                }, 0) / data.score.length).toFixed(0);
            }
            return null;
        },
        scoreSymbol: function (data) {
            if (data.score_symbol) {
                return data.score_symbol.replace('%%max%%', data.score_max);
            }
            return '';
        },
        scoreWidth: function (data, score) {
            if (data.score_max) {
                var val = parseFloat(score.s.replace(',', '.'));
                if (typeof val === 'number') {
                    return Number((val * 100) / data.score_max).toFixed(0);
                }

            }
            return 0;
        },
        _item: function (data) {
            var type = data.expert_type,
                id = data.expert_id,
                items = [];
            switch (type) {
                case 'user_id':
                    items = this.users.filter(function (item) {
                        return item.id == id;
                    });
                    break;
                case 'expert_id':
                    items = this.experts.filter(function (item) {
                        return item.id == id;
                    });
                    break;
                default:
                    throw 'Unsupported expert type: ' + type;
            }
            if (items.length) {
                return items[0];
            }
            return {};
        }
    };

    if (typeof nl2br === 'undefined') {
        function nl2br(str) {
            if (typeof str === 'string') {
                return str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>');
            }
            return str;
        }
    } else {
        console.warn('nl2br() function is already defined');
    }

</script>
<script type="text/html" id="tmpl-expert-review-live-preview">
    <div class="expert-review-live-preview expert-review--color-{{data.color}}">
        <div class="expert-review-live-preview-notice"><?php echo _x( 'Preview may vary from actual', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>

        <!-- Expert -->
        <# if (data.expert_show && (expertPreviewDataHelper.name(data) || data.expert_text ||
        !(data.qa.length || data.score.length || data.pluses.length || data.pluses.minuses))) { #>
        <div class="expert-review-expert">
            <# if (parseInt(data.expert_show_title) && data.expert_title) { #>
            <div class="expert-review-expert-header">{{data.expert_title}}</div>
            <# } else { #>
            <div class="expert-review-expert-header">&lt;<?php echo _x( 'no header', 'tpl', Plugin::TEXT_DOMAIN ) ?>
                &gt;
            </div>
            <# } #>
            <div class="expert-review-expert-bio">
                <div class="expert-review-expert-bio__avatar">
                    <# if (expertPreviewDataHelper.avatar(data)) { #>
                    {{{ expertPreviewDataHelper.avatar(data) }}}
                    <# } #>
                </div>
                <div class="expert-review-expert-bio__body">
                    <div class="expert-review-expert-bio-name">{{{ expertPreviewDataHelper.name(data) }}}</div>
                    <div class="expert-review-expert-bio-description">{{ expertPreviewDataHelper.description(data) }}
                    </div>
                </div>
                <# if (data.expert_show_button) { #>
                <div class="expert-review-expert-bio__button">
                    <span class="expert-review-button js-expert-review-button" onclick="return false;">{{data.expert_question_button_text}}</span>
                </div>
                <# } #>
            </div>
            <# if (data.expert_text) { #>
            <div class="expert-review-expert-text">{{{ nl2br(data.expert_text) }}}</div>
            <# } #>
        </div>
        <# } #>


        <!-- Q&A -->
        <# if (data.qa.length) { #>
        <div class="expert-review-qa">
            <# if (data.qa_title && data.qa_show_title) { #>
            <div class="expert-review-expert-header">{{data.qa_title}}</div>
            <# } else { #>
            <div class="expert-review-expert-header">
                &lt;<?php echo _x( 'questions & answers', 'tpl', Plugin::TEXT_DOMAIN ) ?>&gt;
            </div>
            <# } #>

            <# for (var i = 0; i < data.qa.length; i++) { #>
            <div class="expert-review-qa-container">
                <div class="expert-review-qa__question">{{{ nl2br(data.qa[i].q) }}}</div>
                <div class="expert-review-qa__answer">
                    <div class="expert-review-qa__avatar">{{{ expertPreviewDataHelper.avatar(data) }}}</div>
                    <div class="expert-review-qa__text">{{{ nl2br(data.qa[i].a) }}}</div>
                </div>
            </div>
            <# } #>
        </div>
        <# } #>


        <!-- Rating (Score) -->
        <# if (data.score.length) { #>
        <div class="expert-review-score">

            <# if (parseInt(data.score_show_title) && data.score_title ) { #>
            <div class="expert-review-score-header">{{data.score_title}}</div>
            <# } else { #>
            <div class="expert-review-expert-header">&lt;<?php echo _x( 'rating', 'tpl', Plugin::TEXT_DOMAIN ) ?>&gt;
            </div>
            <# } #>

            <# for (var i = 0; i < data.score.length; i++) { #>
            <div class="expert-review-score-line">
                <div class="expert-review-score-line__name">{{ data.score[i].n }}</div>
                <div class="expert-review-score-line__progress">
                    <div class="expert-review-score-line__progress-container">
                        <div class="expert-review-score-line__progress-fill" style="width: {{ expertPreviewDataHelper.scoreWidth(data, data.score[i]) }}%;"></div>
                    </div>
                </div>
                <div class="expert-review-score-line__score">{{ data.score[i].s }}{{
                    expertPreviewDataHelper.scoreSymbol(data) }}
                </div>
            </div>
            <# } #>

            <# if (data.score_summary_text || data.score_summary_average) { #>
            <div class="expert-review-score-summary">
                <div class="expert-review-score-summary__label">Итого</div>
                <div class="expert-review-score-summary__average">{{ expertPreviewDataHelper.scoreAverage(data) }}</div>
                <div class="expert-review-score-summary__text">{{{ nl2br(data.score_summary_text) }}}</div>
            </div>
            <# } #>
        </div>
        <# } #>


        <!-- Pluses & Minuses -->
        <# if (data.pluses.length || data.minuses.length) { #>
        <div class="expert-review-pluses-minuses">
            <# if (data.pluses_minuses_title && data.pluses_minuses_show_title) { #>
            <div class="expert-review-pluses-minuses-header">{{data.pluses_minuses_title}}</div>
            <# } else { #>
            <div class="expert-review-pluses-minuses-header">
                &lt;<?php echo _x( 'pros & cons', 'tpl', Plugin::TEXT_DOMAIN ) ?>&gt;
            </div>
            <# } #>

            <# if (data.pluses.length) { #>
            <div class="expert-review-pluses">
                <div class="expert-review-pluses-minuses-header">{{ data.pluses_title }}</div>
                <# for (var i = 0; i < data.pluses.length; i++) { #>
                <div class="expert-review-plus">{{ data.pluses[i] }}</div>
                <# } #>
            </div>
            <# } #>

            <# if (data.minuses.length) { #>
            <div class="expert-review-minuses">
                <div class="expert-review-pluses-minuses-header">{{ data.minuses_title }}</div>
                <# for (var i = 0; i < data.minuses.length; i++) { #>
                <div class="expert-review-minus">{{ data.minuses[i] }}</div>
                <# } #>
            </div>
            <# } #>

        </div>
        <# } #>
    </div>
</script>
