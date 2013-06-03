<?php get_header(); ?>

<div class="row">
	<div class="span8">
		<h1>Category Archives: <span> <?php echo single_cat_title('', false); ?></span></h1>
		
		<?php
		$category_description = category_description();
		if(!empty($category_description)): ?>
		<div class="well archive-meta">
			<?php echo $category_description ?>
		</div>
		<?php endif; ?>


		<?php get_template_part( 'loop', 'category' ); ?>
	</div>

	<div class="span4">
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer(); ?>