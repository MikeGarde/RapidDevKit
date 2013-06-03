<?php get_header(); ?>

<div class="row">
	<div class="span8">
		<h1>Tag Archives: <span><?php echo single_tag_title( '', false ); ?></span></h1>
		<?php get_template_part( 'loop', 'tag' ); ?>
	</div>

	<div class="span4">
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer(); ?>