<?php 
	
	/*
		Template Name: Работы
		Template Post Type: post, page, product
	*/
	
get_header();  ?>
<div class="rings_box">
	<div class="container">
		<div class="ring_item ring_item2_1"><span></span></div>
		<div class="ring_item ring_item2_2"><span></span></div>
		<div class="ring_item ring_item2_3"><span></span></div>
		<div class="ring_item ring_item2_4"><span></span></div>
		<div class="ring_item ring_item2_5"><span></span></div>
		<div class="ring_item ring_item2_6"><span></span></div>
		<div class="ring_item ring_item2_7"><span></span></div>
		<div class="ring_item ring_item2_8"><span></span></div>
	</div>
</div>
<header>
	<div class="container">
		<div>
			<?php
				if (get_field("h1zag")) {
					echo "<h1>" . get_field("h1zag") . "</h1>";
					} else {
					echo "<h1>" . get_the_title() . "</h1>";
				}
			?>
			
			
			<?php // Условия ?>
			
			<div class="items usl">
				
				<?php if (get_field("sroki_vypolneniya")) { ?>
					<div class="item">
						<div class="inn">
							<img src="/wp-content/themes/session/img/sroki.png">
							<div class="all">
								
								<span>Срок выполнения</span>
								<?php the_field("sroki_vypolneniya"); ?>
							</div>
						</div>
					</div>
				<?php } ?>
				
				<?php if (get_field("stoimost")) { ?>
					<div class="item">
						<div class="inn">
							<img src="/wp-content/themes/session/img/stoim.png">
							<div class="all">
								
								<span>Стоимость</span>
								<?php the_field("stoimost"); ?>
							</div>
						</div>
					</div>
					<?php } ?>
					
					
					<?php if (get_field("dorabotka")) { ?>
					<div class="item">
					<div class="inn">
					<img src="/wp-content/themes/session/img/dorab.png">
					<div class="all">
					
					<span>Доработка</span>
					<?php the_field("dorabotka"); ?>
					</div>
					</div>
					</div>
					<?php } ?>
					
					</div>
					
					
					<div class="header_img header_img2">
					<div class="img_box" style='background: url("<?php echo get_the_post_thumbnail_url(); ?>") no-repeat center; background-size: contain;'></div>
					</div>
					<div class="header_form">
					<div class="form_title">ХОТИТЕ УЗНАТЬ СТОИМОСТЬ РАБОТЫ?</div>
					<span>Заполните форму и мы с Вами свяжемся <br>в ближайшее время.</span>
					<?php echo do_shortcode( '[contact-form-7 id="106" title="Узнать стоимость"]' ); ?>
					</div>
					</div>
					</div>
					</header>
					<div class="article_section" style="padding-top: 93px;">
					<div class="container">
					<?php the_content(); ?>
					</div>
					</div>
					<noindex><div class="excell_txt">
					<div class="container">
					<div class="excell_info">
					<div class="section_title">ПОЧЕМУ <span>ЛУЧШЕ</span> ЗАКАЗАТЬ  <br><strong>ДИПЛОМНУЮ РАБОТУ</strong>  <br>ИМЕННО У НАС?</div>
					<div class="excell_desc">Нет идей и времени на выполнение дипломной работы? Центр помощи студентам - <br><strong>“СЕССИЮ СДАЛ!”</strong> возьмет эту проблему на себя. Наши специалисты готовы быстро и <br>качественно написать дипломную работу на заказ. А дальше уже дело техники. Вы<br> можете смело обращаться к нам!</div>
					</div>
					<div class="excell_photo">
					<img src="<?php echo get_template_directory_uri(); ?>/img/excell_photo.png" alt="">
					</div>
					</div>
					</div></noindex>
					<div class="reviews" id="reviews">
					<div class="container">
					<div class="section_title"><strong>ОТЗЫВЫ</strong></div>
					
					<?php comments_template(); ?>
					
					</div>
					</div>
					<div class="work" id="work">
					<noindex>
					<div class="container">
					<h2 class="section_title"><strong>ДРУГИЕ РАБОТЫ,</strong> которые <span>мы выполняем</span></h2>
					<div class="work_wrap work_slider">
					<?php
					$wp_query = new WP_Query( array(
					'post_type' => 'post',
					'posts_per_page' => -1,
					'cat' => 2,
					'orderby'=> 'title'
					));
					?>
					<?php if ( have_posts() ) : ?>
					
					<?php while ( have_posts() ) : the_post(); ?>
					<div class="work_item">
					<div class="work_icon"><span><img src="<?php echo get_template_directory_uri(); ?>/img/work_icon.svg" alt="<?php the_title();?>"></span></div>
					<h3 class="work_title"><a href="<?php the_permalink(); ?>"  alt="<?php the_title();?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
					<ul class="work_desc">
					<?php
					if( have_rows('work_price') ):
					while ( have_rows('work_price') ) : the_row(); ?>
					<li><?php the_sub_field('work_col1'); ?> 
					<strong><?php the_sub_field('work_col2'); ?></strong>
					</li>
					<?php endwhile;
					else :
					endif;
					?>
					</ul>
					<a href="<?php the_permalink(); ?>" class="work_button" alt="<?php the_sub_field('work_col1'); ?>">ПОДРОБНЕЕ</a>
					</div>		  		
					<?php endwhile; ?>
					
					<?php endif; ?>
					<?php wp_reset_query();?>
					</div>
					<a href="#" class="show_more"><span>ПОКАЗАТЬ ЕЩЕ</span> <i></i></a>
					</div>
					</noindex>
					</div>
					<div class="univer">
					<div class="container">
					<h2 class="section_title section_univer" id="univer"><strong>ВУЗы,</strong> по которым <span>мы работаем</span>
					</h2>
					<div class="univer_wrap">
					<?php
					$wp_query = new WP_Query( array(
					'post_type' => 'post',
					'posts_per_page' => -1,
					'cat' => 1,
					'orderby'=> 'title'
					));
					?>
					<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
					<div class="univer_item">
					<a href="<?php the_permalink(); ?>" class="univer_title">
					<div class="univer_icon">
					<span>
					<img src="<?php echo get_template_directory_uri(); ?>/img/univer_icon.svg" alt="<?php the_field('univer_name'); ?>">
					</span>
					</div> <?php the_field('univer_name'); ?>
					</a>
					<div class="univer_desc"><?php the_field('univer_desc'); ?></div>
					<a href="<?php the_permalink(); ?>" class="univer_more" alt="<?php the_field('univer_name'); ?>">ПОДРОБНЕЕ</a>
					</div>
					<?php endwhile; ?>
					<?php endif; ?>
					<?php wp_reset_query();?>
					</div>
					<a href="#" class="show_more"><span>ПОКАЗАТЬ ЕЩЕ</span> <i></i></a>
					</div>
					</div>
					<div class="article" id="article">
					<div class="container">
					<h2 class="section_title"><strong>ДРУГИЕ СТАТЬИ</strong> от наших специалистов</h2>
					
					<div class="artic_box">
					<?php
					$wp_query = new WP_Query( array(
					'post_type' => 'post',
					'posts_per_page' => -1,
					'cat' => 3,
					'orderby'=> 'title'
					));
					?>
					<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
					<div class="artic_item">
					<div class="artic_img"><?php echo get_the_post_thumbnail(); ?></div>
					<div class="artic_info">
					<h3 class="artic_title"><?php the_title(); ?></h3>
					<div class="artic_auth"><?php the_field('artic_auth'); ?></div>
					<a href="<?php the_permalink(); ?>" class="artic_show" alt="<?php the_title(); ?>">ПОДРОБНЕЕ</a>
					</div>
					</div>
					<?php endwhile; ?>
					<?php endif; ?>
					<?php wp_reset_query();?>
					</div>
					
					</div>
					</div>
					<?php get_footer(); ?>																				