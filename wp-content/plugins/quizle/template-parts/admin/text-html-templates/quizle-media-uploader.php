<?php

defined( 'WPINC' ) || die;

/**
 * @version 1.1.0
 */

?>
<script type="text/html" id="tmpl-quizle-media-uploader">
    <# // console.log('tmpl-quizle-media-uploader', data) #>
    <div class="quizle-media-upload quizle-media-upload--{{ data.media ? data.media.type : 'none' }} js-quizle-media-uploader">
        <div class="js-quizle-media-upload-wrap" {{{data.media && data.media.type === 'link' ? 'style="display:none"' : ''}}}>
        <div class="quizle-media-upload__preview quizle-media-upload__preview--{{ data.size || 'small' }} js-quizle-media-preview" {{{data.media && data.media.type !== 'link' ?
        '' : 'style="display:none"'}}}>
        <# if (data.media && data.media.type !== 'link') { #>
        <# var mediaPreview = data.media.type == 'video' ? quizleScriptOptions.videoIcon : data.media.url; #>
        <img src="{{ mediaPreview }}" class="js-quizle-media-preview-icon quizle-media-upload__preview-{{data.media.type}}">
        <# if (data.media.type == 'video') { #>
        <span class="js-quizle-media-preview-text quizle-media-upload__preview-text">{{ data.media.title }}</span>
        <# } #>
        <# } #>
    </div>
    <button class="button quizle-media-upload__upload-btn js-quizle-media-browse" {{{!data.media || data.media.type === 'link' ?
    '' : 'style="display:none"'}}}><?php echo __( 'Upload Media', QUIZLE_TEXTDOMAIN ) ?></button>
    <button class="button quizle-media-upload__remove-btn js-quizle-media-remove" {{{data.media && data.media.type !== 'link' ?
    '' : 'style="display:none"'}}}><?php echo __( 'Remove Media', QUIZLE_TEXTDOMAIN ) ?></button>
    </div>
    <input type="text" placeholder="<?php echo __( 'Link to Youtube, Vimeo, etc', QUIZLE_TEXTDOMAIN ) ?>" class="quizle-text js-quizle-media-host-link" value="{{data.media && data.media.type === 'link' ? data.media.url : ''}}" data-prevent_observe_change="1">
    <select name="" class="quizle-select js-quizle-media-position" title="<?php echo __( 'Media Position', QUIZLE_TEXTDOMAIN ) ?>" data-prevent_observe_change="1">
        <?php foreach (
            [
                'right' => _x( 'Right', 'media position', QUIZLE_TEXTDOMAIN ),
                'left'  => _x( 'Left', 'media position', QUIZLE_TEXTDOMAIN ),
                'top'   => _x( 'Top', 'media position', QUIZLE_TEXTDOMAIN ),
            ] as $_value => $_label
        ): ?>
            <option value="<?php echo $_value ?>"{{{data.media && data.media.position === '<?php echo $_value ?>' ? ' selected' : ''}}}><?php echo $_label ?></option>
        <?php endforeach ?>
    </select>
    <input type="hidden" class="js-quizle-media-value" data-name="{{ data.name }}" data-is_json="1" value="{{ data.media ? JSON.stringify(data.media) : '' }}">
    </div>
</script>
