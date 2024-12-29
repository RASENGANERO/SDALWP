<?php

defined( 'WPINC' ) || die;

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Analytics;
use function Wpshop\Quizle\container;
use function Wpshop\Quizle\get_quizle_analytic_url;

/**
 * @version 1.3.0
 */

/**
 * @var array{'post':WP_Post|false} $args
 */

$analytics = container()->get( Analytics::class );
$settings  = container()->get( Settings::class );
?>

<?php
if ( ! $args['post'] ): ?>
    <p><?php echo __( 'Quizle Not Found', QUIZLE_TEXTDOMAIN ) ?></p>
    <?php
    return;
endif;


$date_end = isset( $_GET['date_end'] ) ? DateTimeImmutable::createFromFormat( 'Y-m-d', $_GET['date_end'] ) : current_datetime();
$date_end = $date_end->setTime( 23, 59, 59 );
if ( isset( $_GET['date_start'] ) ) {
    $date_start = DateTimeImmutable::createFromFormat( 'Y-m-d', $_GET['date_start'] );
} else {
//    $date_start = $date_end->format( 'j' ) > 5
//        ? $date_end->modify( 'first day of this month' )->modify( '+1 day' )
//        : $date_end->modify( 'first day of previous month' )->modify( '+1 day' );
    $date_start = $date_end->modify( '-30 days' );
}
$date_start = $date_start->setTime( 0, 0, 0 );

$stat = $analytics->get_created_of_current_month( $args['post']->ID, $date_start, $date_end );

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php
        echo __( 'Analytics', QUIZLE_TEXTDOMAIN );
        echo ' «' . get_the_title( $args['post'] ) . '»';
        ?>
    </h1>

    <a href="<?php echo get_edit_post_link( $args['post']->ID ) ?>" class="page-title-action"><?php echo __( 'Edit quiz', QUIZLE_TEXTDOMAIN ) ?></a>
    <?php if ( $settings->get_value( 'is_quizle_public' ) ): ?>
        <a href="<?php echo get_permalink( $args['post'] ) ?>" class="page-title-action"><?php echo __( 'View', QUIZLE_TEXTDOMAIN ) ?></a>
    <?php endif ?>

    <div class="quizle-analytics">

        <form action="" class="quizle-box quizle-analytics-filter">
            <input type="hidden" name="post_type" value="quizle">
            <input type="hidden" name="page" value="analytics">
            <input type="hidden" name="id" value="<?php echo $args['post']->ID ?>">
            <input type="date" name="date_start" required
                   max="<?php echo current_datetime()->format( 'Y-m-d' ) ?>"
                   value="<?php echo $date_start->format( 'Y-m-d' ) ?>">
            <input type="date" name="date_end" required
                   value="<?php echo $date_end->format( 'Y-m-d' ) ?>">
            <div>
                <button type="submit" class="button"><?php echo __( 'Apply', QUIZLE_TEXTDOMAIN ) ?></button>
                <a href="<?php echo get_quizle_analytic_url( $args['post']->ID ) ?>" class="button"><?php echo __( 'Reset', QUIZLE_TEXTDOMAIN ) ?></a>
            </div>
        </form>

        <?php if ( ! empty( $_GET['date_start'] ) || ! empty( $_GET['date_end'] ) ): ?>
            <div class="quizle-analytics__header"><?php echo sprintf(
                    __( 'Statistics for the period: %s — %s', QUIZLE_TEXTDOMAIN ),
                    date_i18n( get_option( 'date_format' ), $date_start->getTimestamp() ),
                    date_i18n( get_option( 'date_format' ), $date_end->getTimestamp() )
                ) ?></div>
        <?php else: ?>
            <div class="quizle-analytics__header"><?php echo __( 'Statistics for the last 30 days', QUIZLE_TEXTDOMAIN ) ?></div>
        <?php endif ?>

        <div class="quizle-analytics-charts">
            <div class="quizle-box quizle-analytics-chart">
                <div class="quizle-analytics-chart__header">
                    <small><?php echo __( 'Opened', QUIZLE_TEXTDOMAIN ) ?>
                        → <?php echo __( 'Finished', QUIZLE_TEXTDOMAIN ) ?></small>
                    <strong><?php echo $stat->total_created ?> → <?php echo $stat->total_finished ?></strong>
                </div>
                <div id="graph-1" class="quizle-analytics__chart-graph"></div>
            </div>
            <div class="quizle-box quizle-analytics-chart">
                <div class="quizle-analytics-chart__header">
                    <small><?php echo __( 'Conversion', QUIZLE_TEXTDOMAIN ) ?></small>
                    <strong><?php echo $stat->total_conversion ?>%</strong>
                </div>
                <div id="graph-2" class="quizle-analytics__chart-graph"></div>
            </div>
        </div>

        <div id="graph-pulse" class="quizle-analytics__chart"></div>

        <script typeof="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                initQuizleAnalytics({
                    element: document.getElementById('graph-1'),
                    options: {
                        tooltip: {
                            trigger: 'axis'
                        },
                        xAxis: {
                            data: <?php echo json_encode( $stat->dates, JSON_UNESCAPED_UNICODE ) ?>
                        },
                        yAxis: {
                            type: 'value'
                        },
                        series: [
                            {
                                name: '<?php echo esc_js( __( 'Started', QUIZLE_TEXTDOMAIN ) ) ?>',
                                type: 'line',
                                data: <?php echo json_encode( $stat->created_counts, JSON_UNESCAPED_UNICODE ) ?>
                            },
                            {
                                name: '<?php echo esc_js( __( 'Finished', QUIZLE_TEXTDOMAIN ) ) ?>',
                                type: 'line',
                                data: <?php echo json_encode( $stat->finished_counts, JSON_UNESCAPED_UNICODE ) ?>
                            },
                        ]
                    }
                });
                initQuizleAnalytics({
                    element: document.getElementById('graph-2'),
                    options: {
                        tooltip: {
                            trigger: 'axis'
                        },
                        xAxis: {
                            data: <?php echo json_encode( $stat->dates, JSON_UNESCAPED_UNICODE ) ?>
                        },
                        yAxis: {
                            type: 'value'
                        },
                        series: [
                            {
                                name: '<?php echo esc_js( __( 'Conversion', QUIZLE_TEXTDOMAIN ) ) ?>',
                                type: 'line',
                                data: <?php echo json_encode( $stat->conversion, JSON_UNESCAPED_UNICODE ) ?>
                            }
                        ]
                    }
                })
            });
        </script>


        <div class="quizle-box quizle-analytics-questions">
            <div class="quizle-box__header quizle-analytics-questions__header">
                <div class="quizle-box-header__title"><?php echo __( 'Question Statistics for the Period', QUIZLE_TEXTDOMAIN ) ?></div>
            </div>

            <div class="quizle-analytics-questions__body">
                <?php foreach ( $analytics->get_answers_data( $args['post']->ID, $date_start, $date_end ) as $question ): ?>
                    <div class="quizle-analytics-questions-item">
                        <div class="quizle-analytics-questions-item__title"><?php echo esc_html( $question['title'] ) ?></div>
                        <ul class="quizle-analytics-answers-list">
                            <?php foreach ( $question['answers'] as $answer ): ?>
                                <?php $bg_percent = round( $answer['total'] ? $answer['count'] / $answer['total'] * 100 : 0 ) ?>
                                <li class="quizle-analytics-answers-list-item" style="background: linear-gradient(90deg, #e2dbff <?php echo $bg_percent ?>%, #f2f2fa <?php echo $bg_percent ?>%)">
                                    <div class="quizle-analytics-answers-list-item__title">
                                        <?php echo esc_html( $answer['name'] ) ?>
                                    </div>
                                    <div class="quizle-analytics-answers-list-item__value">
                                        <?php echo round( $answer['total'] ? $answer['count'] / $answer['total'] * 100 : 0, 1 ) ?>
                                        %
                                    </div>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div><!--.quizle-analytics-->

</div><!--.wrap-->
