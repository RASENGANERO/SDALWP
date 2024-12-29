<?php

namespace Wpshop\ExpertReview;

use Wpshop\ExpertReview\Settings\CustomStyleOptions;

class CustomStyle {

    /**
     * @var CustomStyleOptions
     */
    protected $style_options;

    /**
     * CustomStyle constructor.
     *
     * @param CustomStyleOptions $style_options
     */
    public function __construct( CustomStyleOptions $style_options ) {
        $this->style_options = $style_options;
    }

    /**
     * @return void
     */
    public function init() {
        if ( $this->style_options->enabled ) {
            add_action( 'wp_head', [ $this, 'render_custom_styles' ] );
            add_action( 'admin_head', [ $this, 'render_custom_styles' ] );
        }
        do_action( __METHOD__, $this );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function render_custom_styles() {
        echo apply_filters( 'expert_review_custom_styles', er_ob_get_content( function ( CustomStyleOptions $options ) {
            ?>

            <style id="expert-review-custom-styles">
                <?php if (is_admin()): ?>
                .mce-window .mce-expert-review-color--custom {
                    background: <?php echo $options->primary_color ?>;
                }

                <?php endif ?>

                .expert-review--color-custom .expert-review-button,
                .expert-review--color-custom .expert-review-expert-bio__avatar,
                .expert-review--color-custom .expert-review-qa__avatar,
                .expert-review--color-custom .expert-review-score-summary__average {
                    background-color: <?php echo $options->primary_color ?>;
                }

                .expert-review--color-custom .expert-review-expert-bio-name,
                .expert-review--color-custom .expert-review-expert-header,
                .expert-review--color-custom .expert-review-pluses-minuses-header,
                .expert-review--color-custom .expert-review-qa-header,
                .expert-review--color-custom .expert-review-score-header,
                .expert-review--color-custom .expert-review-score-line__score {
                    color: <?php echo $options->primary_color ?>;
                }

                .expert-review--color-custom {
                    background: <?php echo $options->background_color ?>;
                }

                .expert-review--color-custom .expert-review-score-line__progress-fill {
                    background: <?php echo $options->primary_color ?>;
                    background-image: -webkit-gradient(linear, left top, right top, from(<?php echo $options->primary_color ?>), to(<?php echo $options->gradient_color ?>));
                    background-image: -webkit-linear-gradient(left,<?php echo $options->primary_color ?>,<?php echo $options->gradient_color ?>);
                    background-image: -o-linear-gradient(left,<?php echo $options->primary_color ?>,<?php echo $options->gradient_color ?>);
                    background-image: linear-gradient(to right,<?php echo $options->primary_color ?>,<?php echo $options->gradient_color ?>);
                }


                .expert-review-poll--color-custom.expert-review-poll--style-light-1 {
                    background: rgba(<?php echo $this->hex2rgba($options->primary_color) ?>, .05);
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-1 .expert-review-poll-item:before {
                    border-color: rgba(<?php echo $this->hex2rgba($options->primary_color) ?>, .4);
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-1 .expert-review-poll-item.voted:before {
                    border-color: <?php echo $options->primary_color ?>;
                    background: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll:not(.voted) .expert-review-poll--color-custom.expert-review-poll--style-light-1 .expert-review-poll-item:hover:before {
                    border-color: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-1 .expert-review-poll-item__progress {
                    background: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-1 .expert-review-poll__loader span {
                    border-left-color: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-2 .expert-review-poll__result-button {
                    border-color: rgba( <?php echo $this->hex2rgba($options->primary_color) ?>, .5);
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-2 .expert-review-poll-item {
                    border-color: rgba( <?php echo $this->hex2rgba($options->primary_color) ?>, .5);
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-2 .expert-review-poll-item.voted {
                    border-color: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-2 .expert-review-poll-item.voted:before {
                    border-color: <?php echo $options->primary_color ?>;
                    background: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-2 .expert-review-poll-item:before {
                    border-color: rgba(<?php echo $this->hex2rgba($options->primary_color)  ?>, .4);
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-2 .expert-review-poll-item__progress {
                    background: rgba(<?php echo $this->hex2rgba($options->primary_color) ?>, .08);
                }

                .expert-review-poll--color-custom.expert-review-poll--style-light-2 .expert-review-poll__loader span {
                    border-left-color: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll--color-custom.expert-review-poll--style-solid-1 {
                    color: <?php echo $options->background_color ?>;
                    background: <?php echo $options->primary_color ?>;
                }

                .expert-review-poll--color-custom.expert-review-poll--style-solid-1 .expert-review-poll__result-button {
                    color: <?php echo $options->background_color ?>;
                }


                .expert-review-faq--color-custom.expert-review-faq--style-simple-1 .expert-review-faq-item:after,
                .expert-review-faq--color-custom.expert-review-faq--style-simple-1 .expert-review-faq-item:before {
                    background: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-1 .expert-review-faq-item {
                    -webkit-box-shadow: 0 5px 25px -6px rgba(<?php echo $this->hex2rgba($options->primary_color) ?>, .2);
                    box-shadow: 0 5px 25px -6px rgba(<?php echo $this->hex2rgba($options->primary_color) ?>, .2)
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-1 .expert-review-faq-item__question {
                    color: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-2 .expert-review-faq-item.expand,
                .expert-review-faq--color-custom.expert-review-faq--style-style-2 .expert-review-faq-item.expand .expert-review-faq-item__answer {
                    border-color: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-2 .expert-review-faq-item:after,
                .expert-review-faq--color-custom.expert-review-faq--style-style-2 .expert-review-faq-item:before {
                    background: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-2 .expert-review-faq-item__question :hover {
                    background: rgba(233, 148, 0, .05)
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-3 .expert-review-faq-item:after,
                .expert-review-faq--color-custom.expert-review-faq--style-style-3 .expert-review-faq-item:before {
                    background: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-3 .expert-review-faq-item__question:hover {
                    color: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-3 .expert-review-faq-item__question:before {
                    background-color: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-3 .expert-review-faq-item__answer {
                    color: <?php echo $options->primary_color ?>;
                }

                .expert-review-faq--color-custom.expert-review-faq--style-style-4 .expert-review-faq-item__question {
                    background-color: <?php echo $options->primary_color ?>;
                }


            </style>
            <?php
        }, $this->style_options ) );
    }

    protected function hex2rgba( $hex, $wrap = false ) {
        $hex = ltrim( $hex, '#' );
        $hex = strtolower( $hex );

        if ( 6 === strlen( $hex ) ) {
            list( $r, $g, $b ) = sscanf( $hex, "%02x%02x%02x" );

            return $wrap ? "rgb($r,$g,$b)" : "$r,$g,$b";
        }

        list( $r, $g, $b, $a ) = sscanf( $hex, "%02x%02x%02x%02x" );
        $a = round( $a / 256, 2 );

        return $wrap ? "rgba($r,$g,$b,$a)" : "$r,$g,$b,$a";
    }
}
