<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Plugin;
use Wpshop\ExpertReview\Shortcodes\Poll;

/**
 * @var array $experts
 * @var array $users
 */

$polls = get_posts( [
    'post_type'   => Poll::POST_TYPE,
    'post_status' => 'publish',
    'numberposts' => - 1,
    'order'       => 'ASC',
] );

?>

<script type="text/javascript">
    var __expert_review_poll_data_helper = {
        style_options: [
            {label: '<?php echo _x( 'Light 1', 'tpl', Plugin::TEXT_DOMAIN ) ?>', value: 'light-1'},
            {label: '<?php echo _x( 'Light 2', 'tpl', Plugin::TEXT_DOMAIN ) ?>', value: 'light-2'},
            {label: '<?php echo _x( 'Solid 2', 'tpl', Plugin::TEXT_DOMAIN ) ?>', value: 'solid-1'}
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
<script type="text/html" id="tmpl-expert-review-poll-popup">
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
        <!--        <input type="hidden" class="js-mce-expert-review-unique-field" name="unique_id" value="{{data.id}}">-->

        <div class="mce-expert-review-poll-preselected">

            <div class="mce-expert-review-form js-mce-expert-review-poll-prepared">
                <label class="mce-expert-review-form-label" for="mce-expert-review-poll-prepared"><?php echo _x( 'Existing Polls', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <select id="mce-mce-expert-review-poll-prepared">
                        <option value=""><?php echo _x( '-- select value --', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                        <?php foreach ( $polls as $poll ): ?>
                            <?php
                            $opt_text = mb_strlen( $poll->post_title, 'UTF-8' ) < 150
                                ? $poll->post_title
                                : ( mb_substr( $poll->post_title, 0, 150, 'UTF-8' ) . '...' )
                            ?>
                            <option value="<?php echo $poll->ID ?>">
                                [<?php echo $poll->ID ?>] <?php echo $opt_text ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <p class="description"><?php echo _x( 'select existing poll or create new one manually', 'tpl', Plugin::TEXT_DOMAIN ) ?></p>
                </div>
            </div>
        </div>

        <div class="js-mce-expert-review-poll-main-data">
            <div class="mce-expert-review-form js-mce-expert-review-poll-show-title">
                <div class="mce-expert-review-form-label"><?php echo _x( 'Show Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="mce-expert-review-form-field">
                    <label>
                        <input type="checkbox"<# if (parseInt(data.show_title)) { #> checked<# }
                        #>> <?php echo _x( 'show', 'tpl', Plugin::TEXT_DOMAIN ) ?>
                    </label>
                </div>
            </div>

            <?php /*
        <div class="mce-expert-review-form js-mce-expert-review-poll-name">
            <label class="mce-expert-review-form-label" for="mce-expert-review-poll-name"><?php echo _x( 'Name', 'tpl_', Plugin::TEXT_DOMAIN ) ?></label>
            <div class="mce-expert-review-form-field">
                <input id="mce-expert-review-poll-name" type="text" value="{{data.name}}">
            </div>
        </div>
        */ ?>

            <div class="mce-expert-review-form js-mce-expert-review-poll-title">
                <label class="mce-expert-review-form-label" for="mce-expert-review-poll-title"><?php echo _x( 'Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <input id="mce-expert-review-poll-title" type="text" value="{{data.title}}">
                </div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-poll-style">
                <label class="mce-expert-review-form-label" for="mce-mce-expert-review-poll-style"><?php echo _x( 'Style', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <select id="mce-mce-expert-review-poll-style">
                        {{{ __expert_review_poll_data_helper.render_options(data.style, 'style_options') }}}
                    </select>
                </div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-poll-show-count">
                <div class="mce-expert-review-form-label"><?php echo _x( 'Show Count', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="mce-expert-review-form-field">
                    <label>
                        <input type="checkbox"<# if (parseInt(data.show_count)) { #> checked<# }
                        #>> <?php echo _x( 'show', 'tpl', Plugin::TEXT_DOMAIN ) ?>
                    </label>
                </div>
            </div>
            <div class="mce-expert-review-form js-mce-expert-review-poll-show-result-button">
                <div class="mce-expert-review-form-label"><?php echo _x( 'Results Button', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="mce-expert-review-form-field">
                    <label>
                        <input type="checkbox"<# if (parseInt(data.show_results_button)) { #> checked<# } #>>
                        <?php echo _x( 'show', 'tpl', Plugin::TEXT_DOMAIN ) ?>
                    </label>
                </div>
            </div>

            <?php /*
        <div class="mce-expert-review-form js-mce-expert-review-poll-multiple">
            <div class="mce-expert-review-form-label"><?php echo _x( 'Multiple', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
            <div class="mce-expert-review-form-field">
                <label>
                    <input type="checkbox"<# if (data.multiple) { #> checked<# }
                    #>> <?php echo _x( 'allow fiew answers', 'tpl', Plugin::TEXT_DOMAIN ) ?>
                </label>
            </div>
        </div>
        */ ?>


            <div class="mce-expert-review-poll-answers">
                <div class="mce-expert-review-poll-answers-list js-mce-expert-review-poll-answers-list">
                    <# if (!data.answers.length) { #>
                    <div class="mce-expert-review-poll-answers-item js-mce-expert-review-poll-answers-item">
                        <input type="hidden" value="1" class="js-mce-expert-review-poll-answers-item-id">
                        <!--                    <div class="mce-expert-review-poll-answers-item__id"><span class="js-mce-expert-review-poll-answers-item-id">1</span></div>-->
                        <textarea placeholder="<?php echo _x( 'Answer', 'tpl', Plugin::TEXT_DOMAIN ) ?>"></textarea>
                        <div class="mce-expert-review-qa-remove">
                            <span class="js-mce-expert-review-poll-answers-remove">&times;</span>
                        </div>
                    </div>
                    <# } #>
                    <# for (var i = 0; i < data.answers.length; i++) { #>
                    <div class="mce-expert-review-poll-answers-item js-mce-expert-review-poll-answers-item">
                        <input type="hidden" value="{{ data.answers[i].id }}" class="js-mce-expert-review-poll-answers-item-id">
                        <textarea placeholder="<?php echo _x( 'Answer', 'tpl', Plugin::TEXT_DOMAIN ) ?>">{{ data.answers[i].text }}</textarea>
                        <div class="mce-expert-review-qa-remove">
                            <span class="js-mce-expert-review-poll-answers-remove">&times;</span>
                        </div>
                    </div>
                    <# } #>
                </div>
                <div>
                    <button type="button" class="button js-mce-expert-review-poll-answers-add"><?php echo _x( 'Add', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                </div>
            </div>
        </div>
    </div>
</script>
