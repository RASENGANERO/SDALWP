<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\PluginMyPopup\Icons;
use Wpshop\PluginMyPopup\Plugin;
use function Wpshop\PluginMyPopup\container;
use function Wpshop\PluginMyPopup\display_help_link;

/**
 * @var string  $namespace
 * @var WP_Post $post
 * @var array   $DEFAULTS
 */

$icons = container()->get( Icons::class );

$__name = function ( $name, $subname = null ) use ( $namespace ) {
    $result = sprintf( '%s[%s]', $namespace, $name );
    if ( $subname ) {
        printf( '%s[%s]', $result, $subname );

        return;
    }

    echo $result;
};

$__value = function ( $key, $subname = null, $default = null ) use ( $post ) {
    $value = get_post_meta( $post->ID, $key, true );
    if ( $subname && is_array( $value ) ) {
        if ( array_key_exists( $subname, $value ) ) {
            return $value[ $subname ];
        } else {
            return $default;
        }
    }

    return $value ?: $default;
};

$__checked = function ( $key, $subname = null, $default = null ) use ( $post ) {
    $value = get_post_meta( $post->ID, $key, true );
    if ( $subname && is_array( $value ) && isset( $value[ $subname ] ) ) {
        $value = $value[ $subname ];
    }
    if ( is_numeric( $value ) ) {
        checked( $value );
    } elseif ( null !== $default ) {
        checked( $default );
    }
};

?>

<div class="wpshop-meta-header">
    <?php echo __( 'Appearance popup', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'appearance' ) ?>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Padding', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_appearance][padding]"
                       value="<?php echo $__value( 'my_popup_appearance', 'padding', $DEFAULTS['my_popup_appearance']['padding'] ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset">
            </span>
            px
        </div>
    </div>
</div>


<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Border', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_border']['width'] ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_border][width]"
                       value="<?php echo $__value( 'my_popup_border', 'width' ) ?: $default ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                       data-preview_param="border:width">
            </span>
            px

            <span class="wpshop-meta-field-inline">
                <select name="<?php echo $namespace ?>[my_popup_border][style]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="border:style">
                    <?php
                    $options = [
                        'solid'  => 'Solid (' . __( 'solid', Plugin::TEXT_DOMAIN ) . ')',
                        'dotted' => 'Dotted (' . __( 'dotted', Plugin::TEXT_DOMAIN ) . ')',
                        'dashed' => 'Dashed (' . __( 'dashed', Plugin::TEXT_DOMAIN ) . ')',
                        'double' => 'Double (' . __( 'double', Plugin::TEXT_DOMAIN ) . ')',
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_border', 'style' ) ?: '', $val ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </span>

            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_border']['color'] ?>
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_border][color]"
                       class="js-wpshop-color-picker js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="border:color"
                       value="<?php echo $__value( 'my_popup_border', 'color' ) ?: $default ?>">
            </span>
            <?php echo __( 'rounding', Plugin::TEXT_DOMAIN ) ?>

            <span class="wpshop-meta-field-inline">
                <?php
                $default = $DEFAULTS['my_popup_border']['radius'];
                $value   = ( $__value( 'my_popup_border', 'radius' ) || $__value( 'my_popup_border', 'radius' ) == '0' ) ? $__value( 'my_popup_border', 'radius' ) : $default;
                ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_border][radius]"
                       value="<?php echo $value ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                       data-preview_param="border:radius">
            </span>
            px
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Shadow', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_shadow][x]"
                       value="<?php echo $__value( 'my_popup_shadow', 'x' ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       title="<?php echo __( 'Offset X', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_shadow][y]"
                       value="<?php echo $__value( 'my_popup_shadow', 'y' ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                       title="<?php echo __( 'Offset Y', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px

            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_shadow][blur]"
                       value="<?php echo $__value( 'my_popup_shadow', 'blur' ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       title="<?php echo __( 'Blur Radius', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px

            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_shadow][spread]"
                       value="<?php echo $__value( 'my_popup_shadow', 'spread' ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       title="<?php echo __( 'Spread Radius', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px

            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_shadow']['color'] ?>
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_shadow][color]"
                       class="js-wpshop-color-picker js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="shadow:color"
                       value="<?php echo $__value( 'my_popup_shadow', 'color' ) ?: $default ?>">
            </span>
            <?php echo __( 'opacity', Plugin::TEXT_DOMAIN ) ?>

            <span class="wpshop-meta-field-inline">
                <?php
                $default = $DEFAULTS['my_popup_shadow']['opacity'];
                $value   = ( $__value( 'my_popup_shadow', 'opacity' ) || $__value( 'my_popup_shadow', 'opacity' ) == '0' ) ? $__value( 'my_popup_shadow', 'opacity' ) : $default;
                ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_shadow][opacity]"
                       value="<?php echo $value ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       step="1"
                       min="0"
                       max="100"
                       data-preview_param="shadow:opacity">
            </span>
            %
        </div>
    </div>
</div>


<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Vertical alignment content', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <select name="<?php echo $namespace ?>[my_popup_content_vertical_alignment][value]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="cva:value">
                    <?php
                    $options = [
                        'flex-start' => __( 'Top', Plugin::TEXT_DOMAIN ),
                        'center'     => __( 'Center', Plugin::TEXT_DOMAIN ),
                        'flex-end'   => __( 'Bottom', Plugin::TEXT_DOMAIN ),
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_content_vertical_alignment', 'value' ) ?: '', $val ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </span>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Color text', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_content_color']['color'] ?>
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_content_color][color]"
                       class="js-wpshop-color-picker js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="content_color:color"
                       value="<?php echo $__value( 'my_popup_content_color', 'color' ) ?: $default ?>">
            </span>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Animation', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <select name="<?php echo $namespace ?>[my_popup_animation][value]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="animation:value">
                    <?php
                    $default = $DEFAULTS['my_popup_animation']['value'];
                    $options = [
                        [
                            'label' => 'Back',
                            'items' => [

                                'backInDown'  => 'backInDown',
                                'backInLeft'  => 'backInLeft',
                                'backInRight' => 'backInRight',
                                'backInUp'    => 'backInUp',
                            ],
                        ],
                        [
                            'label' => 'Bounce',
                            'items' => [
                                'bounceIn'      => 'bounceIn',
                                'bounceInDown'  => 'bounceInDown',
                                'bounceInLeft'  => 'bounceInLeft',
                                'bounceInRight' => 'bounceInRight',
                                'bounceInUp'    => 'bounceInUp',
                            ],
                        ],
                        [
                            'label' => 'Fade',
                            'items' => [
                                'fadeIn'            => 'fadeIn',
                                'fadeInDown'        => 'fadeInDown',
                                'fadeInLeft'        => 'fadeInLeft',
                                'fadeInRight'       => 'fadeInRight',
                                'fadeInUp'          => 'fadeInUp',
                                'fadeInTopLeft'     => 'fadeInTopLeft',
                                'fadeInTopRight'    => 'fadeInTopRight',
                                'fadeInBottomLeft'  => 'fadeInBottomLeft',
                                'fadeInBottomRight' => 'fadeInBottomRight',

                            ],
                        ],
                        [
                            'label' => 'Flip',
                            'items' => [
                                'flipInX' => 'flipInX',
                                'flipInY' => 'flipInY',
                            ],
                        ],
                        [
                            'label' => 'Rotate',
                            'items' => [
                                'rotateIn'          => 'rotateIn',
                                'rotateInDownLeft'  => 'rotateInDownLeft',
                                'rotateInDownRight' => 'rotateInDownRight',
                                'rotateInUpLeft'    => 'rotateInUpLeft',
                                'rotateInUpRight'   => 'rotateInUpRight',
                            ],
                        ],

                        'zoomIn' => 'zoomIn',
                    ];
                    ?>
                    <?php foreach ( $options as $k => $opt_group_or_label ): ?>
                        <?php if ( is_array( $opt_group_or_label ) ): ?>
                            <optgroup label="<?php echo $opt_group_or_label['label'] ?>">
                            <?php foreach ( $opt_group_or_label['items'] as $val => $label ): ?>
                                <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_animation', 'value' ) ?: $default, $val ) ?>><?php echo $label ?></option>
                            <?php endforeach ?>
                        </optgroup>
                        <?php else: ?>
                            <option value="<?php echo $k ?>"<?php selected( $__value( 'my_popup_animation', 'value' ) ?: $default, $k ) ?>><?php echo $opt_group_or_label ?></option>
                        <?php endif ?>
                    <?php endforeach; ?>
                </select>
            </span>
        </div>
    </div>
</div>


<div class="wpshop-meta-hr"></div>


<div class="wpshop-meta-header">
    <?php echo __( 'Background', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'background' ) ?>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Background color', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_background_color']['color'] ?>
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_background_color][color]"
                       class="js-wpshop-color-picker js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="bg:color"
                       value="<?php echo $__value( 'my_popup_background_color', 'color', $default ) ?>">
            </span>

            <?php echo __( 'opacity', Plugin::TEXT_DOMAIN ) ?>

            <span class="wpshop-meta-field-inline">
                <?php
                $default = $DEFAULTS['my_popup_background_color']['opacity'];
                $value   = $__value( 'my_popup_background_color', 'opacity', $default );
                ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_background_color][opacity]"
                       value="<?php echo $value ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       step="1"
                       min="0"
                       max="100">
            </span>
            %
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Background image', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <input type="text" name="<?php echo $namespace ?>[my_popup_background_image][image]"
                   value="<?php echo $__value( 'my_popup_background_image', 'image' ) ?>"
                   class="js-my-popup-form-element-url js-my-popup-change-observable js-my-popup-preset"
                   data-preview_param="bg_image:image">
            <button type="button"
                    class="button js-my-popup-form-element-browse"><?php echo __( 'Choose', Plugin::TEXT_DOMAIN ) ?></button>

            <span class="wpshop-meta-field-inline">
                <select name="<?php echo $namespace ?>[my_popup_background_image][position]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="bg_image:position">
                    <?php
                    $default = $DEFAULTS['my_popup_background_image']['position'];
                    $options = [
                        [
                            'label' => __( 'Top', Plugin::TEXT_DOMAIN ),
                            'items' => [
                                'top_left'   => __( 'Top left', Plugin::TEXT_DOMAIN ),
                                'top_center' => __( 'Top center', Plugin::TEXT_DOMAIN ),
                                'top_right'  => __( 'Top right', Plugin::TEXT_DOMAIN ),
                            ],
                        ],
                        [
                            'label' => __( 'Middle', Plugin::TEXT_DOMAIN ),
                            'items' => [
                                'center_left'   => __( 'Middle left', Plugin::TEXT_DOMAIN ),
                                'center_center' => __( 'Middle center', Plugin::TEXT_DOMAIN ),
                                'center_right'  => __( 'Middle right', Plugin::TEXT_DOMAIN ),
                            ],
                        ],
                        [
                            'label' => __( 'Bottom', Plugin::TEXT_DOMAIN ),
                            'items' => [
                                'bottom_left'   => __( 'Bottom left', Plugin::TEXT_DOMAIN ),
                                'bottom_center' => __( 'Bottom center', Plugin::TEXT_DOMAIN ),
                                'bottom_right'  => __( 'Bottom right', Plugin::TEXT_DOMAIN ),
                            ],
                        ],
                    ];
                    ?>
                    <?php foreach ( $options as $opt_group ): ?>
                        <optgroup label="<?php echo $opt_group['label'] ?>">
                        <?php foreach ( $opt_group['items'] as $val => $label ): ?>
                            <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_background_image', 'position' ) ?: $default, $val ) ?>><?php echo $label ?></option>
                        <?php endforeach ?>
                    </optgroup>
                    <?php endforeach ?>
                </select>
            </span>

            <span class="wpshop-meta-field-inline">
                <select name="<?php echo $namespace ?>[my_popup_background_image][repeat]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="bg_image:repeat">
                    <?php
                    $options = [
                        'no-repeat' => __( 'Do not repeat', Plugin::TEXT_DOMAIN ),
                        'repeat-x'  => __( 'Repeat on X', Plugin::TEXT_DOMAIN ),
                        'repeat-y'  => __( 'Repeat on Y', Plugin::TEXT_DOMAIN ),
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_background_image', 'repeat' ) ?: '', $val ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </span>

            <span class="wpshop-meta-field-inline">
                <select name="<?php echo $namespace ?>[my_popup_background_image][size]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="bg_image:size">
                    <?php
                    $options = [
                        'auto'    => 'Auto (' . __( 'don\'t stretch', Plugin::TEXT_DOMAIN ) . ')',
                        'cover'   => 'Cover (' . __( 'stretch', Plugin::TEXT_DOMAIN ) . ')',
                        'contain' => 'Contain (' . __( 'contain', Plugin::TEXT_DOMAIN ) . ')',
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_background_image', 'size' ) ?: '', $val ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </span>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Darken the background image', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">

                <label class="wpshop-meta-checkbox">
                    <input type="hidden" name="<?php echo $namespace ?>[my_popup_bg_image_overlay][is_enabled]" value="0">
                    <input type="checkbox" name="<?php $__name( 'my_popup_bg_image_overlay', 'is_enabled' ) ?>" value="1"
                           class="js-my-popup-change-observable js-my-popup-preset"
                           data-preview_param="blackout_bg:is_enabled"
                        <?php $__checked( 'my_popup_bg_image_overlay', 'is_enabled' ) ?>>
                    <span class="wpshop-meta-checkbox__label"></span>
                </label>
            </span>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Darken parameters', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
        <span class="wpshop-meta-field-inline">
            <?php $default = $DEFAULTS['my_popup_bg_image_overlay']['color'] ?>
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_bg_image_overlay][color]"
                       class="js-wpshop-color-picker js-my-popup-change-observable js-my-popup-preset"
                       value="<?php echo $__value( 'my_popup_bg_image_overlay', 'color' ) ?: $default ?>">
            </span>
            <?php echo __( 'opacity', Plugin::TEXT_DOMAIN ) ?>

            <span class="wpshop-meta-field-inline">
                <?php
                $default = $DEFAULTS['my_popup_bg_image_overlay']['opacity'];
                $value   = $__value( 'my_popup_bg_image_overlay', 'opacity', $default );
                ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_bg_image_overlay][opacity]"
                       value="<?php echo $value ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       step="1"
                       min="0"
                       max="100">
            </span>
            %
        </div>
    </div>
</div>


<div class="wpshop-meta-hr"></div>


<div class="wpshop-meta-header">
    <?php echo __( 'Icon', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'icon' ) ?>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field__label">
        <label><?php echo __( 'Icon image', Plugin::TEXT_DOMAIN ) ?></label>
    </div>
    <div class="wpshop-meta-field__body">
        <input type="text" name="<?php echo $namespace ?>[my_popup_icon][image]"
               value="<?php echo $__value( 'my_popup_icon', 'image', $DEFAULTS['my_popup_icon']['image'] ) ?>"
               class="js-my-popup-form-element-url js-my-popup-change-observable js-my-popup-preset">
        <button type="button" class="button js-my-popup-form-element-browse"><?php echo __( 'Choose', Plugin::TEXT_DOMAIN ) ?></button>

        <span class="wpshop-meta-field-inline">
            <select name="<?php echo $namespace ?>[my_popup_icon][position]"
                    class="js-my-popup-change-observable js-my-popup-preset">
                <?php
                $default = $DEFAULTS['my_popup_icon']['position'];
                $options = [
                    [
                        'label' => __( 'Left', Plugin::TEXT_DOMAIN ),
                        'items' => [
                            'left-top'    => __( 'Left top', Plugin::TEXT_DOMAIN ),
                            'left-center' => __( 'Left center', Plugin::TEXT_DOMAIN ),
                            'left-bottom' => __( 'Left bottom', Plugin::TEXT_DOMAIN ),
                        ],
                    ],
                    [
                        'label' => __( 'Top', Plugin::TEXT_DOMAIN ),
                        'items' => [
                            'top-left'   => __( 'Top left', Plugin::TEXT_DOMAIN ),
                            'top-center' => __( 'Top center', Plugin::TEXT_DOMAIN ),
                            'top-right'  => __( 'Top right', Plugin::TEXT_DOMAIN ),
                        ],
                    ],
                    [
                        'label' => __( 'Right', Plugin::TEXT_DOMAIN ),
                        'items' => [
                            'right-top'    => __( 'Right top', Plugin::TEXT_DOMAIN ),
                            'right-center' => __( 'Right center', Plugin::TEXT_DOMAIN ),
                            'right-bottom' => __( 'Right bottom', Plugin::TEXT_DOMAIN ),
                        ],
                    ],
                    [
                        'label' => __( 'Bottom', Plugin::TEXT_DOMAIN ),
                        'items' => [
                            'bottom-left'   => __( 'Bottom left', Plugin::TEXT_DOMAIN ),
                            'bottom-center' => __( 'Bottom center', Plugin::TEXT_DOMAIN ),
                            'bottom-right'  => __( 'Bottom right', Plugin::TEXT_DOMAIN ),
                        ],
                    ],
                ];
                ?>
                <?php foreach ( $options as $opt_group ): ?>
                    <optgroup label="<?php echo $opt_group['label'] ?>">
                        <?php foreach ( $opt_group['items'] as $val => $label ): ?>
                            <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_icon', 'position', $default ), $val ) ?>><?php echo $label ?></option>
                        <?php endforeach ?>
                    </optgroup>
                <?php endforeach ?>
            </select>
        </span>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Width and height', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_icon][width]"
                       value="<?php echo $__value( 'my_popup_icon', 'width', $DEFAULTS['my_popup_icon']['width'] ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       title="<?php echo __( 'Icon width', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_icon][height]"
                       value="<?php echo $__value( 'my_popup_icon', 'height', $DEFAULTS['my_popup_icon']['height'] ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       title="<?php echo __( 'Icon height', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo _x( 'Padding', 'icon gap', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_icon][gap]"
                       value="<?php echo $__value( 'my_popup_icon', 'gap', $DEFAULTS['my_popup_icon']['gap'] ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       title="<?php echo __( 'Icon Padding', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px
        </div>
    </div>
</div>

<div class="wpshop-meta-hr"></div>

<div class="wpshop-meta-header">
    <?php echo __( 'Overlay', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'overlay' ) ?>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Enable background overlay', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <label class="wpshop-meta-checkbox">
                    <input type="hidden" name="<?php echo $namespace ?>[my_popup_enable_overlay][is_enabled]" value="0">
                    <input type="checkbox" name="<?php $__name( 'my_popup_enable_overlay', 'is_enabled' ) ?>" value="1"
                           class="js-my-popup-change-observable js-my-popup-preset"
                        <?php $__checked( 'my_popup_enable_overlay', 'is_enabled', $DEFAULTS['my_popup_enable_overlay']['is_enabled'] ) ?>>
                    <span class="wpshop-meta-checkbox__label"></span>
                </label>
            </span>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Background color', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_overlay']['color'] ?>
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_overlay][color]"
                       class="js-wpshop-color-picker js-my-popup-change-observable js-my-popup-preset"
                       value="<?php echo $__value( 'my_popup_overlay', 'color' ) ?: $default ?>">
            </span>
            <?php echo __( 'opacity', Plugin::TEXT_DOMAIN ) ?>
            <span class="wpshop-meta-field-inline">
                <?php
                $default = $DEFAULTS['my_popup_overlay']['opacity'];
                $value   = ( $__value( 'my_popup_overlay', 'opacity' ) || $__value( 'my_popup_overlay', 'opacity' ) == '0' ) ? $__value( 'my_popup_overlay', 'opacity' ) : $default;
                ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_overlay][opacity]"
                       value="<?php echo $value ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                       data-preview_param="overlay:opacity">
            </span>
            %
        </div>
    </div>
</div>


<div class="wpshop-meta-hr"></div>


<div class="wpshop-meta-header">
    <?php echo __( 'Close button', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'close-button' ) ?>
</div>


<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Show close button', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">

                <label class="wpshop-meta-checkbox">
                    <input type="hidden" name="<?php echo $namespace ?>[my_popup_show_close_button][is_enabled]" value="0">
                    <input type="checkbox"
                           name="<?php $__name( 'my_popup_show_close_button', 'is_enabled' ) ?>"
                           value="1"
                           class="js-my-popup-change-observable"<?php $__checked( 'my_popup_show_close_button', 'is_enabled', $DEFAULTS['my_popup_show_close_button']['is_enabled'] ) ?>>
                    <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'through', Plugin::TEXT_DOMAIN ) ?>
                </label>

                <?php $default = $DEFAULTS['my_popup_show_close_button']['value'] ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_show_close_button][value]"
                       value="<?php echo $__value( 'my_popup_show_close_button', 'value' ) ?: $default ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable" size="4">
                <?php echo __( 'sec.', Plugin::TEXT_DOMAIN ) ?>
            </span>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Icon', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <?php
            $default = $DEFAULTS['my_popup_close_button_icon']['value'];
            $value   = $__value( 'my_popup_close_button_icon', 'value' ) ?: $default;
            ?>
            <div class="my-popup-close-btn-icons js-my-popup-close-btn-icon-container">
                <?php foreach ( $icons->get_icon_list() as $name ): ?>
                    <button class="my-popup-close-btn-icons__item<?php echo $value == $name ? ' selected' : '' ?> js-my-popup-close-btn-icon" data-value="<?php echo $name ?>">
                        <?php echo $icons->get_icon( $name, [
                            'color'  => 'black',
                            'width'  => '20',
                            'height' => '20',
                        ] ) ?>
                    </button>
                <?php endforeach ?>
                <input type="hidden"
                       class="js-my-popup-change-observable js-my-popup-preset js-my-popup-close-btn-icon-value"
                       name="<?php echo $namespace ?>[my_popup_close_button_icon][value]"
                       value="<?php echo $value ?>">

            </div>
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Location', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <select name="<?php echo $namespace ?>[my_popup_close_button_location][value]"
                        class="js-my-popup-change-observable js-my-popup-preset"
                        data-preview_param="close_l:value">
                    <?php
                    $default = $DEFAULTS['my_popup_close_button_location']['value'];
                    $options = [
                        'inside'  => __( 'Inside', Plugin::TEXT_DOMAIN ),
                        'outside' => __( 'Outside', Plugin::TEXT_DOMAIN ),
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_close_button_location', 'value' ) ?: $default, $val ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </span>
            <?php echo __( 'Gap', Plugin::TEXT_DOMAIN ) ?>
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_close_button_location][gap]"
                       value="<?php echo $__value( 'my_popup_close_button_location', 'gap', $DEFAULTS['my_popup_close_button_location']['gap'] ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       title="<?php echo __( 'Close Button Padding', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Size', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_close_button_size][value]"
                       value="<?php echo $__value( 'my_popup_close_button_size', 'value', $DEFAULTS['my_popup_close_button_size']['value'] ) ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       min="0"
                       step="1"
                       title="<?php echo __( 'Close Button Padding', Plugin::TEXT_DOMAIN ) ?>">
            </span>
            px
        </div>
    </div>
</div>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Color', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_close_button_color']['color'] ?>
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_close_button_color][color]"
                       class="js-wpshop-color-picker js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="close:color"
                       value="<?php echo $__value( 'my_popup_close_button_color', 'color' ) ?: $default ?>">
            </span>
            <?php echo __( 'opacity', Plugin::TEXT_DOMAIN ) ?>

            <span class="wpshop-meta-field-inline">
                <?php
                $default = $DEFAULTS['my_popup_close_button_color']['opacity'];
                $value   = $__value( 'my_popup_close_button_color', 'opacity', $default );
                ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_close_button_color][opacity]"
                       value="<?php echo $value ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset"
                       step="1"
                       min="0"
                       max="100">
            </span>
            %
        </div>
    </div>
</div>
