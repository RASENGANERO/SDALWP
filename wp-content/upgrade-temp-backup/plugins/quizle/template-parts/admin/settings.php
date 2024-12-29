<?php

/**
 * @version 1.0.0
 */

defined( 'WPINC' ) || die;

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\PluginContainer;

$settings = PluginContainer::get( Settings::class );

?>


<div class="wrap wpshop-settings-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php settings_errors( 'quizle_messages' ); ?>

    <div class="wpshop-settings-container">
        <div class="wpshop-settings-container__tabs">
            <ul class="wpshop-settings-tabs js-wpshop-settings-tabs">
                <?php
                $set_first_active = true;
                foreach ( $settings->get_tabs() as $tab ): ?>
                    <li data-tab="#<?php echo esc_attr( $tab['id'] ) ?>"<?php echo $set_first_active ? ' class="active"' : ''; ?>><?php
                        echo $settings->get_tab_icons()[ $tab['name'] ] ?? '';
                        echo '<span class="wpshop-settings-tab__label">' . esc_html( $tab['label'] ) . '</span>';
                        $set_first_active = false;
                        ?></li>
                <?php endforeach ?>
            </ul>
        </div>
        <div class="wpshop-settings-container__body">
            <div class="wpshop-settings-box">
                <?php $settings->wrap_form( function ( $settings ) {
                    $set_first_active = true;
                    foreach ( $settings->get_tabs() as $tab ): ?>
                        <div id="<?php echo $tab['id'] ?>" class="wpshop-settings-tab js-wpshop-settings-tab"<?php echo $set_first_active ? ' style="display:block"' : '' ?>>
                            <?php if ( current_user_can( 'manage_options' ) ): ?>
                                <?php Settings::get_template_part( 'admin/settings/tab', $tab['template_name'], [ 'label' => $tab['label'] ] ); ?>
                            <?php else: ?>
                                <p class="error-message"><?php echo __( 'Sorry, you are not allowed to perform actions on this section.', QUIZLE_TEXTDOMAIN ) ?></p>
                            <?php endif ?>
                        </div>
                        <?php $set_first_active = false; endforeach;
                } ); ?>
            </div>
        </div>
        <div class="wpshop-settings-container__sidebar">
            <div class="wpshop-settings-sidebar-inner">

                <div class="wpshop-settings-box">
                    Plugin Version: <?php echo QUIZLE_VERSION ?>
                </div>

            </div>
        </div>
    </div>
</div>
