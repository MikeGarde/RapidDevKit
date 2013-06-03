<?php get_header(); ?>

<div class="row">
	<div class="span8">
		<?php if(have_posts()): ?>
		
		<h1>Search Results for: <span><?php echo get_search_query(); ?></span></h1>
		<?php get_template_part( 'loop', 'search' ); ?>
		
		<?php else: ?>
			
		<h1>Nothing Found</12>
		<p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>		
		
		<?php endif; ?>
	</div>

	<div class="span4">
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer(); ?>