<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Plugin;

/**
 * @var array $experts
 * @var array $users
 */

?>

<script type="text/javascript">
    var __expert_review_faq_data_helper = {
        style_options: [
            {label: 'Simple 1', value: 'simple-1'},
            {label: 'Style 1', value: 'style-1'},
            {label: 'Style 2', value: 'style-2'},
            {label: 'Style 3', value: 'style-3'},
            {label: 'Style 4', value: 'style-4'},
        ],
        render_options(value, options) {
            var items = this[options],
                result = '';

            items.forEach(function (item) {
                var selected = item.value === value ? ' selected' : '';
                result += '<option value="' + item.value + '"' + selected + '>' + item.label + '</option>';
            });

            return result;
        }
    }
</script>
<script type="text/html" id="tmpl-expert-review-faq-popup">
    <div class="mce-expert-review">

        <div class="mce-expert-review-colors">
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--purple-1" data-color="purple-1"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--blue-1" data-color="blue-1"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--blue-2" data-color="blue-2"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--pink-1" data-color="pink-1"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--red-1" data-color="red-1"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--orange-1" data-color="orange-1"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--green-2" data-color="green-2"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--gray-1" data-color="gray-1"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--gray-2" data-color="gray-2"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--black-1" data-color="black-1"></div>
            <div class="mce-expert-review-color js-mce-expert-review-color mce-expert-review-color--custom" data-color="custom"></div>
        </div>

        <input type="hidden" class="js-mce-expert-review-color-field" value="{{data.color}}">

        <div class="mce-expert-review-form js-mce-expert-review-faq-expanded">
            <div class="mce-expert-review-form-label"><?php echo _x( 'Output', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
            <div class="mce-expert-review-form-field">
                <label>
                    <input type="checkbox"<# if (data.expanded) { #> checked<# }
                    #>> <?php echo _x( 'expanded', 'tpl', Plugin::TEXT_DOMAIN ) ?>
                </label>
            </div>
        </div>

        <div class="mce-expert-review-form js-mce-expert-review-faq-show-title">
            <div class="mce-expert-review-form-label"><?php echo _x( 'Show Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
            <div class="mce-expert-review-form-field">
                <label>
                    <input type="checkbox"<# if (data.show_title) { #> checked<# }
                    #>> <?php echo _x( 'show', 'tpl', Plugin::TEXT_DOMAIN ) ?>
                </label>
            </div>
        </div>

        <div class="mce-expert-review-form js-mce-expert-review-faq-title">
            <label class="mce-expert-review-form-label" for="mce-expert-review-faq-title"><?php echo _x( 'Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
            <div class="mce-expert-review-form-field">
                <input id="mce-expert-review-faq-title" type="text" value="{{data.title}}">
            </div>
        </div>

        <div class="mce-expert-review-form js-mce-expert-review-faq-style">
            <label class="mce-expert-review-form-label" for="mce-mce-expert-review-faq-style"><?php echo _x( 'Style', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
            <div class="mce-expert-review-form-field">
                <select id="mce-mce-expert-review-faq-style">
                    {{{ __expert_review_faq_data_helper.render_options(data.style, 'style_options') }}}
                </select>
            </div>
        </div>

        <div class="mce-expert-review-qa">
            <div class="mce-expert-review-qa-list js-mce-expert-review-faq-qa-list">
                <# if (!data.qa.length) { #>
                <div class="mce-expert-review-qa-item js-mce-expert-review-faq-qa-item">
                    <textarea placeholder="<?php echo _x( 'Question', 'tpl', Plugin::TEXT_DOMAIN ) ?>"></textarea>
                    <textarea placeholder="<?php echo _x( 'Answer', 'tpl', Plugin::TEXT_DOMAIN ) ?>"></textarea>
                    <div class="mce-expert-review-qa-remove">
                        <span class="js-mce-expert-review-faq-qa-remove">&times;</span>
                    </div>
                </div>
                <# } #>
                <# for (var i = 0; i < data.qa.length; i++) { #>
                <div class="mce-expert-review-qa-item js-mce-expert-review-faq-qa-item">
                    <textarea placeholder="<?php echo _x( 'Question', 'tpl', Plugin::TEXT_DOMAIN ) ?>">{{ data.qa[i].q }}</textarea>
                    <textarea placeholder="<?php echo _x( 'Answer', 'tpl', Plugin::TEXT_DOMAIN ) ?>">{{ data.qa[i].a }}</textarea>
                    <div class="mce-expert-review-qa-remove">
                        <span class="js-mce-expert-review-faq-qa-remove">&times;</span>
                    </div>
                </div>
                <# } #>
            </div>
            <div>
                <button type="button" class="button js-mce-expert-review-faq-qa-add"><?php echo _x( 'Add', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
            </div>
        </div>

    </div>
</script>
