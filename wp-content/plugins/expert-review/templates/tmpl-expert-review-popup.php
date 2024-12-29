<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Wpshop\ExpertReview\Plugin;
use Wpshop\ExpertReview\Preset;
use function Wpshop\ExpertReview\container;

/**
 * @var array $experts
 * @var array $users
 */

$presets = container()->get(Preset::class)->get_all_preses();
$preset_names = [];
foreach ($presets as $preset) {
    $preset_names[] = $preset['name'];
}
?>

<script type="text/javascript">
    var __expert_review_preset_items = <?php echo json_encode( $preset_names ) ?>;
</script>
<script type="text/html" id="tmpl-expert-review-popup">
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

        <div class="mce-expert-review-header js-mce-expert-review-header"><?php echo _x( 'Expert', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
        <div class="mce-expert-review-section js-mce-expert-review-section" data-id="experts">
            <div class="mce-expert-review-expert">

                <div class="mce-expert-review-form js-mce-expert-review-expert-show-expert">
                    <div class="mce-expert-review-form-label"><?php echo _x( 'Show Expert', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                    <div class="mce-expert-review-form-field"><label><input type="checkbox"<# if (data.expert_show) { #> checked<# } #>> <?php echo _x( 'Show', 'tpl', Plugin::TEXT_DOMAIN ) ?></label></div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-type">
                    <label class="mce-expert-review-form-label"><?php echo _x( 'Expert', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <div>
                            <select class="js-mce-expert-review-type">
                                <option value="self"
                                <# if (data.expert_type === "self") { #> selected<# } #>><?php echo _x( 'Setup manually', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                                <option value="user_id"
                                <# if (data.expert_type === "user_id") { #> selected<# } #>><?php echo _x( 'Select from users', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                                <option value="expert_id"
                                <# if (data.expert_type === "expert_id") { #> selected<# } #>><?php echo _x( 'Select from experts', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="mce-expert-review-form js-mce-expert-review-expert-avatar-alt">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-avatar" data-label="<?php echo _x( 'Avatar Alt', 'tpl', Plugin::TEXT_DOMAIN ) ?>">
                    </label>
                    <div class="mce-expert-review-form-field">
                        <span class="mce-expert-review-additional js-mce-expert-review-expert-avatar-alt-additional"><?php echo _x( 'Additional', 'tpl', Plugin::TEXT_DOMAIN ) ?></span>
                        <input id="mce-expert-review-expert-avatar-alt" type="text" value="{{ data.expert_avatar_alt }}" style="display: none">
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-avatar">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-avatar"><?php echo _x( 'Avatar URL', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <input id="mce-expert-review-expert-avatar" type="text" value="{{data.expert_avatar}}">
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-name">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-name"><?php echo _x( 'Name', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <input id="mce-expert-review-expert-name" type="text" value="{{data.expert_name}}"></div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-link">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-link"><?php echo _x( 'Link', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <input id="mce-expert-review-expert-link" type="text" value="{{data.expert_link}}"></div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-experts" style="display: none;">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-experts"><?php echo _x( 'Experts', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <select id="mce-expert-review-expert-experts">
							<?php foreach ( $experts as $expert ): ?>
                                <option value="<?php echo esc_attr( $expert['id'] ) ?>"
                                <# if (data.expert_id === "<?php echo $expert['id'] ?>") { #> selected<# } #>><?php echo esc_html( $expert['name'] ) ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-users" style="display: none;">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-users"><?php echo _x( 'User', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <select id="mce-expert-review-expert-users">
							<?php foreach ( $users as $user ): ?>
                                <option value="<?php echo esc_attr( $user['id'] ) ?>"
                                <# if (data.expert_id === "<?php echo $user['id'] ?>") { #> selected<# } #>><?php echo esc_html( $user['name'] . ' <' . $user['email'] . '>' ) ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-description">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-description"><?php echo _x( 'Description', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <input id="mce-expert-review-expert-description" type="text" value="{{data.expert_description}}">
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-show-button">
                    <div class="mce-expert-review-form-label"><?php echo _x( 'Ask Question', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                    <div class="mce-expert-review-form-field"><label><input type="checkbox"<# if (data.expert_show_button) { #> checked<# } #>> <?php echo _x( 'Show Button', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-question-button-text">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-question-button-text"><?php echo _x( 'Ask Question - text', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <input id="mce-expert-review-expert-question-button-text" type="text" value="{{data.expert_question_button_text}}">
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-show-button-type">
                    <label class="mce-expert-review-form-label" for="mce-mce-expert-review-expert-show-button-type"><?php echo _x( 'Ask Question - action', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field">
                        <select id="mce-mce-expert-review-expert-show-button-type">
                            <option value="popup"
                            <# if (data.expert_show_button_type === "popup") { #> selected<# } #>><?php echo _x( 'Popup', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                            <option value="comments"
                            <# if (data.expert_show_button_type === "comments") { #> selected<# } #>><?php echo _x( 'Comments', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                            <option value="link"
                            <# if (data.expert_show_button_type === "link") { #> selected<# } #>><?php echo _x( 'Link', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                        </select>
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-external-link" style="display: none">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-external-link"><?php echo _x( 'Ask Question - link', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field"><input id="mce-expert-review-external-link" type="text" value="{{data.question_external_link}}">
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-popup-use-phone" style="display: none">
                    <div class="mce-expert-review-form-label"></div>
                    <div class="mce-expert-review-form-field">
                        <label><input type="checkbox"<# if (data.popup_use_phone) { #> checked<# } #>> <?php echo _x( 'Phone instead of email', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    </div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-show-title">
                    <div class="mce-expert-review-form-label"><?php echo _x( 'Show Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                    <div class="mce-expert-review-form-field"><label><input type="checkbox"<# if (data.expert_show_title) { #> checked<# } #>> <?php echo _x( 'Show', 'tpl', Plugin::TEXT_DOMAIN ) ?></label></div>
                </div>

                <div class="mce-expert-review-form js-mce-expert-review-expert-title">
                    <label class="mce-expert-review-form-label" for="mce-expert-review-expert-title"><?php echo _x( 'Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                    <div class="mce-expert-review-form-field"><input id="mce-expert-review-expert-title" type="text" value="{{data.expert_title}}">
                    </div>
                </div>

                <div class="mce-expert-review-expert-text js-mce-expert-review-expert-text">
                    <textarea placeholder="<?php echo _x( 'Text (if needed)', 'tpl', Plugin::TEXT_DOMAIN ) ?>">{{data.expert_text}}</textarea>
                </div>

            </div>
        </div>

        <div class="mce-expert-review-header js-mce-expert-review-header"><?php echo __( 'Q&A', Plugin::TEXT_DOMAIN ) ?></div>
        <div class="mce-expert-review-section js-mce-expert-review-section" data-id="qa">
            <div class="mce-expert-review-qa">
                <div class="mce-expert-review-qa-list js-mce-expert-review-qa-list">
                    <# if (!data.qa.length) { #>
                    <div class="mce-expert-review-qa-item js-mce-expert-review-qa-item">
                        <textarea placeholder="<?php echo _x( 'Question', 'tpl', Plugin::TEXT_DOMAIN ) ?>"></textarea>
                        <textarea placeholder="<?php echo _x( 'Answer', 'tpl', Plugin::TEXT_DOMAIN ) ?>"></textarea>
                        <div class="mce-expert-review-qa-remove">
                            <span class="js-mce-expert-review-qa-remove">&times;</span>
                        </div>
                    </div>
                    <# } #>
                    <# for (var i = 0; i < data.qa.length; i++) { #>
                    <div class="mce-expert-review-qa-item js-mce-expert-review-qa-item">
                        <textarea placeholder="<?php echo _x( 'Question', 'tpl', Plugin::TEXT_DOMAIN ) ?>">{{ data.qa[i].q }}</textarea>
                        <textarea placeholder="<?php echo _x( 'Answer', 'tpl', Plugin::TEXT_DOMAIN ) ?>">{{ data.qa[i].a }}</textarea>
                        <div class="mce-expert-review-qa-remove">
                            <span class="js-mce-expert-review-qa-remove">&times;</span>
                        </div>
                    </div>
                    <# } #>
                </div>
                <div>
                    <button type="button" class="button js-mce-expert-review-qa-add"><?php echo _x( 'Add', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                </div>
            </div><!--.mce-expert-review-qa-->

            <div class="mce-expert-review-form js-mce-expert-review-qa-show-title">
                <div class="mce-expert-review-form-label"><?php echo _x( 'Show Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="mce-expert-review-form-field"><label><input type="checkbox"<# if (data.qa_show_title) { #> checked <# } #>> <?php echo _x( 'Show', 'tpl', Plugin::TEXT_DOMAIN ) ?></label></div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-qa-title">
                <label class="mce-expert-review-form-label" for="mce-expert-review-qa-title"><?php echo _x( 'Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field"><input id="mce-expert-review-qa-title" type="text" value="{{data.qa_title}}"></div>
            </div>
        </div>

        <div class="mce-expert-review-header js-mce-expert-review-header"><?php echo __( 'Rating', Plugin::TEXT_DOMAIN ) ?></div>

        <div class="mce-expert-review-section js-mce-expert-review-section" data-id="rating">
            <div class="mce-expert-review-score">
                <div class="mce-expert-review-score-lines js-mce-expert-review-score-lines">
                    <# if(!data.score.length) { #>
                    <div class="mce-expert-review-score-line js-mce-expert-review-score-line">
                        <div class="mce-expert-review-score-line-name js-mce-expert-review-score-line-name">
                            <input type="text"></div>
                        <div class="mce-expert-review-score-line-score js-mce-expert-review-score-line-score">
                            <input type="number" min="0" step="0.1"></div>
                        <div class="mce-expert-review-score-line-remove">
                            <span class="js-mce-expert-review-score-line-remove">&times;</span></div>
                    </div>
                    <# } #>
                    <# for (var i = 0; i < data.score.length; i++) { #>
                    <div class="mce-expert-review-score-line js-mce-expert-review-score-line">
                        <div class="mce-expert-review-score-line-name js-mce-expert-review-score-line-name">
                            <input type="text" value="{{data.score[i].n}}"></div>
                        <div class="mce-expert-review-score-line-score js-mce-expert-review-score-line-score">
                            <input type="number" min="0" step="0.1" value="{{data.score[i].s}}"></div>
                        <div class="mce-expert-review-score-line-remove">
                            <span class="js-mce-expert-review-score-line-remove">&times;</span></div>
                    </div>
                    <# } #>
                </div>
                <div>
                    <button type="button" class="button js-mce-expert-review-score-line-add"><?php echo _x( 'Add', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                </div>
            </div>

            <div class="mce-expert-review-summary-text js-mce-expert-review-summary-text">
                <textarea placeholder="<?php echo _x( 'Result Text (if needed)', 'tpl', Plugin::TEXT_DOMAIN ) ?>">{{data.score_summary_text}}</textarea>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-summary-average">
                <div class="mce-expert-review-form-label"><?php echo _x( 'Result Score', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="mce-expert-review-form-field"><label><input type="checkbox"<# if (data.score_summary_average) {#> checked <# } #>> <?php echo _x( 'Show', 'tpl', Plugin::TEXT_DOMAIN ) ?></label></div>
            </div>

            <div class="mce-expert-review-form mce-expert-review-score-max js-mce-expert-review-score-max">
                <label class="mce-expert-review-form-label" for="mce-expert-review-score-max"><?php echo _x( 'Maximum Points', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field"><input id="mce-expert-review-score-max" type="number" min="0" value="{{data.score_max}}">
                </div>
            </div>

            <div class="mce-expert-review-form mce-expert-review-score-symbol js-mce-expert-review-score-symbol">
                <label class="mce-expert-review-form-label" for="mce-expert-review-score-symbol"><?php echo _x( 'Symbol', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field"><input id="mce-expert-review-score-symbol" type="text" value="{{data.score_symbol}}">
                    <p class="description"><?php echo esc_html( '% или /%%max%% для вывода например 45/100' ) ?></p>
                </div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-score-show-title">
                <div class="mce-expert-review-form-label"><?php echo _x( 'Show Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="mce-expert-review-form-field"><label><input type="checkbox"<# if (data.score_show_title) { #> checked<# } #>> <?php echo _x( 'Show', 'tpl', Plugin::TEXT_DOMAIN ) ?></label></div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-score-title">
                <label class="mce-expert-review-form-label" for="mce-expert-review-score-title"><?php echo _x( 'Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field"><input id="mce-expert-review-score-title" type="text" value="{{data.score_title}}">
                </div>
            </div>

        </div>

        <div class="mce-expert-review-header js-mce-expert-review-header"><?php echo __( 'Pros and Cons', Plugin::TEXT_DOMAIN ) ?></div>
        <div class="mce-expert-review-section js-mce-expert-review-section" data-id="pros-n-cons">
            <div class="mce-expert-review-pluses-minuses">
                <div class="mce-expert-review-pluses">
                    <div class="mce-expert-review-pluses-container js-mce-expert-review-pluses-minuses-container">
                        <# if (!data.pluses.length) { #>
                        <div class="mce-expert-review-plus js-mce-expert-review-plus">
                            <input type="text" placeholder="+">
                            <span class="mce-expert-review-plus-minus-remove js-mce-expert-review-plus-minus-remove">&times;</span>
                        </div>
                        <# } #>
                        <# for (var i = 0; i < data.pluses.length; i++) { #>
                        <div class="mce-expert-review-plus js-mce-expert-review-plus">
                            <input type="text" placeholder="+" value="{{ data.pluses[i] }}">
                            <span class="mce-expert-review-plus-minus-remove js-mce-expert-review-plus-minus-remove">&times;</span>
                        </div>
                        <# } #>
                    </div>
                    <div>
                        <button type="button" class="button js-mce-expert-review-plus-minus-add"><?php echo _x( 'Add', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                    </div>
                </div>
                <div class="mce-expert-review-minuses">
                    <div class="mce-expert-review-minus-container js-mce-expert-review-pluses-minuses-container">
                        <# if (!data.minuses.length) { #>
                        <div class="mce-expert-review-minus js-mce-expert-review-minus">
                            <input type="text" placeholder="–">
                            <span class="mce-expert-review-plus-minus-remove js-mce-expert-review-plus-minus-remove">&times;</span>
                        </div>
                        <# } #>
                        <# for (var i = 0; i < data.minuses.length; i++) { #>
                        <div class="mce-expert-review-minus js-mce-expert-review-minus">
                            <input type="text" placeholder="–" value="{{ data.minuses[i] }}">
                            <span class="mce-expert-review-plus-minus-remove js-mce-expert-review-plus-minus-remove">&times;</span>
                        </div>
                        <# } #>
                    </div>
                    <div>
                        <button type="button" class="button js-mce-expert-review-plus-minus-add"><?php echo _x( 'Add', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                    </div>
                </div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-plus-minus-show-title">
                <div class="mce-expert-review-form-label"><?php echo _x( 'Show Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></div>
                <div class="mce-expert-review-form-field"><label><input type="checkbox"<# if (data.pluses_minuses_show_title) { #> checked<# } #>> <?php echo _x( 'Show', 'tpl', Plugin::TEXT_DOMAIN ) ?></label></div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-plus-minus-title">
                <label class="mce-expert-review-form-label" for="mce-expert-review-plus-minus-title"><?php echo _x( 'Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <input id="mce-expert-review-plus-minus-title" type="text" value="{{data.pluses_minuses_title}}">
                </div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-pluses-title">
                <label class="mce-expert-review-form-label" for="mce-expert-review-pluses-title"><?php echo _x( 'Pluses Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <input id="mce-expert-review-pluses-title" type="text" value="{{data.pluses_title}}">
                </div>
            </div>
            <div class="mce-expert-review-form js-mce-expert-review-minuses-title">
                <label class="mce-expert-review-form-label" for="mce-expert-review-minuses-title"><?php echo _x( 'Minuses Title', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <input id="mce-expert-review-minuses-title" type="text" value="{{data.minuses_title}}">
                </div>
            </div>
        </div>

        <div class="mce-expert-review-header js-mce-expert-review-header">&lt;<?php echo _x( 'Preset', 'tpl', Plugin::TEXT_DOMAIN ) ?>&gt;</div>
        <div class="mce-expert-review-section js-mce-expert-review-section" data-id="presets">

            <div class="mce-expert-review-form js-mce-expert-review-expert-preset-list">
                <label class="mce-expert-review-form-label" for="mce-mce-expert-review-expert-show-button-type"><?php echo _x( 'Saved presets', 'tpl', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <select id="mce-mce-expert-review-expert-preset-list">
                        <option value=""><?php echo _x( '-- select preset --', 'tpl', Plugin::TEXT_DOMAIN ) ?></option>
                        <# for (var i = 0; i < __expert_review_preset_items.length; i++) { #>
                            <option value="{{ __expert_review_preset_items[i] }}">{{ __expert_review_preset_items[i] }}</option>
                        <# } #>
                    </select>
                </div>
            </div>
            <div class="mce-expert-review-form js-mce-expert-review-expert-preset-select" style="">
                <div class="mce-expert-review-form-label"></div>
                <div class="mce-expert-review-form-field">
                    <button type="button" class="button" value="show"><?php echo _x( 'Apply', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                    <button type="button" class="button" value="close"><?php echo _x( 'Apply And Close', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                    <button type="button" class="button" value="remove"><?php echo _x( 'Remove', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                </div>
            </div>

            <div class="mce-expert-review-form js-mce-expert-review-preset-name">
                <label class="mce-expert-review-form-label" for="mce-expert-review-preset-name"><?php echo _x( 'Name', 'tpl_', Plugin::TEXT_DOMAIN ) ?></label>
                <div class="mce-expert-review-form-field">
                    <input id="mce-expert-review-preset-name" type="text" value="{{data.preset_name}}">
                </div>
            </div>
            <div class="mce-expert-review-form js-mce-expert-review-expert-preset-save" style="">
                <div class="mce-expert-review-form-label"></div>
                <div class="mce-expert-review-form-field">
                    <label>
                        <button type="button" class="button" data-update_text="<?php echo _x( 'Update Preset', 'tpl', Plugin::TEXT_DOMAIN ) ?>"><?php echo _x( 'Save Preset', 'tpl', Plugin::TEXT_DOMAIN ) ?></button>
                    </label>
                </div>
            </div>
        </div>

    </div>
</script>
