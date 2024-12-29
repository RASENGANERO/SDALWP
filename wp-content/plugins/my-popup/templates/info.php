<?php

use Wpshop\PluginMyPopup\Plugin;
use Wpshop\PluginMyPopup\Settings\PluginOptions;
use function Wpshop\PluginMyPopup\container;

if ( ! defined( 'WPINC' ) ) {
    die;
}

$options = container()->get( PluginOptions::class );

?>
<div class="wrap">
    <h2>My Popup</h2>

    <div class="wpshop-widgets">
        <div class="wpshop-widget">
            <div class="wpshop-widget__icon">
                <img src="<?php echo plugins_url( 'assets/admin/images/widget-docs.svg', MY_POPUP_FILE ) ?>" alt="">
            </div>
            <div class="wpshop-widget__header">
                <a href="https://support.wpshop.ru/docs/plugins/my-popup" target="_blank" rel="noopener"><?php echo __( 'Documentation', Plugin::TEXT_DOMAIN ) ?></a>
            </div>
            <div class="wpshop-widget__description"><?php echo __( 'If you have a question about our product, perhaps the answer is already in our documentation.', Plugin::TEXT_DOMAIN ) ?></div>
        </div><!--.wpshop-widget-->

        <div class="wpshop-widget wpshop-widget--color-purple">
            <div class="wpshop-widget__icon">
                <img src="<?php echo plugins_url( 'assets/admin/images/widget-qa.svg', MY_POPUP_FILE ) ?>" alt="">
            </div>
            <div class="wpshop-widget__header">
                <a href="https://support.wpshop.ru/fag_tag/my-popup/" target="_blank" rel="noopener"><?php echo __( 'Questions and answers', Plugin::TEXT_DOMAIN ) ?></a>
            </div>
            <div class="wpshop-widget__description"><?php echo __( 'Section of frequently asked questions and answers to them. You can quickly find the answer to a question.', Plugin::TEXT_DOMAIN ) ?></div>
        </div><!--.wpshop-widget-->

        <div class="wpshop-widget wpshop-widget--color-red">
            <div class="wpshop-widget__icon">
                <img src="<?php echo plugins_url( 'assets/admin/images/widget-video.svg', MY_POPUP_FILE ) ?>" alt="">
            </div>
            <div class="wpshop-widget__header">
                <a href="https://www.youtube.com/watch?v=22ahf1M1TGM" target="_blank" rel="noopener"><?php echo __( 'Video instructions', Plugin::TEXT_DOMAIN ) ?></a>
            </div>
            <div class="wpshop-widget__description"><?php echo __( 'An overview of the plugin and functions in video format. Subscribe to the channel so you don\'t miss it.', Plugin::TEXT_DOMAIN ) ?></div>
        </div>
    </div>

    <div class="wpshop-widget">
        <?php echo __( 'Our pages:', Plugin::TEXT_DOMAIN ) ?>
        <a href="https://vk.com/wpshop" target="_blank" rel="noopener" class="wpshop-widget-social-icon wpshop-widget-social-icon--vk"></a>
        <a href="https://t.me/wpshop" target="_blank" rel="noopener" class="wpshop-widget-social-icon wpshop-widget-social-icon--telegram"></a>

        <a href="https://wpshop.ru/partner" target="_blank" rel="noopener" class="wpshop-widget-partners"><?php echo __( 'Affiliate program', Plugin::TEXT_DOMAIN ) ?></a>
        <a href="https://wpshop.ru/" target="_blank" rel="noopener" class="wpshop-widget-partners">WPShop.ru</a>
    </div>

    <div class="wpshop-widgets">
        <div class="wpshop-widget">
            <div class="wpshop-widget-license">
                <form action="" method="post">
                    <label for="license"><?php echo __( 'License key', Plugin::TEXT_DOMAIN ) ?>:</label>
                    <?php if ( $options->license_verify ): ?>
                        <p class="wpshop-widget-license__activated">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16"><path d="M256 48c114.88 0 208 93.12 208 208s-93.12 208-208 208S48 370.88 48 256 141.12 48 256 48m0-48a256.05 256.05 0 00-99.66 491.86A256.05 256.05 0 00355.66 20.14 254.47 254.47 0 00256 0zm-23.27 361.24l149-149a24 24 0 00-33.94-33.94l-132 132-55.55-55.55a24 24 0 10-33.94 33.94l72.52 72.52a24 24 0 0033.94 0z" fill="#06c100"/></svg>
                            Plugin successfully activated.
                        </p>
                    <?php endif; ?>
                    <input name="license" type="text" value="" id="license" placeholder="<?php echo $options->license ? '******' : '' ?>">
                    <?php if ( $options->license_error ): ?>
                        <div class="error-message">
                            <?php echo esc_html( $options->license_error ) ?>
                        </div>
                    <?php endif ?>
                    <?php wp_nonce_field( 'my-popup-activate', 'my_popup_nonce' ) ?>
                    <button type="submit" class="button"><?php echo __( 'Activate', Plugin::TEXT_DOMAIN ) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
