<?php

defined( 'WPINC' ) || die;

/**
 * @version 1.1.0
 */

?>
<script type="text/html" id="tmpl-quizle-conditions-group">
    <# // console.log('tmpl-quizle-conditions', data) #>
    <div class="quizle-question-conditions__container js-quizle-question-conditions">
        <div class="quizle-question-conditions__header">
                    <span>
                        <?php printf( __( '%s if %s conditions are met in the answers', QUIZLE_TEXTDOMAIN ),
                            '<span class="quizle-question-conditions__toggle-span js-quizle-question-conditions-type">{{data.type_text}}</span>',
                            '<span class="quizle-question-conditions__toggle-span js-quizle-question-conditions-relation">{{data.relation_text}}</span>'
                        ) ?>
                    </span>
            <div class="quizle-question-conditions-action quizle-question-conditions--delete js-quizle-question--remove-conditions" title="<?php echo __( 'remove branching', QUIZLE_TEXTDOMAIN ) ?>"></div>
        </div>
        <div class="quizle-question-conditions__body"></div>
        <div class="quizle-question-conditions__footer">
            <span class="button js-quizle-question--add-condition-item"><?php echo __( 'Add Condition', QUIZLE_TEXTDOMAIN ) ?></span>
        </div>

        <div class="js-quizle-question-conditions-inputs">
            <input type="hidden" data-name="id" class="js-quizle-question-conditions-id" value="{{data.id}}">
            <input type="hidden" data-name="type" value="{{data.type}}">
            <input type="hidden" data-name="relation" value="{{data.relation}}">
        </div>
    </div>
</script>
