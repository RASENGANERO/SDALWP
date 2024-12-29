<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Wpshop\Quizle\Admin\Settings;
use function Wpshop\Quizle\container;

/**
 * @var array{'label':string} $args
 */

$settings = container()->get( Settings::class );

?>

<?php $settings->render_header(
    __( 'Additional styles', QUIZLE_TEXTDOMAIN ),
    __( 'In this section you can specify any styles you want for the alphabetical index. Use as a normal CSS editor, starting each rule with a selector. For example, .quizle { /* your code */ }', QUIZLE_TEXTDOMAIN ),
    $settings->doc_link( 'doc' ) . '/settings/#additional-styles'
); ?>

<div class="abc-pagination-css-editor">
    <?php $settings->render_css_editor( 'styles' ); ?>
</div>

<p><?php printf( __( '<strong>We recommend:</strong> see examples of <a href="%s" target="_blank" rel="noopener">CSS variables</a>.', QUIZLE_TEXTDOMAIN ), $settings->doc_link( 'doc' ) . '/css-variables/' ) ?></p>
