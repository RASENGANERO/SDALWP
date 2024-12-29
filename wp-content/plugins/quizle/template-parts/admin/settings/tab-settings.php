<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Integration\AmoCRM;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\get_file_upload_max_size;
use function Wpshop\Quizle\get_max_file_uploads;
use function Wpshop\Quizle\human_filesize;

$settings = container()->get( Settings::class );

?>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Settings', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/settings/#settings'
    ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'is_quizle_public', __( 'Enable pages for Quizles', 'quizle' ) ); ?>
    <div class="wpshop-settings-form-description wpshop-settings-form-description--switch-box">
        <?php echo __( 'By default, no separate pages are created for each quizle.', 'quizle' ) ?>
    </div>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'clear_database', __( 'Clear database on the plugin deleting', 'quizle' ) ); ?>
    <div class="wpshop-settings-form-description wpshop-settings-form-description--switch-box">
        <?php echo __( 'All data related to the plugin will be deleted from the database.', 'quizle' ) ?>
    </div>
</div>
<div class="wpshop-settings-form-row">
    <div class="wpshop-settings-form-row__body wpshop-settings-editor">
        <label class="wpshop-settings-editor__label"><?php echo __( 'Privacy Policy', 'quizle' ) ?></label>
        <div class="wpshop-settings-editor__body">
            <?php
            $name = 'privacy_policy';
            wp_editor( $settings->get_value( $name ), $name, [
                'textarea_name' => $settings->get_input_name( $name ),
                'textarea_rows' => 5,
            ] );
            ?>
        </div>
    </div>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Google reCAPTCHA', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/settings/#google-recaptcha'
    ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'grecaptcha.enabled', __( 'Enable', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'grecaptcha.site_key', __( 'Site Key', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_password_input( 'grecaptcha.secret_key', __( 'Secret Key', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        _x( 'File Upload', 'settings', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/settings/#file-upload' ) ?>
</div>

<div class="wpshop-settings-form-row">
    <div>
        <p class="description">
            <?php echo sprintf(
                __( 'Maximum number of uploaded files set by server setting <a href="%s" rel="noopener" target="_blank">max_file_uploads</a>: %d', 'quizle' ),
                'https://www.php.net/manual/en/ini.core.php#ini.max-file-uploads',
                get_max_file_uploads()
            ) ?>
        </p>
        <p class="description">
            <?php echo sprintf(
                __( 'Maximum file size specified by server setting <a href="%s" rel="noopener" target="_blank">upload_max_filesize</a>: %s', 'quizle' ),
                'https://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize',
                human_filesize( get_file_upload_max_size() )
            ) ?>
        </p>
    </div>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'file_upload.limit', __( 'Max Files Count', 'quizle' ), [ 'type' => 'number' ] ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input(
        'file_upload.accept', __( 'Mime Types', 'quizle' ),
        [],
        __( 'Comma-separated values.', 'quizle' ) . ' ' .
        __( 'You can specify specific extensions (<code>.png</code>, <code>.jpg</code>), specific file types (<code>image/jpeg</code>), or specify a subset of types, such as all images (<code>image/*</code>) or all text files (<code>text/*</code>).', 'quizle' ) . ' ' .
        sprintf( __( '<a href="%s" rel="noopener" target="_blank">More about here</a>', 'quizle' ), 'https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept' )
    ); ?>
</div>

<?php /*
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'file_upload.reset_on_reload', __( 'Reset File Inputs on Reload', 'quizle' ) ); ?>
</div>
 */ ?>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox(
        'file_upload.allow_guest',
        __( 'Allow for not Logged in Users', 'quizle' ),
        [],
        __( 'If a user is not allowed to upload files, questions with this type will not be displayed.', 'quizle' )
    ); ?>
</div>


<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Additional Settings', 'quizle' ),
        '',
        $settings->doc_link( 'doc' ) . '/settings/#additional'
    ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'enable_phone_mask', __( 'Enable Phone mask', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'user_messengers_brand_colors', __( 'Use Messenger Brand Colors', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'verify_nonce', __( 'Enable Request Verification', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'enable_wp_editor', __( 'Enable Visual Editor', 'quizle' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'prevent_autofocus', __( 'Prevent Next/Prev buttons autofocus', 'quizle' ) ); ?>
</div>
