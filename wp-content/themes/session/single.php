<?php get_header(); ?>
<?php the_tags( 'Метки: ', ', ' ); ?>
<?php
	$marksArray = getMarks($post->ID);
?>

<main class="main_page">
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
</main>
<?php get_footer(); ?>