<?php 
/*
	Template Name: Вузы
	Template Post Type: post, page, product
*/

get_header(); ?>
<div class="rings_box">
	<div class="container">
			<div class="ring_item ring_item3_1"><span></span></div>
			<div class="ring_item ring_item3_2"><span></span></div>
		 <div class="ring_item ring_item3_4"><span></span></div>
			<div class="ring_item ring_item3_5"><span></span></div>
			<div class="ring_item ring_item3_6"><span></span></div>
			<div class="ring_item ring_item3_7"><span></span></div>
	</div>
</div>
<div class="rings_box rings_box__index">
	<div class="container">
		<div class="ring_item ring_item3_3"><span></span></div>
	</div>
	</div>
<header class="header_service">
	<div class="container">
		<div>
			<h1 style="max-width: 500px;"><?php the_title(); ?></h1>
			<div class="header_img header_img3">
				<div class="img_box" style='background: url("<?php echo get_the_post_thumbnail_url(); ?>") no-repeat center; background-size: contain;'></div>
			</div>
			<div class="header_form">
					<div class="form_title">ХОТИТЕ УЗНАТЬ СТОИМОСТЬ РАБОТЫ?</div>
					<span>Заполните форму и мы с Вами свяжемся <br>в ближайшее время.</span>
					<?php echo do_shortcode( '[contact-form-7 id="106" title="Узнать стоимость"]' ); ?>
			</div>
		</div>
		<div class="service_box">
			<div class="service_item">
				<div class="service_icon">
					<div class="icon">
						<span></span>
						<div><img src="<?php echo get_template_directory_uri(); ?>/img/service_icon1.svg" alt="Доступные цены"></div>
					</div>
				</div>
				<div class="service_title">Доступные цены</div>
				<div class="service_desc">Задачи от 800 рублей, <br>рефераты от 2000 рублей,<br>курсовые от 5000 рублей</div>
			</div>
			<div class="service_item">
				<div class="service_icon">
					<div class="icon">
						<span></span>
						<div><img src="<?php echo get_template_directory_uri(); ?>/img/service_icon2.svg" alt="Сроки от 6 часов"></div>
					</div>
				</div>
				<div class="service_title">Сроки от 6 часов</div>
				<div class="service_desc">Мы выполняем самые <br>срочные работы</div>
			</div>
			<div class="service_item">
				<div class="service_icon">
					<div class="icon">
						<span></span>
						<div><img src="<?php echo get_template_directory_uri(); ?>/img/service_icon3.svg" alt="Бесплатные правки"></div>
					</div>
				</div>
				<div class="service_title">Бесплатные правки</div>
				<div class="service_desc">После написания работы  <br>правки могут вноситься <br>абсолютно <i>БЕСПЛАТНО</i></div>
			</div>
		</div>
	</div>
</header>
<?php
	$marksArray = getMarks($post->ID);
?>
	<div class="article_section">
		<div class="container">
			<?php the_content(); ?>
			<? if (!empty($marksArray)): ?>
				<div class="mark-container">
					<? foreach($marksArray as $markItem):?>
					<div class="mark-item">
						<span class="mark-item-text"><?php echo $markItem->name;?></span>
					</div>
					<? endforeach; ?>
				</div>
			<? endif; ?>
		</div>
	</div>
	<div class="reviews" id="reviews">
		<div class="container">
			<?php comments_template(); ?>
		</div>
	</div>
	<div class="univer">
		<div class="container">
			<div class="section_title section_univer" id="univer"><strong>ДРУГИЕ ВУЗы,</strong> по которым <span>мы работаем</span>
			</div>
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
	<div class="work" id="work">
		<div class="container">
			<div class="section_title"><strong>РАБОТЫ,</strong> которые <span>мы выполняем</span></div>
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
								<div class="work_title"><?php the_title();?></div>
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
								<a href="<?php the_permalink(); ?>" class="work_button" alt="<?php the_sub_field('work_col1'); ?>" alt="<?php the_title();?>" alt="<?php the_title();?>" alt="<?php the_title();?>" >ПОДРОБНЕЕ</a>
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
			<div class="section_title"><span>Полезные</span> <strong>СТАТЬИ</strong> от наших специалистов</div>
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
									<div class="artic_title"><?php the_title(); ?></div>
									<div class="artic_auth"><?php the_field('artic_auth'); ?></div>
									<a href="<?php the_permalink(); ?>" class="artic_show">ПОДРОБНЕЕ</a>
								</div>
							</div>
						<?php endwhile; ?>
				<?php endif; ?>
				<?php wp_reset_query();?>
			</div>
		</div>
	</div>
<?php get_footer(); ?>