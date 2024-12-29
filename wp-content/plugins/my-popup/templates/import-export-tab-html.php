<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\PluginMyPopup\Plugin;
use function Wpshop\PluginMyPopup\display_help_link;

/**
 * @var WP_Post $post
 */

?>

<div class="wpshop-meta-header">
    <?php echo __( 'Import and Export', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'import-export' ) ?>
</div>


<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Export', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <label class="wpshop-meta-checkbox">
                <input type="checkbox" value="1" class="js-mypopup-export-with-rules">
                <span class="wpshop-meta-checkbox__label"></span> <?php _e( 'Export with Rules', Plugin::TEXT_DOMAIN ) ?>
            </label>
        </div>
    </div>
</div>
<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
        </div>
        <div class="wpshop-meta-field__body">
            <div class="mypopup-export__container">
                <button class="button js-mypopup-export"><?php echo __( 'Export Data', Plugin::TEXT_DOMAIN ) ?></button>
                <span class="mypopup-export__message js-mypopup-export-success-message" style="display: none"><?php echo __( 'the data is copied to the clipboard!', Plugin::TEXT_DOMAIN ) ?></span>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Import', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <textarea rows="6" class="wpshop-meta-field js-mypopup-import-input"></textarea>
            <button class="button js-mypopup-import"><?php echo __( 'Import Data', Plugin::TEXT_DOMAIN ) ?></button>
        </div>
    </div>
</div>
