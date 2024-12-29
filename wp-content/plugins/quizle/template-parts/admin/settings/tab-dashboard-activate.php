<?php

/**
 * @version 1.3.0
 */

use Wpshop\Quizle\Admin\Settings;
use function Wpshop\Quizle\container;

if ( ! defined( 'WPINC' ) ) {
    die;
}

$settings = container()->get( Settings::class );

?>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'License', QUIZLE_TEXTDOMAIN ),
        sprintf( __( 'To activate the plugin, enter the license key that you receive after payment in the letter or in <a href="%s" target="_blank" rel="noopener">personal account</a>.', QUIZLE_TEXTDOMAIN ), 'https://wpshop.ru/dashboard' )
    ); ?>
</div>

<div class="wpshop-settings-license">
    <?php if ( $error = $settings->get_reg_option()['license_error'] ): ?>
        <div class="error-message">
            <?php echo esc_html( $error ) ?>
        </div>
    <?php endif ?>
    <form class="wpshop-settings-license__form" action="" method="post" name="registration">
        <?php $settings->render_reg_input(); ?>
    </form>
</div>
