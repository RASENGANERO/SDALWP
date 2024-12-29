<?php

/**
 * @version 1.3.0
 */

use Wpshop\Quizle\Admin\Settings;
use Wpshop\Quizle\Css\CssBuilder;
use Wpshop\Quizle\Db\Database;
use function Wpshop\Quizle\container;
use const Wpshop\Quizle\RESULT_REQUEST_VAR;

$settings = container()->get( Settings::class );

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>

</head>

<body <?php body_class( 'quizle-result' ); ?>>

<?php wp_body_open(); ?>

<div class="quizle-result-page">
    <?php
    $result = null;
    if ( $token = $_REQUEST[ RESULT_REQUEST_VAR ] ?? null ) {
        $result = container()->get( Database::class )->get_quizle_result_by_token( $token );
    }
    if ( $result && ( $result_item = $result->get_result_item() ) ) {
        $color_primary    = get_post_meta( $result->quiz_id, 'quizle-color-primary', true ) ?: null;
        $color_background = get_post_meta( $result->quiz_id, 'quizle-color-background', true ) ?: null;
        $color_text       = get_post_meta( $result->quiz_id, 'quizle-color-text', true ) ?: null;
        $styles           = new CssBuilder( '.quizle--' . $result->quiz_id );
        $styles->add( '', [
            '--quizle-primary-color'      => get_post_meta( $result->quiz_id, 'quizle-color-primary', true ) ?: null,
            '--quizle-primary-color-text' => get_post_meta( $result->quiz_id, 'quizle-color-text-primary', true ) ?: null,
            '--quizle-background'         => get_post_meta( $result->quiz_id, 'quizle-color-background', true ) ?: null,
            '--quizle-background-1'       => get_post_meta( $result->quiz_id, 'quizle-color-background-1', true ) ?: null,
            '--quizle-background-2'       => get_post_meta( $result->quiz_id, 'quizle-color-background-2', true ) ?: null,
            '--quizle-text-color'         => get_post_meta( $result->quiz_id, 'quizle-color-text', true ) ?: null,
        ] );

        $quizle_style = '';
        if ( 'background' === $result_item['image_position'] ) {
            $quizle_style = 'background-image: url("' . $result_item['image'] . '")';
        }

        ?>

        <div class="quizle-container">
            <style><?php echo $styles ?></style>
            <div class="quizle quizle--<?php echo $result->quiz_id ?> quizle--view-slides">
                <div class="quizle-body">
                    <div class="quizle-results">
                        <?php echo $result->get_result_content() ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
    } else {
        ?>

        <div>Stored result not found</div>

        <?php
    } ?>

</div>

<?php wp_footer(); ?>
</body>
</html>
