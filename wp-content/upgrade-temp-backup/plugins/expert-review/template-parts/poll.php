<?php

/**
 * @version 1.6.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\ExpertReview\Plugin;
use Wpshop\ExpertReview\Shortcodes\Poll;

/**
 * @var array  $atts
 * @var array  $params
 * @var array  $answers
 * @var string $content
 */
$total = (int) get_post_meta( $atts['id'], Poll::META_POLL_TOTAL_VOTES, true );

$result_show_txt = esc_html( apply_filters( 'expert_review_poll:show_result_text', __( 'Show Results', Plugin::TEXT_DOMAIN ) ) );
$result_hide_txt = esc_attr( apply_filters( 'expert_review_poll:hide_result_text', __( 'Hide Results', Plugin::TEXT_DOMAIN ) ) );

$data      = [
    'id'       => esc_attr( $atts['id'] ),
    'can_vote' => (int) get_post_meta( $atts['id'], Poll::META_POLL_CAN_VOTE, true ),
    'r'        => (int) get_post_meta( $atts['id'], Poll::META_POLL_RESET, true ),
];
$data_attr = [];
foreach ( $data as $k => $v ) {
    $data_attr[] = "data-{$k}=\"{$v}\"";
}
$data_attr = implode( ' ', $data_attr );

do_action( 'expert_review:poll_before', $atts, $params );
?>
    <div class="expert-review-poll js-expert-review-poll expert-review-poll--style-<?php echo esc_attr( $params['style'] ) ?> expert-review-poll--color-<?php echo esc_attr( $params['color'] ) ?>" <?php echo $data_attr ?>>
        <div class="expert-review-poll__header"><?php echo $params['title'] ?></div>
        <?php foreach ( $answers as $item ): ?>
            <div class="expert-review-poll-item js-expert-review-poll-item" data-id="<?php echo $item['id'] ?>">
                <div class="expert-review-poll-item__answer js-expert-review-poll-item-answer"><?php echo $item['text'] ?></div>
                <div class="expert-review-poll-item__num js-expert-review-poll-item-num"><?php echo $item['percent'] ?>%
                </div>
                <div class="expert-review-poll-item__progress js-expert-review-poll-item-progress" style="width: <?php echo( $item['percent'] ? $item['percent'] . '%' : 0 ) ?>"></div>
            </div>
        <?php endforeach ?>
        <?php if ( ! empty( $params['show_results_button'] ) ): ?>
            <button class="button expert-review-poll__result-button js-expert-review-poll-result-button" style="display:none" data-toggle_txt="<?php echo $result_hide_txt ?>">
                <?php echo $result_show_txt ?>
            </button>
        <?php endif ?>
        <?php if ( $params['show_count'] ): ?>
            <div class="expert-review-poll__count">
                <?php echo __( 'Voted', Plugin::TEXT_DOMAIN ) ?>:
                <span class="js-expert-review-poll-count"><?php echo $total ?></span>
            </div>
        <?php endif ?>
    </div><!--.expert-review-poll-->

<?php do_action( 'expert_review:poll_before', $atts, $params );
