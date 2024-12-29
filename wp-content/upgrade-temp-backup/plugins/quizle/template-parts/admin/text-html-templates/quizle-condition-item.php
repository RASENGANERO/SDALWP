<?php

defined( 'WPINC' ) || die;

/**
 * @version 1.1.0
 */

?>
<script type="text/html" id="tmpl-quizle-condition-item">
    <# // console.log('tmpl-quizle-condition-item', data) #>
    <div class="quizle-question-condition-item-wrap js-quizle-condition-item">
        <div class="quizle-question-condition-item">
            <div class="quizle-question-condition-item__title">
                        <span>
                            <?php echo __( 'Condition', QUIZLE_TEXTDOMAIN ) ?> <span class="js-quizle-condition-item-idx">{{data.idx}}</span>
                            <span class="quizle-question-condition-item__change-mark js-quizle-condition-item-changed"
                                  {{{ !data._changed ? 'style="display:none"' : '' }}}><?php echo __( '(changed)', QUIZLE_TEXTDOMAIN ) ?></span>
                </span>
                <div class="quizle-question-condition-item-action quizle-question-condition-item--delete js-quizle-question--remove-condition-item" title="<?php echo __( 'remove condition', QUIZLE_TEXTDOMAIN ) ?>"></div>
            </div>
            <select data-name="target" class="js-quizle-condition-item-question">
                <option value=""></option>
                <# for (var i = 0; i < data.questions.length; i++) { #>
                <option value="{{data.questions[i].question_id}}">{{data.questions[i].title}}</option>
                <# } #>
            </select>
            Дан ответ
            <span class="js-quizle-condition-item-answers"><?php echo __( '(select a question)', QUIZLE_TEXTDOMAIN ) ?></span>
            <input type="hidden" data-name="id" class="js-quizle-condition-item-id" value="{{data.id}}">
            <input type="hidden" data-name="disabled" value="{{data.disabled}}">
            <input type="hidden" data-name="compare" value="{{data.compare}}">

            <div class="quizle-question-condition-item__overlay" {{{ data.disabled== 1 ?
            '' : 'style="display:none"' }}}><?php echo __( 'cannot be applied', QUIZLE_TEXTDOMAIN ) ?></div>
    </div>
    </div>
</script>
