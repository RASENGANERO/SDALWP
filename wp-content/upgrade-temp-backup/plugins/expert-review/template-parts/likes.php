<?php

/**
 * @version 1.7.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Shortcodes;

/**
 * @var array      $atts
 * @var array      $icons
 * @var int        $post_id
 * @var int        $likes
 * @var int        $dislikes
 * @var string     $content
 *
 * @var Shortcodes $instance
 */

$type = $atts['hide_dislikes'] ? 'toggle' : 'like';

$classes = [];
if ( ! empty( $atts['style'] ) ) {
    $classes[] = 'expert-review-likes--style-' . $atts['style'];
}
if ( ! empty( $atts['size'] ) ) {
    $classes[] = 'expert-review-likes--size-' . $atts['size'];
}
if ( $atts['alignment'] ) {
    $classes[] = 'expert-review-likes--alignment-' . $atts['alignment'];
}
$classes = ' ' . implode( ' ', $classes );

$data_attr = [];
if ( $atts['entity_type'] === Shortcodes::LIKE_ENTITY_POSTS ) {
    if ( $atts['name'] ) {
        $identity          = apply_filters( 'expert_review:named_likes_identity', $atts['name'], $atts );
        $data_attr['name'] = esc_attr( md5( $identity ) );
    } else {
        $data_attr['post_id'] = $post_id;
    }
} elseif ( $atts['entity_type'] === Shortcodes::LIKE_ENTITY_COMMENTS ) {
    $data_attr['entity_type'] = Shortcodes::LIKE_ENTITY_COMMENTS;
    $data_attr['comment_id']  = $atts['comment_id'];
}

$data_attr = array_map( function ( $item, $key ) {
    return "data-{$key}=\"$item\"";
}, $data_attr, array_keys( $data_attr ) );

$data_attr = implode( ' ', $data_attr );

do_action( 'expert_review_likes_before', $atts );
?>
    <div class="expert-review-likes<?php echo $classes ?> js-expert-review-likes-button-container" <?php echo $data_attr ?>>
        <button class="expert-review-likes__button expert-review-likes__button--like js-expert-review-likes-button" data-type="<?php echo $type ?>">
            <?php if ( $atts['show_icon'] ): ?>
                <span class="expert-review-likes__icon">
                <?php echo $icons[ $atts['icons'] ]['like'] ?>
            </span>
            <?php endif ?>
            <?php if ( $atts['show_label'] ): ?>
                <span class="expert-review-likes__label">
                <?php echo $atts['label_like'] ?>
            </span>
            <?php endif ?>
            <?php if ( $atts['show_count'] ) : ?>
                <span class="expert-review-likes__count js-expert-review-likes-count" data-count="<?php echo $likes ?>">
                <?php
                if ( $likes > 0 ) {
                    echo $instance->rounded_number( $likes );
                } elseif ( $likes < 0 ) {
                    echo '-' . $instance->rounded_number( abs( $likes ) );
                }
                ?>
            </span>
            <?php endif ?>
            <?php if ( ! $atts['hide_dislikes'] ): ?>
                <button class="expert-review-likes__button expert-review-likes__button--dislike js-expert-review-likes-button" data-type="dislike">
                    <?php if ( $atts['show_icon'] ): ?>
                        <span class="expert-review-likes__icon">
                        <?php echo $icons[ $atts['icons'] ]['dislike'] ?>
                    </span>
                    <?php endif ?>
                    <?php if ( $atts['show_label'] ): ?>
                        <span class="expert-review-likes__label">
                        <?php echo $atts['label_dislike'] ?>
                    </span>
                    <?php endif ?>
                    <?php if ( $atts['show_count'] ): ?>
                        <span class="expert-review-likes__count js-expert-review-dislikes-count" data-count="<?php echo $dislikes ?>">
                        <?php if ( $dislikes > 0 ) {
                            echo $instance->rounded_number( $dislikes );
                        } elseif ( $dislikes < 0 ) {
                            echo '-' . $this->rounded_number( abs( $dislikes ) );
                        } ?>
                    </span>
                    <?php endif ?>
                </button>
            <?php endif ?>
        </button>
    </div>
<?php
do_action( 'expert_review_likes_after', $atts );
