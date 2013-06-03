<?php get_header(); ?>

<div class="row">
	<div class="span8">
		<?php get_template_part('loop', 'index'); ?>
	</div>

	<div class="span4">
		<?php get_sidebar('blog'); ?>
	</div>
</div>

<?php get_footer(); ?>