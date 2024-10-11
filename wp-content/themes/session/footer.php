<div class="popup">
	<div class="popup_overlay"></div>
			<div class="popup_form" id="popup_callback">
				<div class="close"></div>
				<div class="popup_title">У ВАС ЕСТЬ К НАМ ВОПРОСЫ?</div>
				<span>Заполните форму и мы с Вами свяжемся <br>в ближайшее время.</span>
				<?php echo do_shortcode( '[contact-form-7 id="70" title="Заказать звонок"]' ); ?>
			</div>
			<div class="popup_form" id="popup_txt">
				<div class="close"></div>
				<p>Сдача сессии — один из важнейших этапов обучения для каждого студента. Он включает в себя не только подготовку к экзаменам и
					зачетам, но и написание лабораторных, контрольных и курсовых работ. Зачастую такие работы являются допусками к экзаменам, 
					успешная сдача которых, в свою очередь, является гарантией перехода на следующий курс обучения или окончания обучения в ВУЗе
				<p>
				<p>Мы предлагаем высококвалифицированную <strong>помощь со сдачей сессии</strong> для студентов любых ВУЗов и специальностей. 
					Среди наших специалистов преподаватели высших учебных заведений и профессиональные эксперты в различных областях наук.
				</p>
				<p>Можно ли <strong>сдать сессию самостоятельно</strong>? Да! Но на это потребуется много времени и сил. 
					Мы предлагаем облегчить процесс обучения в университете и потратить свободное от подготовки к экзаменам время 
					на более приятные занятия. С нами возможна <strong>сдача сессии дистанционно</strong>. 
					Наши эксперты всегда готовы не только написать работу или сдать за Вас экзамен, но и проконсультировать по теме, 
					предмету или специальности.
				</p>
				<p>Уникальность нашего проекта заключается в том, что Вы связываетесь с исполнителем напрямую, что исключает 
					недопонимание между заказчиком и экспертом, и повышает вероятность успешной сдачи работы. 
					Мы гарантируем конфиденциальность, высокое качество и выполнение работы в срок.
				</p>
			</div>
</div>
	<footer>
		<div class="container">
			<div class="row">
				<div class="f_col">
					<div class="f_left">
						<div class="f_title"><?php the_field('f_title',202); ?></div>
						<div class="f_info">
							<span><?php the_field('f_info1',202); ?></span>
							<span><?php the_field('f_info2',202); ?></span>
						</div>
						<div class="f_pay">
							<span><?php the_field('f_pay',202); ?></span>
							<ul>
								<li><img style="width:98px" src="https://sessiusdal.ru/wp-content/themes/session/img/f_pay1.png" alt=""></li>
								<li><img src="https://sessiusdal.ru/wp-content/themes/session/img/f_pay2.svg" alt=""></li>
							</ul>
						</div>
					</div>
					<div class="f_nav">
						<div class="soc_button">
							<ul>
								<?php if(get_field('soc_item1', 2)){ ?>
									<li>
										<a href="<?php the_field('soc_item1', 2) ?>" rel="nofollow">
											<span class="icon_box"><span class="icon"><img src="<?php echo get_template_directory_uri(); ?>/img/soc_icon1.svg" alt=""></span></span> МЫ В ВКОНТАКТЕ
										</a>
									</li>
								<?php } ?>
								<?php if(get_field('soc_item2', 2)){ ?>
									<li>
													<a href="https://wa.me/<?php the_field('soc_item2', 2); ?>" target="_blank" class="soc_icon">
											<span class="icon_box"><span class="icon"><img style="width:30px;" src="<?php echo get_template_directory_uri(); ?>/img/whatsap_icon1.png" alt=""></span></span> МЫ В WHATSAPP
										</a>
									</li>
								<?php } ?> 
							</ul>
						</div>
						<nav>
							<?php wp_nav_menu( [ 'menu' => 'footer_nav' ] ); ?>
							<a href="mailto:SessiuSdal@yandex.ru" style="color:#333;">SessiuSdal@yandex.ru</a>
						</nav>
					</div>
				</div>
				<div class="f_img">
					<div class="img_item"><img src="<?php the_field('f_img1', 202) ?>" alt=""></div>
					<div class="img_item"><img src="<?php the_field('f_img2', 202) ?>" alt=""></div>
				</div>
			</div>
			<div class="f_copyright">© <?php echo date('Y') ?>. sessiusdal.ru <span>|</span><br> Все права защищены.</div>
		</div>
	</footer>

	<script src="<?php echo get_template_directory_uri(); ?>/libs/jquery.min.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/libs/slick.min.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/libs/jquery.maskedinput.min.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/libs/common.js?ver=1.0"></script>
	<?php wp_footer(); ?>
	<!-- <script src="//code.jivo.ru/widget/EupusvNPo6" async></script> -->

</body>
</html>