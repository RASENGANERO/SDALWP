<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<div class="quizle-page">

    <?php while ( have_posts() ) :

        the_post();
        $quizle = get_post();

//        echo '<h1 class="entry-title">' . get_the_title() . '</h1>';

        echo do_shortcode( '[quizle name=' . urldecode( $quizle->post_name ) . ']' );
    endwhile; ?>

</div>

<?php wp_footer(); ?>
</body>
</html>
