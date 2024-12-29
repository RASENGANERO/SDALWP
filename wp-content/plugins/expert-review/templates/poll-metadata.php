<?php

use Wpshop\ExpertReview\Plugin;
use Wpshop\ExpertReview\Shortcodes\Poll;
use function Wpshop\ExpertReview\container;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var \WP_Post $post
 */

$answers = container()->get( Poll::class )->get_answers_with_percent( $post->ID );
?>

<h3><?php echo _x( 'Poll Result', 'tpl', Plugin::TEXT_DOMAIN ) ?> "<?php echo esc_html( $post->post_title ) ?>"</h3>
<ul>
    <?php foreach ( $answers as $item ): ?>
        <li>
            #<?php echo $item['id'] ?>
            [ <?php echo $item['votes'] ?> / <?php echo $item['percent'] ?>% ]
            <?php echo esc_html( $item['text'] ) ?>
        </li>
    <?php endforeach ?>
</ul>
<p>
    <?php echo _x( 'Total Voted', 'tpl', Plugin::TEXT_DOMAIN ) ?>:
    <?php echo (int) get_post_meta( $post->ID, Poll::META_POLL_TOTAL_VOTES, true ) ?>
</p>
<a href="#" class="js-edit-votes"><?php echo __( 'Edit' ) ?></a>
<div class="js-edit-votes-table" style="display: none">
    <table class="expert-review-poll-table" role="presentation" cellspacing="0">
        <tbody>
        <thead>
        <tr>
            <th><?php echo __( 'Answer', Plugin::TEXT_DOMAIN ) ?></th>
            <td><?php echo __( 'Count of Votes', Plugin::TEXT_DOMAIN ) ?></td>
        </tr>
        </thead>
        <?php foreach ( $answers as $item ): ?>
            <tr>
                <th scope="row">
                    <label for="<?php echo $id = uniqid( $item['id'] . '_' ) ?>"><?php echo esc_html( $item['text'] ) ?></label>
                </th>
                <td class="js-vote-data td-number-input">
                    <input type="hidden" value="<?php echo $item['id'] ?>" name="id">
                    <input type="hidden" value="<?php echo $item['text'] ?>" name="text">
                    <input type="number" class="" min="0" step="1" id="<?php echo $id ?>" name="votes" value="<?php echo $item['votes'] ?>" placeholder="" autocomplete="new-password">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <button class="button js-edit-votes-btn"><?php echo __( 'Save' ) ?></button>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        'use strict';
        $('.js-edit-votes').on('click', function (e) {
            e.preventDefault();
            $(this).hide();
            $('.js-edit-votes-table').show();
        });
        $('.js-edit-votes-btn').on('click', function (e) {
            e.preventDefault();
            var $this = $(this);
            $.ajax({
                url: expert_review_globals.url,
                type: 'post',
                data: {
                    action: 'expert_review_save_poll',
                    nonce: expert_review_globals.nonce,
                    params: {
                        answers: collect_answers()
                    },
                    current: $('#post_ID').val(),
                    confirm_update: 1,
                    update_answers_only: 1,
                    force_update_answers: 1
                },
                beforeSend: function () {
                    $this.attr('disable', true);
                }
            }).done(function (response) {
                if (!response.success) {
                    console.log(response);
                    alert('<?php echo __( 'Oops, some error occurred while updated the poll', Plugin::TEXT_DOMAIN ) ?>');
                    return;
                }
                window.location.reload();
            }).always(function () {
                $this.attr('disable', false);
            });
        });

        function collect_answers() {
            var result = [];
            $('.js-vote-data').each(function () {
                var $this = $(this);
                result.push({
                    id: $this.find('input[name="id"]').val(),
                    text: $this.find('input[name="text"]').val(),
                    votes: $this.find('input[name="votes"]').val()
                })
            });
            return result;
        }
    });
</script>
