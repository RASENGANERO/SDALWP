<?php

/**
 * @version 1.8.1
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Settings\AdvancedOptions;
use Wpshop\ExpertReview\Utilities;
use function Wpshop\ExpertReview\container;

$advanced_options = container()->get( AdvancedOptions::class );

/**
 * @var array  $atts
 * @var string $content
 */
$nl2br = $atts['_nl2br_fn'];

$expert_name        = '';
$expert_description = '';
$expert_avatar      = '';
$expert_link        = apply_filters( 'expert_review:expert_link', $atts['expert_link'], $atts );


if ( $atts['expert_name'] ) {
    $expert_name = $atts['expert_name'];
    if ( $expert_link ) {
        $expert_name = '<a href="' . $expert_link . '" target="_blank"' . ( 'schema' == $advanced_options->expert_microdata_type ? ' itemprop="url"' : '' ) . '>' . $expert_name . '</a>';
    }
}
if ( $atts['expert_description'] ) {
    $expert_description = $nl2br( $atts['expert_description'] );
}
if ( $atts['expert_avatar'] ) {
    $avatar_alt = ! empty( $atts['expert_avatar_alt'] ) ? $atts['expert_avatar_alt'] : $atts['expert_name'];

    $expert_avatar_atts = apply_filters( 'expert_review:avatar_attributes', [
        'src' => $atts['expert_avatar'],
        'alt' => esc_attr( $avatar_alt ),
    ] );

    if ( 'schema' == $advanced_options->expert_microdata_type ) {
        $expert_avatar_atts['itemprop'] = 'image';
    }

    $expert_avatar_atts_str = '';
    foreach ( $expert_avatar_atts as $key => $value ) {
        $expert_avatar_atts_str .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
    }
    $expert_avatar_atts_str = trim( $expert_avatar_atts_str );

    $expert_avatar = '<img ' . $expert_avatar_atts_str . '>';
    if ( $expert_link ) {
        $expert_avatar = '<a href="' . $expert_link . '" target="_blank">' . $expert_avatar . '</a>';
    }
}

$button_settings = null;
if ( $atts['expert_show_button'] ) {
    $settings         = [
        'type'       => $atts['expert_show_button_type'],
        'expertType' => $atts['expert_type'],
        'expertId'   => $atts['expert_id'],
        'use_phone'  => $atts['popup_use_phone'],
        'link'       => $atts['question_external_link'],
    ];
    $settings['sign'] = Utilities::sign_data( $settings, wp_create_nonce( 'button_settings' ) );
    $button_settings  = esc_attr( json_encode( $settings ) );
}

$expert_name        = apply_filters( 'expert_review:expert_name', $expert_name, $atts );
$expert_avatar      = apply_filters( 'expert_review:expert_avatar', $expert_avatar, $atts );
$expert_description = apply_filters( 'expert_review:expert_description', $expert_description, $atts );

$classes = [];
if ( ! empty( $atts['color'] ) ) {
    $classes[] = 'expert-review--color-' . $atts['color'];
}
$classes = ' ' . implode( ' ', $classes );

do_action( 'expert_review_before', $atts );
?>
<div class="expert-review<?php echo $classes ?>">
    <?php
    echo er_render_template(
        'expert-blocks/expert.php',
        compact( 'atts', 'nl2br', 'expert_name', 'expert_avatar', 'expert_description', 'button_settings' )
    );
    echo er_render_template(
        'expert-blocks/qa.php',
        compact( 'atts', 'nl2br', 'expert_avatar' )
    );
    echo er_render_template(
        'expert-blocks/rating.php',
        compact( 'atts', 'nl2br' )
    );
    echo er_render_template(
        'expert-blocks/plus-minus.php',
        compact( 'atts', 'nl2br' )
    );
    ?>
</div><!--.expert-review-->
<?php do_action( 'expert_review_after', $atts ); ?>
