<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Plugin;

/**
 * @var string $value
 * @var string $name
 */

$experts = $value ? json_decode( $value, true ) : []

?>
<div class="js-expert-form-wrapper">
    <input type="hidden" class="js-expert-data" value="<?php echo esc_attr( $value ) ?>" name="<?php echo $name ?>">
    <ul class="expert-review-list js-expert-list"></ul>

    <div class="expert-review-settings-form js-expert-form">
        <div class="expert-review-settings-form__row">
            <label><?php echo __( 'Name', Plugin::TEXT_DOMAIN ) ?>
                <input type="text" class="js-expert-name">
            </label>
        </div>
        <div class="expert-review-settings-form__row">
            <label><?php echo __( 'Email', Plugin::TEXT_DOMAIN ) ?>
                <input type="email" class="js-expert-email">
            </label>
        </div>
        <div class="expert-review-settings-form__row">
            <label><?php echo __( 'Avatar URL', Plugin::TEXT_DOMAIN ) ?>
                <!--            <input type="text" class="js-expert-avatar">-->
                <input type="text" name="<?php echo '' ?>[my_popup_background_image]"
                       class="js-wpshop-form-element-url js-expert-avatar"
                       data-preview_param="bg_image:image">
                <button type="button"
                        class="button button-primary js-wpshop-form-element-browse"><?php echo __( 'Choose', Plugin::TEXT_DOMAIN ) ?></button>

            </label>
        </div>
        <div class="expert-review-settings-form__row">
            <label><?php echo __( 'Link', Plugin::TEXT_DOMAIN ) ?>
                <input type="text" class="js-expert-link">
            </label>

        </div>
        <div class="expert-review-settings-form__row">
            <label><?php echo __( 'Description', Plugin::TEXT_DOMAIN ) ?>
                <input type="text" class="js-expert-description">
            </label>
        </div>
        <div class="expert-review-settings-form__row">
            <button class="button button-primary js-add-expert"><?php echo __( 'Add New Expert', Plugin::TEXT_DOMAIN ) ?></button>
        </div>
    </div>

</div>
