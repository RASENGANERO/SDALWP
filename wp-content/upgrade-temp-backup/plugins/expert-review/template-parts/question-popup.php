<?php

/**
 * @version 1.6.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Plugin;

?>

<template id="expert-review-question-popup">
    <div class="expert-review-popup-holder js-expert-review-popup">
        <div class="expert-review-popup">
            <span class="expert-review-popup__close js-expert-review-close-mark">&times;</span>
            <div class="expert-review-popup__content">
                <form>
                    <label>
                        <?php echo __( 'Name', Plugin::TEXT_DOMAIN ) ?>:
                        <input type="text" name="name" class="required">
                    </label>
                    <label>
                        <?php echo __( 'Phone', Plugin::TEXT_DOMAIN ) ?>:
                        <input type="text" name="_p" class="required"></label>
                    <label>
                        <?php echo __( 'Email', Plugin::TEXT_DOMAIN ) ?>:
                        <input type="text" name="_a" class="required"></label>
                    <input type="hidden" name="email">
                    <label>
                        <?php echo __( 'Question', Plugin::TEXT_DOMAIN ) ?>:
                        <textarea name="text" class="required"></textarea></label>
                    <label>
                        <input type="checkbox" name="consent" value="1" class="required">
                        <?php echo __( 'Consent to the processing of personal data', Plugin::TEXT_DOMAIN ) ?>
                    </label>
                    <button type="submit" class="expert-review-popup__submit"><?php echo __( 'Submit', Plugin::TEXT_DOMAIN ) ?></button>
                    <span class="expert-review-popup__cancel js-expert-review-cancel"><?php echo __( 'Cancel', Plugin::TEXT_DOMAIN ) ?></span>
                    <input type="hidden" name="settings">
                </form>
            </div>
        </div>
    </div>
</template>
