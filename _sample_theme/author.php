<?php get_header(); ?>

<div class="row">
	<div class="span8">
		<?php if(have_posts()) the_post(); ?>
		<h1 class="page-title author">
			Author Archives: <span><?php the_author(); ?></span>
		</h1>
		
		
		<?php if(get_the_author_meta('description')): ?>
		<div id="entry-author-info" class="well">
			<div id="author-avatar">
				<?php echo get_avatar(get_the_author_meta('user_email'), apply_filters('twentyten_author_bio_avatar_size', 60)); ?>
			</div>
			<!-- #author-avatar -->
				
			<div id="author-description">
				<h2>About <?php the_author_posts_link(); ?></h2>
				<?php the_author_meta('description'); ?>
			</div>
			<!-- #author-description	-->
		</div>
		<!-- #entry-author-info -->
		<?php endif; ?>


		<?php
		rewind_posts();
		get_template_part('loop', 'author');
		?>
	</div>

	<div class="span4">
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer(); ?>