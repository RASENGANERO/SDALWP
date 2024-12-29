<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\PluginMyPopup\Plugin;
use function Wpshop\PluginMyPopup\display_help_link;

/**
 * @var string  $namespace
 * @var WP_Post $post
 * @var array   $DEFAULTS
 */


$__name = function ( $name, $subname = null ) use ( $namespace ) {
    $result = sprintf( '%s[%s]', $namespace, $name );
    if ( $subname ) {
        printf( '%s[%s]', $result, $subname );

        return;
    }

    echo $result;
};

$__value = function ( $key, $subname = null ) use ( $post ) {
    $value = get_post_meta( $post->ID, $key, true );
    if ( $subname && is_array( $value ) && isset( $value[ $subname ] ) ) {
        return $value[ $subname ];
    }

    return $value;
};

$__checked = function ( $key, $subname = null, $default = null, $current = true ) use ( $post ) {
    $value = get_post_meta( $post->ID, $key, true );
    if ( $subname && is_array( $value ) && isset( $value[ $subname ] ) ) {
        $value = $value[ $subname ];
    }
    if ( is_numeric( $value ) ) {
        checked( $value, $current );
    } elseif ( null !== $default ) {
        checked( $default, $current );
    }
};

$__checked_position = function ( $key, $subname = null, $default = null, $current = true ) use ( $post ) {
    $value = get_post_meta( $post->ID, $key, true );
    if ( $subname && is_array( $value ) && isset( $value[ $subname ] ) ) {
        $value = $value[ $subname ];
    }
    if ( $value ) {
        checked( $value, $current );
    } elseif ( null !== $default ) {
        checked( $default, $current );
    }
};

?>

<div class="wpshop-meta-header">
    <?php echo __( 'Location and sizes of popup on the page', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'position' ) ?>
</div>

<div class="wpshop-meta-row">
    <div class="mypopup-sizes-container">
        <div class="mypopup-position">
            <?php
            $options = [
                'top_left'      => __( 'top left', Plugin::TEXT_DOMAIN ),
                'top_center'    => __( 'top center', Plugin::TEXT_DOMAIN ),
                'top_right'     => __( 'top right', Plugin::TEXT_DOMAIN ),
                'center_left'   => __( 'center left', Plugin::TEXT_DOMAIN ),
                'center_center' => __( 'center center', Plugin::TEXT_DOMAIN ),
                'center_right'  => __( 'center right', Plugin::TEXT_DOMAIN ),
                'bottom_left'   => __( 'bootom left', Plugin::TEXT_DOMAIN ),
                'bottom_center' => __( 'bottom center', Plugin::TEXT_DOMAIN ),
                'bottom_right'  => __( 'bottom right', Plugin::TEXT_DOMAIN ),
            ];
            ?>
            <?php foreach ( $options as $val => $label ) : ?>
                <input type="radio" name="<?php echo $namespace ?>[my_popup_position][value]" class="js-my-popup-change-observable js-my-popup-preset" data-preview_param="position:value" id="mypopup_position_<?php echo $val ?>" value="<?php echo $val ?>"
                    <?php $__checked_position( 'my_popup_position', 'value', $DEFAULTS['my_popup_position']['value'], $val ) ?>>
                <label for="mypopup_position_<?php echo $val ?>"><?php echo $label ?></label>
            <?php endforeach ?>
        </div>

        <div class="mypopup-sizes">
            <div class="mypopup-sizes__width">
                <label for="" class="mypopup-sizes__label"><?php echo __( 'Width', Plugin::TEXT_DOMAIN ) ?>:</label>

                <span class="wpshop-meta-field-inline">
                    <input type="number"
                           name="<?php echo $namespace ?>[my_popup_position][width]"
                           value="<?php echo $__value( 'my_popup_position', 'width' ) ?>"
                           class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                           data-preview_param="position:width">
                </span>

                <select name="<?php echo $namespace ?>[my_popup_position][width_units]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="position:width_units">
                    <?php
                    $options = [
                        'px' => 'px',
                        'vw' => 'vw',
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_position', 'width_units' ) ?: '', $val ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mypopup-sizes__height">
                <label for="" class="mypopup-sizes__label"><?php echo __( 'Height', Plugin::TEXT_DOMAIN ) ?>:</label>

                <span class="wpshop-meta-field-inline">
                    <input type="number"
                           name="<?php echo $namespace ?>[my_popup_position][height]"
                           value="<?php echo $__value( 'my_popup_position', 'height' ) ?>"
                           class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                           data-preview_param="position:height">
                </span>

                <select name="<?php echo $namespace ?>[my_popup_position][height_units]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="position:height_units">
                    <?php
                    $options = [
                        'px' => 'px',
                        'vh' => 'vh',
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ) : ?>
                        <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_position', 'height_units' ) ?: '', $val ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mypopup-sizes__description">
                <?php echo __( 'vw and vh - units of measure, relative to the width and height of the browser', Plugin::TEXT_DOMAIN ) ?>
                <br>
                <?php echo __( '100vw - means 100 percent of the width of the browser, i.e. the popup will be full width', Plugin::TEXT_DOMAIN ) ?>
            </div>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_cookies_type][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_cookies_type', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_cookies_type', 'is_enabled', $DEFAULTS['my_popup_cookies_type']['is_enabled'] ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php _e( 'Show Reapply through', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <span class="wpshop-meta-field-inline">
        <?php $default = $DEFAULTS['my_popup_cookies_type']['value'] ?>
        <input type="number"
               name="<?php echo $namespace ?>[my_popup_cookies_type][value]"
               value="<?php echo $__value( 'my_popup_cookies_type', 'value' ) ?: $default ?>"
               class="wpshop-meta-field--size-xs" size="4">
    </span>

    <select name="<?php echo $namespace ?>[my_popup_cookies_type][mode]">
        <?php
        $options = [
            'minutes' => __( 'minutes', Plugin::TEXT_DOMAIN ),
            'hours'   => __( 'hours', Plugin::TEXT_DOMAIN ),
            'days'    => __( 'days', Plugin::TEXT_DOMAIN ),
            'months'  => __( 'months', Plugin::TEXT_DOMAIN ),
        ];
        ?>
        <?php foreach ( $options as $val => $label ) : ?>
            <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_cookies_type', 'mode' ) ?: '', $val ) ?>><?php echo $label ?></option>
        <?php endforeach ?>
    </select>
</div>


<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_close_overlay][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_close_overlay', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_close_overlay', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Prevent closing popup by clicking on the overlay', Plugin::TEXT_DOMAIN ) ?>
    </label>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_close_esc][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_close_esc', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_close_esc', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Prevent closing popup by ESC key', Plugin::TEXT_DOMAIN ) ?>
    </label>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_page_scrolling][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_page_scrolling', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_page_scrolling', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Disable page scrolling', Plugin::TEXT_DOMAIN ) ?>
    </label>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_close_time][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_close_time', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_close_time', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Close popup through', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <span class="wpshop-meta-field-inline">
        <input type="number"
               name="<?php echo $namespace ?>[my_popup_close_time][value]"
               value="<?php echo $__value( 'my_popup_close_time', 'value' ) ?>"
               class="wpshop-meta-field--size-xs" size="4">
    </span>
    <?php echo __( 'sec.', Plugin::TEXT_DOMAIN ) ?>
</div>


<div class="wpshop-meta-header">
    <?php echo __( 'Display on devices', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'devices' ) ?>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_display_desktop][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_display_desktop', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_display_desktop', 'is_enabled', $DEFAULTS['my_popup_display_desktop']['is_enabled'] ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'On the computer', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_display_tablet][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_display_tablet', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_display_tablet', 'is_enabled', $DEFAULTS['my_popup_display_tablet']['is_enabled'] ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'On tablets', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_display_mobile][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_display_mobile', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_display_mobile', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'On mobiles', Plugin::TEXT_DOMAIN ) ?>
    </label>
</div>
