<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\PluginContainer;

$settings = PluginContainer::get( Settings::class );

?>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Settings', QUIZLE_TEXTDOMAIN ),
        '',
        $settings->doc_link( 'doc' ) . '/settings/#settings'
    ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'is_quizle_public', __( 'Enable pages for Quizles', QUIZLE_TEXTDOMAIN ) ); ?>
    <div class="wpshop-settings-form-description wpshop-settings-form-description--switch-box">
        <?php echo __( 'By default, no separate pages are created for each quizle.', QUIZLE_TEXTDOMAIN ) ?>
    </div>
</div>
<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'clear_database', __( 'Clear database on the plugin deleting', QUIZLE_TEXTDOMAIN ) ); ?>
    <div class="wpshop-settings-form-description wpshop-settings-form-description--switch-box">
        <?php echo __( 'All data related to the plugin will be deleted from the database.', QUIZLE_TEXTDOMAIN ) ?>
    </div>
</div>
<div class="wpshop-settings-form-row">
    <div class="wpshop-settings-form-row__body wpshop-settings-editor">
        <label class="wpshop-settings-editor__label"><?php echo __( 'Privacy Policy', QUIZLE_TEXTDOMAIN ) ?></label>
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
