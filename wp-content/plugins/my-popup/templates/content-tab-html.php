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

    if ( ! metadata_exists( 'post', $post->ID, $key ) ) {
        return false;
    }

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
}

?>

<div class="wpshop-meta-header">
    <?php echo __( 'Content', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'content' ) ?>
</div>

<?php $my_popup_editor_content = get_post_meta( $post->ID, 'my_popup_editor_content', true ) ?>

<?php wp_editor( $my_popup_editor_content, 'my_popup_editor_content', [
    'textarea_name'  => 'my_popup_editor_content',
    'default_editor' => 'tinymce',
    'textarea_rows'  => 15,
] ) ?>

<br>

<div class="mypopup-content-shortcodes">
    <div class="mypopup-content-shortcodes__box">
        <div class="mypopup-content-shortcodes__header">
            <?php echo __( 'Buttons', Plugin::TEXT_DOMAIN ) ?>:
            <?php display_help_link( 'content_buttons' ) ?>
        </div>
        [mypopup_button tag="a" href="#"]<?php echo __( 'Link', Plugin::TEXT_DOMAIN ) ?>[/mypopup_button]<br>
        [mypopup_button tag="a" href="#" target="_blank"]<?php echo __( 'New tab', Plugin::TEXT_DOMAIN ) ?>[/mypopup_button]<br>
        [mypopup_button tag="a" href="#" background="#00a327"]<?php echo __( 'Bg', Plugin::TEXT_DOMAIN ) ?>[/mypopup_button]<br>
        [mypopup_button class="js-mypopup-modal-close"]<?php echo __( 'Close', Plugin::TEXT_DOMAIN ) ?>[/mypopup_button]
    </div>
    <div class="mypopup-content-shortcodes__box">
        <div class="mypopup-content-shortcodes__header">
            <?php echo __( 'Countdown timer', Plugin::TEXT_DOMAIN ) ?>:
            <?php display_help_link( 'content_countdown' ) ?>
        </div>
        [mypopup_countdown minutes=10] — <?php echo __( 'timer on 10 minutes', Plugin::TEXT_DOMAIN ) ?><br>
        [mypopup_countdown expire_date="<?php echo date('Y-m-d 23:59:59') ?>"] — <?php echo __( 'timer on date', Plugin::TEXT_DOMAIN ) ?><br>
        [mypopup_countdown hours=1 background="#f56" color="#fff"] — <?php echo __( 'background and color', Plugin::TEXT_DOMAIN ) ?><br>
        [mypopup_countdown minutes=10 style=2] — <?php echo __( 'style 1, 2 or 3', Plugin::TEXT_DOMAIN ) ?>
    </div>
</div>

<br>

<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__label">
            <label><?php echo __( 'Inline styles', Plugin::TEXT_DOMAIN ) ?></label>
        </div>
        <div class="wpshop-meta-field__body">
                <textarea name="<?php echo $namespace ?>[my_popup_inline_styles]"
                          rows="6"
                          class="wpshop-meta-field js-my-popup-change-observable js-my-popup-preset"
                          data-preview_param="i_s"><?php echo esc_html( $__value( 'my_popup_inline_styles' ) ) ?></textarea>
            <p class="description"><?php echo __( 'Use #{{id}} as popup id, for example:' ) ?></p>
            <pre><code>#{{id}} .mypopup-body p { font-weight: 800; }</code></pre>
        </div>
    </div>
</div>

<?php
$html_codes = $__value( 'my_popup_prepared_code' );
$html_codes = is_array( $html_codes ) ? $html_codes : [ $html_codes ];
?>
<div class="js-html-code-container">
    <?php foreach ( $html_codes as $i => $html_code_value ): ?>
        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'HTML Code', Plugin::TEXT_DOMAIN ) ?></label>
                    <p><?php echo __( 'You can use this shortcode in content', Plugin::TEXT_DOMAIN ) ?><br>
                        <code class="js-shortcode-example">[mypopup_html id=<?php echo $i + 1 ?>]</code>
                    </p>
                    <?php if ( $i ): ?>
                        <a href="#" class="js-remove-html-code"><?php esc_html_e( 'Remove', Plugin::TEXT_DOMAIN ); ?></a>
                    <?php endif ?>
                </div>
                <div class="wpshop-meta-field__body">
                <textarea name="<?php echo $namespace ?>[my_popup_prepared_code][]"
                          id="my_popup_prepared_code_<?php echo $i + 1 ?>"
                          rows="6"
                          class="wpshop-meta-field js-my-popup-change-observable js-my-popup-preset"
                          data-preview_param="pcode[]"><?php echo esc_html( $html_code_value ) ?></textarea>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<div class="wpshop-meta-row">
    <button class="button js-add-html-code"><?php esc_html_e( 'Add HTML Code Input', Plugin::TEXT_DOMAIN ); ?></button>
</div>
<template id="tmpl-my-popup-html-code">
    <div class="wpshop-meta-row">
        <div class="wpshop-meta-field">
            <div class="wpshop-meta-field__label">
                <label><?php echo __( 'HTML Code', Plugin::TEXT_DOMAIN ) ?></label>
                <p><?php echo __( 'You can use this shortcode in content', Plugin::TEXT_DOMAIN ) ?><br>
                    <code class="js-shortcode-example">[mypopup_html id={{data.shortcode_id}}]</code>
                </p>
                <a href="#" class="js-remove-html-code"><?php esc_html_e( 'Remove', Plugin::TEXT_DOMAIN ); ?></a>
            </div>
            <div class="wpshop-meta-field__body">
                <textarea name="<?php echo $namespace ?>[my_popup_prepared_code][]"
                          id="{{data.input_id}}"
                          rows="8"
                          class="wpshop-meta-field js-my-popup-change-observable js-my-popup-preset"
                          data-preview_param="pcode[]"></textarea>
            </div>
        </div>
    </div>
</template>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        return;
        (function ($) {
            var template = wp.template('my-popup-html-code');
            var $container = $('.js-html-code-container');

            function refreshIds() {
                var id = 0;
                $container.find('.wpshop-meta-row').each(function () {
                    var $this = $(this);
                    id++;
                    $this.find('.js-shortcode-example').text('[mypopup_html id=' + id + ']');
                    $this.find('textarea').attr('id', 'my_popup_prepared_code_' + id);
                });
            }

            $(document).on('click', '.js-add-html-code', function (e) {
                e.preventDefault();
                $container.append(template({id: $container.find('.wpshop-meta-row').length + 1}));
            })
            $(document).on('click', '.js-remove-html-code', function (e) {
                e.preventDefault();
                $(this).parents('.wpshop-meta-row').remove();
                refreshIds();
            });
        })(jQuery);
    });
</script>

<div class="mypopup-box">

    <div class="mypopup-box-header mypopup-box__header js-mypopup-box-header">
        <div class="mypopup-box-header__title">
            <?php echo __( 'Social networks', Plugin::TEXT_DOMAIN ) ?>
        </div>
        <?php display_help_link( 'social-networks' ) ?>
        <div class="mypopup-header__actions">
            <div class="mypopup-box-header-action mypopup-box-header-action--expand js-mypopup-box-header-action-expand" data-identity="socials"></div>
        </div>
    </div>

    <div class="mypopup-box__body js-mypopup-box-body"<?php echo ( $_COOKIE[ urlencode( 'my-popup-box-hide:socials' ) ] ?? 0 ) ? ' style="display:none"' : '' ?>>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <?php echo __( 'You can use this shortcode in content', Plugin::TEXT_DOMAIN ) ?>
                <input class="mypopup-inline-input" type="text" value="[mypopup_social_buttons]" onmouseover="this.select()">
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Type of buttons', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <select name="<?php echo $namespace ?>[my_popup_type_social_buttons][value]"
                                class="js-my-popup-change-observable js-my-popup-preset"
                                data-preview_param="social_type:value">
                            <?php
                            $options = [
                                'square' => __( 'Square', Plugin::TEXT_DOMAIN ),
                                'round'  => __( 'Round', Plugin::TEXT_DOMAIN ),
                            ];
                            ?>
                            <?php foreach ( $options as $val => $label ): ?>
                                <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_type_social_buttons', 'value' ) ?: '', $val ) ?>><?php echo $label ?></option>
                            <?php endforeach ?>
                        </select>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Align', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <select name="<?php echo $namespace ?>[my_popup_social_buttons_align][value]"
                                class="js-my-popup-change-observable js-my-popup-preset">
                            <?php
                            $default = $DEFAULTS['my_popup_social_buttons_align']['value'];
                            $options = [
                                'left' => __( 'Left', Plugin::TEXT_DOMAIN ),
                                'center'  => __( 'Center', Plugin::TEXT_DOMAIN ),
                                'right'  => __( 'Right', Plugin::TEXT_DOMAIN ),
                            ];
                            ?>
                            <?php foreach ( $options as $val => $label ): ?>
                                <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_social_buttons_align', 'value' ) ?: $default, $val ) ?>><?php echo $label ?></option>
                            <?php endforeach ?>
                        </select>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Button width', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_social_buttons_width']['value'] ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_social_buttons_width][value]"
                       value="<?php echo $__value( 'my_popup_social_buttons_width', 'value' ) ?: $default ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                       data-preview_param="social_width:value">
            </span>
                    px
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Button height', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_social_buttons_height']['value'] ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_social_buttons_height][value]"
                       value="<?php echo $__value( 'my_popup_social_buttons_height', 'value' ) ?: $default ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                       data-preview_param="social_height:value">
            </span>
                    px
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Indent between buttons', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <?php $default = $DEFAULTS['my_popup_social_buttons_indent']['value'] ?>
                <input type="number"
                       name="<?php echo $namespace ?>[my_popup_social_buttons_indent][value]"
                       value="<?php echo $__value( 'my_popup_social_buttons_indent', 'value' ) ?: $default ?>"
                       class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                       data-preview_param="social_indent:value">
            </span>
                    px
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Hide links using JS', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <label class="wpshop-meta-checkbox">
                        <input type="hidden" name="<?php echo $namespace ?>[my_popup_hide_social_links][is_enabled]" value="0">
                        <input type="checkbox" name="<?php $__name( 'my_popup_hide_social_links', 'is_enabled' ) ?>" value="1"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="hide_links:is_enabled"
                            <?php $__checked( 'my_popup_hide_social_links', 'is_enabled' ) ?>>
                        <span class="wpshop-meta-checkbox__label"></span>
                    </label>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Facebook', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_facebook][value]"
                       value="<?php echo $__value( 'my_popup_social_facebook', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="fb:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Vkontakte', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_vkontakte][value]"
                       value="<?php echo $__value( 'my_popup_social_vkontakte', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="vk:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Twitter', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_twitter][value]"
                       value="<?php echo $__value( 'my_popup_social_twitter', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="tw:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Odnoklassniki', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_odnoklassniki][value]"
                       value="<?php echo $__value( 'my_popup_social_odnoklassniki', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="ok:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Telegram', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_telegram][value]"
                       value="<?php echo $__value( 'my_popup_social_telegram', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="tg:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Youtube', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_youtube][value]"
                       value="<?php echo $__value( 'my_popup_social_youtube', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="ytb:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Instagram', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_instagram][value]"
                       value="<?php echo $__value( 'my_popup_social_instagram', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="in:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Linkedin', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_linkedin][value]"
                       value="<?php echo $__value( 'my_popup_social_linkedin', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="ln:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Whatsapp', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_whatsapp][value]"
                       value="<?php echo $__value( 'my_popup_social_whatsapp', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="wa:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Viber', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_viber][value]"
                       value="<?php echo $__value( 'my_popup_social_viber', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="vb:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Pinterest', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_pinterest][value]"
                       value="<?php echo $__value( 'my_popup_social_pinterest', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="pn:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Yandexzen', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_yandexzen][value]"
                       value="<?php echo $__value( 'my_popup_social_yandexzen', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="yz:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'GitHub', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_github][value]"
                       value="<?php echo $__value( 'my_popup_social_github', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="gh:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Discord', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_discord][value]"
                       value="<?php echo $__value( 'my_popup_social_discord', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="dc:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'RuTube', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_rutube][value]"
                       value="<?php echo $__value( 'my_popup_social_rutube', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="rt:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Yappy', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_yappy][value]"
                       value="<?php echo $__value( 'my_popup_social_yappy', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="yp:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Pikabu', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_pikabu][value]"
                       value="<?php echo $__value( 'my_popup_social_pikabu', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="pb:value">
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Yandex', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <input type="text"
                       name="<?php echo $namespace ?>[my_popup_social_yandex][value]"
                       value="<?php echo $__value( 'my_popup_social_yandex', 'value' ) ?>"
                       class="js-my-popup-change-observable js-my-popup-preset"
                       data-preview_param="yn:value">
            </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mypopup-box">
    <div class="mypopup-box-header mypopup-box__header js-mypopup-box-header">
        <div class="mypopup-box-header__title">
            <?php echo __( 'Social widgets', Plugin::TEXT_DOMAIN ) ?>
        </div>
        <?php display_help_link( 'social-widgets' ) ?>
        <div class="mypopup-header__actions">
            <div class="mypopup-box-header-action mypopup-box-header-action--expand js-mypopup-box-header-action-expand" data-identity="social-widgets"></div>
        </div>
    </div>

    <div class="mypopup-box__body js-mypopup-box-body"<?php echo ( $_COOKIE[ urlencode( 'my-popup-box-hide:social-widgets' ) ] ?? 0 ) ? ' style="display:none"' : '' ?>>
        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Facebook', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_display_widget_facebook][value]"
                               value="<?php echo $__value( 'my_popup_display_widget_facebook', 'value' ) ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="widget_fb:value">
                    </span>
                    <?php echo __( 'page address', Plugin::TEXT_DOMAIN ) ?>
                    <input class="mypopup-inline-input" type="text" value='[mypopup_social_widget type="fb"]' size="30" onmouseover="this.select()">
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Vkontakte', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_display_widget_vkontakte][value]"
                               value="<?php echo $__value( 'my_popup_display_widget_vkontakte', 'value' ) ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="widget_vk:value">
                    </span>
                    <?php echo __( 'group id', Plugin::TEXT_DOMAIN ) ?>
                    <input class="mypopup-inline-input" type="text" value='[mypopup_social_widget type="vk"]' size="30" onmouseover="this.select()">
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Odnoklassniki', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_display_widget_odnoklassniki][value]"
                               value="<?php echo $__value( 'my_popup_display_widget_odnoklassniki', 'value' ) ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="widget_ok:value">
                    </span>
                    <?php echo __( 'group id', Plugin::TEXT_DOMAIN ) ?>
                    <input class="mypopup-inline-input" type="text" value='[mypopup_social_widget type="ok"]' size="30" onmouseover="this.select()">
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Twitter', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_display_widget_twitter][value]"
                               value="<?php echo $__value( 'my_popup_display_widget_twitter', 'value' ) ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="widget_tw:value">
                    </span>
                    <?php echo __( 'page address', Plugin::TEXT_DOMAIN ) ?>
                    <input class="mypopup-inline-input" type="text" value='[mypopup_social_widget type="tw"]' size="30" onmouseover="this.select()">
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Pinterest', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_display_widget_pinterest][value]"
                               value="<?php echo $__value( 'my_popup_display_widget_pinterest', 'value' ) ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="widget_pn:value">
                    </span>
                    <?php echo __( 'board address', Plugin::TEXT_DOMAIN ) ?>
                    <input class="mypopup-inline-input" type="text" value='[mypopup_social_widget type="pn"]' size="30" onmouseover="this.select()">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mypopup-box">

    <div class="mypopup-box-header mypopup-box__header js-mypopup-box-header">
        <div class="mypopup-box-header__title">
            <?php echo __( 'Output posts', Plugin::TEXT_DOMAIN ) ?>
        </div>
        <?php display_help_link( 'output-posts' ) ?>
        <div class="mypopup-header__actions">
            <div class="mypopup-box-header-action mypopup-box-header-action--expand js-mypopup-box-header-action-expand" data-identity="output-posts"></div>
        </div>
    </div>

    <div class="mypopup-box__body js-mypopup-box-body"<?php echo ( $_COOKIE[ urlencode( 'my-popup-box-hide:output-posts' ) ] ?? 0 ) ? ' style="display:none"' : '' ?>>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <?php echo __( 'You can use this shortcode in content', Plugin::TEXT_DOMAIN ) ?>
                <input class="mypopup-inline-input" type="text" value="[mypopup_output_posts]" onmouseover="this.select()">
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Title', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <?php $default = $DEFAULTS['my_popup_output_posts_title']['value'] ?>
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_output_posts_title][value]"
                               value="<?php echo is_string( $__value( 'my_popup_output_posts_title', 'value' ) ) ? $__value( 'my_popup_output_posts_title', 'value' ) : $default ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="rp_title">
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Number of posts', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <?php $default = $DEFAULTS['my_popup_output_posts_count']['value'] ?>
                        <input type="number"
                               name="<?php echo $namespace ?>[my_popup_output_posts_count][value]"
                               value="<?php echo $__value( 'my_popup_output_posts_count', 'value' ) ?: $default ?>"
                               class="wpshop-meta-field--size-xs js-my-popup-change-observable js-my-popup-preset" size="4"
                               data-preview_param="rp_count:value">
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Sorting', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <select name="<?php echo $namespace ?>[my_popup_output_posts_order][value]"
                                class="js-my-popup-change-observable js-my-popup-preset"
                                data-preview_param="rp_order:value">
                            <?php
                            $options = [
                                'rand'     => __( 'Accidentally', Plugin::TEXT_DOMAIN ),
                                'views'    => __( 'By views (views)', Plugin::TEXT_DOMAIN ),
                                'comments' => __( 'By comments', Plugin::TEXT_DOMAIN ),
                                'new'      => __( 'New', Plugin::TEXT_DOMAIN ),
                            ];
                            ?>
                            <?php foreach ( $options as $val => $label ): ?>
                                <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_output_posts_order', 'value' ) ?: '', $val ) ?>><?php echo $label ?></option>
                            <?php endforeach ?>
                        </select>
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'ID of posts to include', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <?php $default = $DEFAULTS['my_popup_output_posts_include']['value'] ?>
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_output_posts_include][value]"
                               value="<?php echo is_string( $__value( 'my_popup_output_posts_include', 'value' ) ) ? $__value( 'my_popup_output_posts_include', 'value' ) : $default ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="rp_posts_include:value">
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'ID of posts to exclude', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <?php $default = $DEFAULTS['my_popup_output_posts_exclude']['value'] ?>
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_output_posts_exclude][value]"
                               value="<?php echo is_string( $__value( 'my_popup_output_posts_exclude', 'value' ) ) ? $__value( 'my_popup_output_posts_exclude', 'value' ) : $default ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="rp_posts_exclude:value">
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'ID of categories to include', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <?php $default = $DEFAULTS['my_popup_output_categories_include']['value'] ?>
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_output_categories_include][value]"
                               value="<?php echo is_string( $__value( 'my_popup_output_categories_include', 'value' ) ) ? $__value( 'my_popup_output_categories_include', 'value' ) : $default ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="rp_categories_include:value">
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'ID of categories to exclude', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <?php $default = $DEFAULTS['my_popup_output_categories_exclude']['value'] ?>
                        <input type="text"
                               name="<?php echo $namespace ?>[my_popup_output_categories_exclude][value]"
                               value="<?php echo is_string( $__value( 'my_popup_output_categories_exclude', 'value' ) ) ? $__value( 'my_popup_output_categories_exclude', 'value' ) : $default ?>"
                               class="js-my-popup-change-observable js-my-popup-preset"
                               data-preview_param="rp_categories_exclude:value">
                    </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Get posts', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <label class="wpshop-meta-checkbox">
                    <input type="hidden" name="<?php echo $namespace ?>[my_popup_output_posts_add_sorting][is_enabled]" value="0">
                    <input type="checkbox" name="<?php $__name( 'my_popup_output_posts_add_sorting', 'is_enabled' ) ?>" value="1"
                           class="js-my-popup-change-observable js-my-popup-preset"
                           data-preview_param="rp_sorting:is_enabled"
                        <?php $__checked( 'my_popup_output_posts_add_sorting', 'is_enabled', $DEFAULTS['my_popup_output_posts_add_sorting']['is_enabled'] ) ?>>
                    <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'If there weren’t enough records, get them according to the sorting', Plugin::TEXT_DOMAIN ) ?>
                </label>
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Open links in a new tab', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <label class="wpshop-meta-checkbox">
                    <input type="hidden" name="<?php echo $namespace ?>[my_popup_output_posts_open_new_tab][is_enabled]" value="0">
                    <input type="checkbox" name="<?php $__name( 'my_popup_output_posts_open_new_tab', 'is_enabled' ) ?>" value="1"
                           class="js-my-popup-change-observable js-my-popup-preset"
                           data-preview_param="rp_tab:is_enabled"
                        <?php $__checked( 'my_popup_output_posts_open_new_tab', 'is_enabled' ) ?>>
                    <span class="wpshop-meta-checkbox__label"></span>
                </label>
            </span>
                </div>
            </div>
        </div>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Show thumbnail', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <label class="wpshop-meta-checkbox">
                    <input type="hidden" name="<?php echo $namespace ?>[my_popup_output_post_show_thumb][is_enabled]" value="0">
                    <input type="checkbox" name="<?php $__name( 'my_popup_output_post_show_thumb', 'is_enabled' ) ?>" value="1"
                           class="js-my-popup-change-observable js-my-popup-preset"
                           data-preview_param="rp_thumb:is_enabled"
                        <?php $__checked( 'my_popup_output_post_show_thumb', 'is_enabled', $DEFAULTS['my_popup_output_post_show_thumb']['is_enabled'] ) ?>>
                    <span class="wpshop-meta-checkbox__label"></span>
                </label>
            </span>
                </div>
            </div>
        </div>

        <br>

        <div class="wpshop-meta-row">
            <div class="wpshop-meta-field">
                <div class="wpshop-meta-field__label">
                    <label><?php echo __( 'Style', Plugin::TEXT_DOMAIN ) ?></label>
                </div>
                <div class="wpshop-meta-field__body">
                    <span class="wpshop-meta-field-inline">
                        <select name="<?php echo $namespace ?>[my_popup_output_post_style][value]"
                                class="js-my-popup-change-observable js-my-popup-preset"
                                data-preview_param="rp_style:value">
                            <?php
                            $options = [
                                'horizontal' => __( 'Horizontal (In a row)', Plugin::TEXT_DOMAIN ),
                                'vertical'   => __( 'Vertical (Per Column)', Plugin::TEXT_DOMAIN ),
                            ];
                            ?>
                            <?php foreach ( $options as $val => $label ): ?>
                                <option value="<?php echo $val ?>"<?php selected( $__value( 'my_popup_output_post_style', 'value' ) ?: '', $val ) ?>><?php echo $label ?></option>
                            <?php endforeach ?>
                        </select>
                    </span>
                </div>
            </div>
        </div>

    </div><!--.mypopup-box__body js-mypopup-box-body-->
</div><!--.mypopup-box-->
