<?php

defined( 'WPINC' ) || die;

/**
 * @version 1.1.0
 */

?>
<script type="text/html" id="tmpl-quizle-image-uploader">
    <div class="quizle-image-upload js-quizle-image-uploader">
        <div class="quizle-image-upload__preview quizle-image-upload__preview--{{ data.size || 'small' }} js-quizle-image-preview" {{{data.url ?
        '' : 'style="display:none"'}}}>
        <# if (data.url) { #>
        <img src="{{ data.url }}" class="quizle-image-upload__preview-img">
        <# } #>
    </div>
    <button class="button quizle-image-upload__upload-btn js-quizle-image-browse" {{{!data.url ?
    '' : 'style="display:none"'}}}><?php echo __( 'Upload Image', QUIZLE_TEXTDOMAIN ) ?></button>
    <button class="button quizle-image-upload__remove-btn js-quizle-image-remove" {{{data.url ?
    '' : 'style="display:none"'}}}><?php echo __( 'Remove Image', QUIZLE_TEXTDOMAIN ) ?></button>
    <input type="hidden" class="js-quizle-image-url" data-name="{{ data.name }}" value="{{data.url}}">
    </div>
</script>
