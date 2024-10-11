<?php return; ?>
<!--<?php 
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
	global $wpdb;
?>
<div class="price_form">
		<div class="container">
			<div class="form_icon">
				<span class="main_icon">?</span>
				<span class="form_icon__item form_icon1">?</span>
				<span class="form_icon__item form_icon2">?</span>
				<span class="form_icon__item form_icon3">?</span>
				<span class="form_icon__item form_icon4">?</span>
			</div>
			<div class="form_info__main">
				<div class="form_title">УЗНАТЬ СТОИМОСТЬ <br>СВОЕЙ РАБОТЫ</div>
				<span>Заполните форму и мы с Вами свяжемся <br> в ближайшее время.</span>
			</div>
			<div class="header_form">
				<?php echo apply_shortcodes( '[contact-form-7 id="106" title="Узнать стоимость"]' ); ?>
			</div>
		</div>
	</div>
	<div class="univer" >
		<div class="container">
			<div class="section_title section_univer" id="univer"><h2><strong>ВУЗы,</strong> по которым <span>мы работаем</h2></span>
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
								<div class="univer_title">
									<div class="univer_icon">
										<span>
											<img src="<?php echo get_template_directory_uri(); ?>/img/univer_icon.svg" alt="<?php the_field('univer_name'); ?>">
										</span>
										</div> <?php the_field('univer_name'); ?>
									</div>
									<div class="univer_desc"><?php the_field('univer_desc'); ?></div>
								<a href="<?php the_permalink(); ?>" class="univer_more" alt="<?php the_field('univer_name'); ?>" alt="<?php the_field('univer_name'); ?>">ПОДРОБНЕЕ</a>
							</div>
						<?php endwhile; ?>
					<?php endif; ?>
					<?php wp_reset_query();?>
			</div>
			<a href="#" class="show_more"><span>ПОКАЗАТЬ ЕЩЕ</span> <i></i></a>
		</div>
	</div>
	<div class="our_team" id="our-team">
		<div class="container">
			<div class="section_title"><h2>Наша <strong>КОМАНДА</strong></h2></div>
		</div>
		<div class="supervisor">
			<div class="container">
				<div class="visor_info">
					<div class="visor_post"><?php the_field("supervisor_main",2); ?></div>
					<div class="visor_name"><span><?php the_field("supervisor_name",2); ?></span> <?php the_field("supervisor_fname",2); ?></div>
					<div class="visor_desc"><?php the_field("supervisor_info",2); ?></div>
				</div>
				<div class="visor_photo">
					<img src="<?php echo get_template_directory_uri(); ?>/img/visor_photo.png" alt="">
				</div>
			</div>
		</div>
		<div class="container">
			<div class="team_box">
				<?php
					if( have_rows('spec_item',2) ):
					 while ( have_rows('spec_item',2) ) : the_row(); ?>
							<div class="team_item">
								<div class="team_info">
									<div class="team_photo"><img src="<?php the_sub_field('spec_photo',2); ?>" alt=""></div>
									<div class="team_desc">
									<?php the_sub_field('spec_desc',2); ?>
									</div>
									<div class="team_name"><div><?php the_sub_field('spec_name',2); ?> <strong><?php the_sub_field('spec_fname',2); ?></strong></div></div>
								</div>
							</div>					 	
					        
					 <?php  endwhile;
					 else :
					endif;
				?>
			</div>
		</div>
	</div>
	<div class="article" id="article">
		<div class="container">
			<div class="section_title"><h2><span>Полезные</span> <strong>СТАТЬИ</strong> от наших специалистов</h2></div>
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
								<div class="artic_img"><img data-lazy="<?php echo get_the_post_thumbnail_url(); ?>"></div>
								<div class="artic_info">
									<div class="artic_title"><?php the_title(); ?></div>
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
	<div class="form_order">
		<div class="container">
			<img class="form_order_after" src="<?php echo get_template_directory_uri(); ?>/img/form_order_after.png" alt="">
			<div class="header_form">
				<?php echo apply_shortcodes( '[contact-form-7 id="106" title="Узнать стоимость"]' ); ?>
			</div>
			<div class="form_order__desc">
				<div class="form_title">Мы гарантируем Вам <br><strong>УВЕРЕННОСТЬ</strong> и <br><strong>СПОКОЙСТВИЕ</strong> на сессии</div>
				<div class="form_desc">Коллектив <strong>“СЕССИЮ СДАЛ!”</strong> - <br>это Ваша надежная поддержка <br>во время учебы.</div>
			</div>
		</div>
	</div>
	<div class="reviews" id="reviews">
		<div class="container">
			<div class="section_title"><h2><strong>ОТЗЫВЫ</strong></h2></div>
			<div id="mc-container"></div>
			<a id="mc-link" href="https://cackle.me">Комментарии для сайта <b style="color:#4FA3DA">Cackl</b><b style="color:#F65077">e</b></a>
		</div>
	</div>-->