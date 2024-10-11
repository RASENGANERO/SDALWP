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
        __( 'Appearance', QUIZLE_TEXTDOMAIN ),
        '',
        $settings->doc_link( 'doc' ) . '/settings/#appearance'
    ); ?>
</div>

<div class="wpshop-settings-form-row">
    <p class="description">
        <?php echo __( 'The colour settings are the default settings that will be used when you create a new quiz. Changing these settings does not affect quizzes that have already been created.', QUIZLE_TEXTDOMAIN ) ?>
    </p>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_color_picker( 'quizle-color-primary', __( 'Control Color', QUIZLE_TEXTDOMAIN ), [ 'default' => $settings->get_default( 'quizle-color-primary' ) ] ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_color_picker( 'quizle-color-text-primary', __( 'Control Text Color', QUIZLE_TEXTDOMAIN ), [ 'default' => $settings->get_default( 'quizle-color-text-primary' ) ] ) ?>
</div>

<div class="wpshop-settings-form-row">
    <p class="description"><?php echo __( 'Controls are buttons, a progress bar and other interactive elements.', QUIZLE_TEXTDOMAIN ) ?></p>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_color_picker( 'quizle-color-background', __( 'Background Color', QUIZLE_TEXTDOMAIN ), [ 'default' => $settings->get_default( 'quizle-color-background' ) ] ) ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_color_picker( 'quizle-color-text', __( 'Text Color', QUIZLE_TEXTDOMAIN ), [ 'default' => $settings->get_default( 'quizle-color-text' ) ] ) ?>
</div>
