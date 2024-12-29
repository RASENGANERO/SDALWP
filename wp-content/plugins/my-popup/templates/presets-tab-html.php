<?php

use Wpshop\PluginMyPopup\Plugin;
use Wpshop\PluginMyPopup\PopupPresets;
use function Wpshop\PluginMyPopup\container;
use function Wpshop\PluginMyPopup\display_help_link;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var string  $namespace
 * @var WP_Post $post
 * @var array   $DEFAULTS
 * @var bool    $is_dev
 */


$popupPresets = container()->get( PopupPresets::class );
$presets      = $popupPresets->get_presets();

?>
<div class="wpshop-meta-header">
    <?php echo __( 'Presets', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'presets' ) ?>
</div>

<?php if ( $is_dev ): ?>
    <div class="wpshop-meta-row">
        <div class="wpshop-meta-field">
            <span class="wpshop-meta-field-inline">
                <label>
                    Name:
                    <input type="text" class="js-popup-preset-name">
                </label>
                <label>
                    Image:
                    <input type="text" class="js-popup-preset-image">
                </label>
                <button class="button js-popup-save-preset"><?php echo __( 'Save Current as Preset', Plugin::TEXT_DOMAIN ) ?></button>
            </span>
        </div>
    </div>
<?php endif ?>

<div class="mypopup-presets">
    <?php foreach ( $presets as $id => $preset ): ?>
        <div class="mypopup-preset js-preset-row">
            <div class="mypopup-preset__body">
                <div class="mypopup-preset__select">
                    <button class="button js-my-popup-select-preset" value="<?php echo $id ?>"><?php echo __( 'Activate', Plugin::TEXT_DOMAIN ) ?></button>
                </div>

                <?php if ( isset( $preset['image'] ) ): ?>
                    <img src="<?php echo $popupPresets->image_url( $preset['image'] ) ?>" alt="">
                <?php endif ?>
            </div>

            <?php if ( $is_dev ): ?>
                <span class="mypopup-preset__remove js-my-popup-remove-preset" data-id="<?php echo $id ?>"><?php echo __( 'Remove', Plugin::TEXT_DOMAIN ) ?></span>
            <?php endif ?>
        </div>
    <?php endforeach ?>
</div>
