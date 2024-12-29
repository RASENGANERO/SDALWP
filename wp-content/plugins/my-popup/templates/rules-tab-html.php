<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\PluginMyPopup\MyPopup;
use Wpshop\PluginMyPopup\Plugin;
use Wpshop\PluginMyPopup\Rule\RuleAutocomplete;
use Wpshop\PluginMyPopup\Utilities;
use function Wpshop\PluginMyPopup\container;
use function Wpshop\PluginMyPopup\display_help_link;
use function Wpshop\PluginMyPopup\get_post_meta_with_null;

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

$options_type_options = apply_filters( 'my_popup:rule_options', [
    'all'              => __( 'on every page', Plugin::TEXT_DOMAIN ),
    'home'             => __( 'on home', Plugin::TEXT_DOMAIN ),
    'posts'            => __( 'in posts', Plugin::TEXT_DOMAIN ),
    'posts_cat'        => '&#11025;&nbsp;' . __( 'posts in  categories', Plugin::TEXT_DOMAIN ),
    'posts_tag'        => '&#11025;&nbsp;' . __( 'posts in  tags', Plugin::TEXT_DOMAIN ),
    'posts_taxonomies' => '&#11025;&nbsp;' . __( 'posts in taxonomies', Plugin::TEXT_DOMAIN ) . ' *',
    'pages'            => __( 'on pages', Plugin::TEXT_DOMAIN ),
    'post_types'       => __( 'on post types', Plugin::TEXT_DOMAIN ) . ' *',
    'categories'       => __( 'in categories', Plugin::TEXT_DOMAIN ),
    'tags'             => __( 'in tags', Plugin::TEXT_DOMAIN ),
    'taxonomies'       => __( 'in taxonomies', Plugin::TEXT_DOMAIN ) . ' *',
    'search'           => __( 'on page search', Plugin::TEXT_DOMAIN ),
    '404'              => __( 'on 404', Plugin::TEXT_DOMAIN ),
    'url_match'        => __( 'url regular expression', Plugin::TEXT_DOMAIN ),
] );

?>


<div class="wpshop-meta-row">
    <div class="wpshop-meta-field">
        <div class="wpshop-meta-field__body">
            <span class="wpshop-meta-field-inline">
                <label class="wpshop-meta-checkbox">
                    <input type="hidden" name="<?php echo $namespace ?>[my_popup_enable]" value="0">
                    <input type="checkbox" name="<?php $__name( 'my_popup_enable' ) ?>" value="1"
                        <?php $__checked( 'my_popup_enable', null, $DEFAULTS['my_popup_enable'] ) ?>>
                    <span class="wpshop-meta-checkbox__label"><?php echo __( 'Enable Popup', Plugin::TEXT_DOMAIN ) ?></span>
                </label>
            </span>
        </div>
    </div>
</div>

<div class="wpshop-meta-header">
    <?php echo __( 'Events at which to show popup', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'events' ) ?>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_show_on_time][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_show_on_time', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_show_on_time', 'is_enabled', $DEFAULTS['my_popup_show_on_time']['is_enabled'] ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Show through', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <span class="wpshop-meta-field-inline">
        <?php $default = $DEFAULTS['my_popup_show_on_time']['value'] ?>
        <input type="number"
               name="<?php echo $namespace ?>[my_popup_show_on_time][value]"
               value="<?php echo $__value( 'my_popup_show_on_time', 'value' ) ?: $default ?>"
               class="wpshop-meta-field--size-xs" size="4">
    </span>
    <?php echo __( 'sec. after loading page', Plugin::TEXT_DOMAIN ) ?>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_show_on_scroll][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_show_on_scroll', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_show_on_scroll', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Show after scrolling', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <span class="wpshop-meta-field-inline">
        <input type="number"
               name="<?php echo $namespace ?>[my_popup_show_on_scroll][value]"
               value="<?php echo $__value( 'my_popup_show_on_scroll', 'value' ) ?>"
               class="wpshop-meta-field--size-xs" size="4">
    </span>
    <?php echo __( '% page', Plugin::TEXT_DOMAIN ) ?>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_show_on_element][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_show_on_element', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_show_on_element', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Show scroll to element', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <span class="wpshop-meta-field-inline">
        <input type="text"
               name="<?php echo $namespace ?>[my_popup_show_on_element][value]"
               value="<?php echo $__value( 'my_popup_show_on_element', 'value' ) ?>">
    </span>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_show_inactive_time][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_show_inactive_time', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_show_inactive_time', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Show if user is not active', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <span class="wpshop-meta-field-inline">
        <input type="number"
               name="<?php echo $namespace ?>[my_popup_show_inactive_time][value]"
               value="<?php echo $__value( 'my_popup_show_inactive_time', 'value' ) ?>"
               class="wpshop-meta-field--size-xs" size="4">
    </span>
    <?php echo __( 'sec.', Plugin::TEXT_DOMAIN ) ?>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_show_leaves_page][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_show_leaves_page', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_show_leaves_page', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Show if user leaves page', Plugin::TEXT_DOMAIN ) ?>
    </label>
</div>

<div class="wpshop-meta-row">
    <label class="wpshop-meta-checkbox">
        <input type="hidden" name="<?php echo $namespace ?>[my_popup_show_on_click][is_enabled]" value="0">
        <input type="checkbox" name="<?php $__name( 'my_popup_show_on_click', 'is_enabled' ) ?>" value="1"
            <?php $__checked( 'my_popup_show_on_click', 'is_enabled' ) ?>>
        <span class="wpshop-meta-checkbox__label"></span> <?php echo __( 'Show by clicking on an element', Plugin::TEXT_DOMAIN ) ?>
    </label>

    <span class="wpshop-meta-field-inline">
        <input type="text"
               name="<?php echo $namespace ?>[my_popup_show_on_click][selector]"
               value="<?php echo $__value( 'my_popup_show_on_click', 'selector' ) ?>"
               title="<?php echo __( 'selector', MY_POPUP_TEXTDOMAIN ) ?>">
    </span>

    <a href="https://support.wpshop.ru/faq/my-popup-open-on-click/" target="_blank"><?php _e( 'Help', MY_POPUP_TEXTDOMAIN ) ?></a>
</div>
<div class="wpshop-meta-row">
    <?php
    $popup_id = container()->get( MyPopup::class )->get_popup_id( get_the_ID() );
    ?>
    <div>
        <p><?php echo __( 'You can call the popup using the myPopupShow function with passing the popup ID. For example:', MY_POPUP_TEXTDOMAIN ) ?></p>
        <pre>
<?php echo esc_html( <<<"HTML"
<button class="button" onclick="myPopupShow('{$popup_id}')">Button</button>
HTML

) ?>
        </pre>
    </div>
</div>

<div class="wpshop-meta-header">
    <?php echo __( 'On witch pages to display popup', Plugin::TEXT_DOMAIN ) ?>
    <?php display_help_link( 'rules' ) ?>
</div>

<p>
    <?php echo __( 'By default a popup will not be displayed on all pages but you can set your conditions below for output', Plugin::TEXT_DOMAIN ) ?>
    <br>
    <?php echo __( 'Higher rules more important', Plugin::TEXT_DOMAIN ) ?>
</p>


<?php
$rules_json = get_post_meta_with_null( $post->ID, 'rules', true );
if ( null === $rules_json ) {
    $rules_json = '[{"show":"show","type":"all","subtype":"","value":""}]';
} else {
    $rules_json = $rules_json['value'];
}

$rules = [];
if ( $rules_json && ! is_array( $rules_json ) ) {
    $rules = json_decode( $rules_json, true );
}
$rule_values   = container()->get( RuleAutocomplete::class )->gather_rules_values( (array) $rules );
$rule_subtypes = container()->get( RuleAutocomplete::class )->gather_rule_subtypes( (array) $rules );

?>

<div class="wpshop-meta-row">
    <div class="mypopup-rules-add-button">
        <span class="button js-mypopup-rule-add"><?php echo __( 'Add rule', Plugin::TEXT_DOMAIN ) ?></span>
    </div>

    <div class="mypopup-rules js-mypopup-rules">
        <?php

        //        $allow_rules_popup = false;

        foreach ( $rules as $rule ): ?>

            <div class="mypopup-rule js-mypopup-rule">
                <div class="mypopup-rule__capture"></div>
                <div class="mypopup-rule__visible js-mypopup-rule-visible">
                    <select>
                        <?php
                        $options = [
                            'show' => __( 'Show', Plugin::TEXT_DOMAIN ),
                            'hide' => __( 'Hide', Plugin::TEXT_DOMAIN ),
                        ];
                        ?>
                        <?php foreach ( $options as $val => $label ): ?>
                            <option value="<?php echo $val ?>"<?php selected( $rule['show'], $val ) ?>><?php echo $label ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="mypopup-rule__type js-mypopup-rule-type">
                    <select>
                        <?php foreach ( $options_type_options as $val => $label ): ?>
                            <option value="<?php echo $val ?>"<?php selected( $rule['type'], $val ) ?>><?php echo $label ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="mypopup-rule__subtype js-mypopup-rule-subtype">
                    <div class="mypopup-ac-container js-mypopup-ac-container">
                        <span class="mypopup-ac-pills js-mypopup-ac-pills">
                            <?php Utilities::render_rule_subtype_pills( $rule, $rule_subtypes ) ?>
                        </span>
                        <input class="mypopup-ac-input js-mypopup-ac-input" type="text" value="">
                        <input class="mypopup-ac-result js-mypopup-ac-result" type="hidden" value="<?php echo esc_attr( $rule['value'] ) ?>">
                    </div>
                </div>

                <div class="mypopup-rule__value js-mypopup-rule-value">
                    <div class="mypopup-ac-container js-mypopup-ac-container">
                        <span class="mypopup-ac-pills js-mypopup-ac-pills">
                            <?php Utilities::render_rule_pills( $rule, $rule_values ) ?>
                        </span>
                        <input class="mypopup-ac-input js-mypopup-ac-input" type="text" value="">
                        <input class="mypopup-ac-result js-mypopup-ac-result" type="hidden" value="<?php echo esc_attr( $rule['value'] ) ?>">
                    </div>
                </div>

                <div class="mypopup-rule__remove">
                    <span class="mypopup-rule-remove js-mypopup-rule-remove">&times;</span>
                </div>
            </div>

        <?php endforeach ?>
    </div>
    <div class="mypopup-rule-dump js-mypopup-rule-dump">

        <div class="mypopup-rule js-mypopup-rule">
            <div class="mypopup-rule__capture"></div>
            <div class="mypopup-rule__visible js-mypopup-rule-visible">
                <select>
                    <?php
                    $options = [
                        'show' => __( 'Show', Plugin::TEXT_DOMAIN ),
                        'hide' => __( 'Hide', Plugin::TEXT_DOMAIN ),
                    ];
                    ?>
                    <?php foreach ( $options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="mypopup-rule__type js-mypopup-rule-type">
                <select>
                    <?php foreach ( $options_type_options as $val => $label ): ?>
                        <option value="<?php echo $val ?>"><?php echo $label ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="mypopup-rule__subtype js-mypopup-rule-subtype">
                <div class="mypopup-rule__subtype js-mypopup-rule-subtype">
                    <div class="mypopup-ac-container js-mypopup-ac-container" data-type="subtype">
                        <span class="mypopup-ac-pills js-mypopup-ac-pills"></span>
                        <input class="mypopup-ac-input js-mypopup-ac-input" type="text" value="" placeholder="<?php esc_html_e( 'subtype', Plugin::TEXT_DOMAIN ); ?>">
                        <input class="mypopup-ac-result js-mypopup-ac-result" type="hidden" value="">
                    </div>
                </div>
            </div>

            <div class="mypopup-rule__value js-mypopup-rule-value">
                <div class="mypopup-rule__value js-mypopup-rule-value">
                    <div class="mypopup-ac-container js-mypopup-ac-container" data-type="value">
                        <span class="mypopup-ac-pills js-mypopup-ac-pills"></span>
                        <input class="mypopup-ac-input js-mypopup-ac-input" type="text" value="">
                        <input class="mypopup-ac-result js-mypopup-ac-result" type="hidden" value="">
                    </div>
                </div>
            </div>

            <div class="mypopup-rule__remove">
                <span class="mypopup-rule-remove js-mypopup-rule-remove">&times;</span>
            </div>
        </div>

    </div>

    <input type="hidden"
           name="<?php echo $namespace ?>[rules][value]"
           id="rules_pages"
           value="<?php echo esc_attr( $rules_json ) ?>">
</div>
