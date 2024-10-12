<?php
/*
Template Name: Страница о нас
*/
?>
<style>
	#menu-top li{
		display: none;
	}
</style>
<? get_header();  ?>
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
						<div class="service_desc">Задачи от 800 рублей, <br>рефераты от 2000 рублей,<br>курсовые от 5000 рублей</div>
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
    <div class="our_team" id="our-team">
		<div class="container">
			<div class="section_title"><h2>О нас</h2></div>
		</div>
		<div class="visible-super supervisor">
            <div class="video-container">
                <div class="video-text-container">
                    <span class="video-text">СЕССИЮ СДАЛ! — это центр с многолетним опытом работы, который помогает студентам всех ВУЗов и специальностей успешно справляться с учебными задачами. Мы предлагаем высококвалифицированную поддержку в сдаче сессии, благодаря нашим преподавателям высших учебных заведений и экспертам с многолетним опытом в различных научных областях.</span>
                    <span class="video-text">Мы предлагаем сделать этот процесс проще и эффективнее, чтобы у вас было больше времени на важные дела. С нами возможна дистанционная сдача экзаменов и написание работ. Кроме того, наши специалисты готовы проконсультировать по предметам и темам.</span>
                    <span class="video-text">Наш опыт позволяет гарантировать успешную сдачу благодаря прямому взаимодействию с исполнителями, что исключает недопонимания. Мы обеспечиваем полную конфиденциальность, высокое качество и выполнение работы в срок. С СЕССИЮ СДАЛ! сдача сессии — это просто, надежно и быстро!</span>
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
		<div class="visible-super supervisor">
			<div class="visible-super-container container">
				<div class="visor-new visor_info">
					<div class="visor-new_post visor_post"><?php the_field("supervisor_main",2); ?></div>
					<div class="visor-new_name visor_name"><span><?php the_field("supervisor_name",2); ?></span> <?php the_field("supervisor_fname",2); ?></div>
					<div class="visor-new_desc visor_desc"><?php the_field("supervisor_info",2); ?></div>
				</div>
				<img class="image-visor" src="<?php echo get_template_directory_uri(); ?>/img/visor_photo.png" alt="">
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
		</div>
<?php get_footer(); ?>