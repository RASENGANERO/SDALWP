<!DOCTYPE html>

<?php header("Last-Modified: " . get_the_modified_date('r'))?>


<html lang="ru">

<head>

	<meta charset="UTF-8">

	<meta id="vp" name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" type="image/x-icon">
	
	
	<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" type="image/x-icon">
	<!-- <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.svg" type="image/svg+xml"> -->
			
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/fonts/fonts.css">

	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/slick.css">

	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.css?ver=2.0">

	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/media.css?ver=2">

	<?php wp_head(); ?>

	

</head>

<body class="<?php if(is_front_page()){echo 'front_page';} ?>" id="body">

	<div class="scroll_top"></div>

	<div class="top_line scroll" <?php if ( is_user_logged_in() ) { echo "style='top:32px'";} ?>>

		<div class="container">

			<a href="<?php echo home_url(); ?>" class="logo"><img src="<?php echo get_template_directory_uri(); ?>/img/logo.png" alt=""></a>

			<nav>

				<ul id="menu-top">

					<li <?php if(is_front_page()){ echo 'class="active"'; } ?> ><a href="<?php if(is_front_page()){ echo '#body'; }else{ echo "/";} ?>">ГЛАВНАЯ</a></li>

					<li><a href="#about">О нас</a></li>

					<li><a href="<?php if(!is_front_page()){ echo "/"; } ?>#our-team">НАША КОМАНДА</a></li>
										
					<li <?php if(in_category('work')){ echo 'class="active"'; } ?> ><a href="#work">Наши услуги</a></li>

					<li <?php if(in_category('universities')){ echo 'class="active"'; } ?> ><a href="#univer">Вузы</a></li>

					<li <?php if(in_category('articles')){ echo 'class="active"'; } ?>><a href="#article">СТАТЬИ</a></li>

					<li><a href="#reviews">ОТЗЫВЫ</a></li>

				</ul>

			</nav>

			<div class="header_right">
				<a href="#popup_callback" class="order_button">ЗАКАЗАТЬ ЗВОНОК</a>
				<div class="soc_box">
				<a href="<?php the_field('soc_item1', 2); ?>" target="_blank" class="soc_icon"><img src="<?php echo get_template_directory_uri(); ?>/img/vk_icon.png" alt=""></a>
				<a href="https://wa.me/<?php the_field('soc_item2', 2); ?>" target="_blank" class="soc_icon"><img src="<?php echo get_template_directory_uri(); ?>/img/whatsap_icon1.png" alt=""></a>
				<a href="mailto:<?php the_field('soc_item3', 2); ?>" target="_blank" class="soc_icon"><img src="<?php echo get_template_directory_uri(); ?>/img/mail_icon1.png" alt=""></a>
				</div>
				<div class="nav_burger"></div>
			</div>

		</div>

	</div>