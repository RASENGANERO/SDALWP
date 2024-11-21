<?php 
/*
	Template Name: Главная
	Template Post Type: post, page, product
*/


get_header();  ?>
<div class="rings_box">
	<div class="container">
		<div class="ring_item ring_item3"><span></span></div>
		<div class="ring_item ring_item4"><span></span></div>
		<div class="ring_item ring_item5"><span></span></div>
		<div class="ring_item ring_item7"><span></span></div>
		<div class="ring_item ring_item8"><span></span></div>
		<div class="ring_item ring_item9"><span></span></div>
		<div class="ring_item ring_item10"><span></span></div>
		<div class="ring_item ring_item11"><span></span></div>
		<div class="ring_item ring_item12"><span></span></div>
	</div>
</div>
	<div class="rings_box rings_box__index" hidden>
		<div class="container">
			<div class="ring_item ring_item6"><span></span></div>
		</div>
	</div>
	<header class="front_header front_page">
		<div class="container">
			<div class="service_wrap">
				<div class="service_info">
					<div class="leader_box">
						<div class="leader_photo">
							<div class="ring_item ring_item1"><span></span></div>
							<div class="ring_item ring_item2"><span></span></div>
							<img src="<?php echo get_template_directory_uri(); ?>/img/leader_photo.png" alt="">
						</div>
						<div class="leader_info">
							<div class="leader_post">РУКОВОДИТЕЛЬ ПРОЕКТА</div>
							<div class="leader_name"><div>Александра <strong>ФИЛИППОВА</strong></div></div>
						</div>
					</div>
					<div class="header_box">
						<div class="header_title">
							<h1>“<span>СЕССИЮ</span> СДАЛ!”</h1>
							<div><div class="hyphen"></div> СОВРЕМЕННЫЙ</div>
							<div><span>центр</span> помощи</div>
							<div><strong>студентам</strong></div>
						</div>
						<div class="header_desc"><?php the_content(); ?></div>
					</div>
				</div>
				<div class="service_box">
					<div class="service_item">
						<div class="service_icon">
							<div class="icon">
								<span></span>
								<div><img src="<?php echo get_template_directory_uri(); ?>/img/service_icon1.svg" alt=""></div>
							</div>
						</div>
						<div class="service_title">Доступные цены</div>
						<div class="service_desc">Задачи от 800 рублей <br>Pефераты от 2000 рублей <br>Курсовые от 5000 рублей</div>
					</div>
					<div class="service_item">
						<div class="service_icon">
							<div class="icon">
								<span></span>
								<div><img src="<?php echo get_template_directory_uri(); ?>/img/service_icon2.svg" alt=""></div>
							</div>
						</div>
						<div class="service_title">Сроки от 6 часов</div>
						<div class="service_desc">Мы выполняем самые <br>срочные работы</div>
					</div>
					<div class="service_item">
						<div class="service_icon">
							<div class="icon">
								<span></span>
								<div><img src="<?php echo get_template_directory_uri(); ?>/img/service_icon3.svg" alt=""></div>
							</div>
						</div>
						<div class="service_title">Бесплатные правки</div>
						<div class="service_desc">После написания работы  <br>правки могут вноситься <br>абсолютно <i>БЕСПЛАТНО</i></div>
					</div>
				</div>
			</div>
			<div class="header_form">
				<div class="form_title">ХОТИТЕ УЗНАТЬ СТОИМОСТЬ РАБОТЫ?</div>
				<span>Заполните форму и мы с Вами свяжемся <br>в ближайшее время.</span>
				<?php echo do_shortcode( '[contact-form-7 id="106" title="Узнать стоимость"]' ); ?>
			</div>
		</div>
	</header>
	<div class="work" id="work">
		<div class="container">
			<div class="section_title"><h2><strong>РАБОТЫ,</strong> которые <span>мы выполняем</span></h2></div>
			<div class="work_wrap">
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
								<div class="work_icon"><span><img src="<?php echo get_template_directory_uri(); ?>/img/work_icon.svg" alt=""></span></div>
								<h3 class="work_title"><a href="<?php the_permalink(); ?>"  title="<?php the_title();?>"><?php the_title();?></a></h3>
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
								<a href="<?php the_permalink(); ?>" class="work_button">ПОДРОБНЕЕ</a>
							</div>		  		
				  <?php endwhile; ?>
				 
				<?php endif; ?>
				<?php wp_reset_query();?>
			</div>
			<a href="#" class="show_more"><span>ПОКАЗАТЬ ЕЩЕ</span> <i></i></a>
		</div>
	</div>
	<div class="scroll_content">
		
		
		
		
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
	<div class="our_team" id="about">
		<div class="container">
			<div class="section_title"><h2>О нас</h2></div>
		</div>
		<div class="visible-super supervisor">
            <div class="video-container">
                <div class="video-text-container">
                    <span class="video-text">СЕССИЮ СДАЛ! — это центр с многолетним опытом работы, который помогает студентам всех ВУЗов и специальностей успешно справляться с учебными задачами. Мы предлагаем высококвалифицированную поддержку в сдаче сессии, благодаря нашим преподавателям высших учебных заведений и экспертам с многолетним опытом в различных научных областях.</span>
                    <span class="video-text">Мы предлагаем сделать этот процесс проще и эффективнее, чтобы у вас было больше времени на важные дела. С нами возможна дистанционная сдача экзаменов и написание работ. Кроме того, наши специалисты готовы проконсультировать по всем предметам и темам.</span>
                    <span class="video-text"></span>
                </div>
                <div class="video-visor-container">
                    <video class="video-visor" controls>
                        <source src="<?php echo get_template_directory_uri(); ?>/video/about.mp4" type="video/mp4" />
                    </video>
                </div>
            </div>
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
	</div>
		
		
		
		</div>
<?php get_footer(); ?>