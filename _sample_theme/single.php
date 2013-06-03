<?php
$rdk->set_post();
get_header();
?>

<div class="row">
	<div class="span8">
		<?php while ( have_posts() ) : the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1><?php the_title(); ?></h1>
			<div class="entry-content">
				<?php the_content(); ?>
				<?php wp_link_pages(array('before' => '<div class="page-link">Pages:', 'after' => '</div>')); ?>
			</div><!-- .entry-content -->
		</div><!-- #post- -->
		
		
		<?php if(get_the_author_meta('description')): ?>
		<div id="entry-author-info" class="well">
			<div id="author-avatar">
				<?php echo get_avatar(get_the_author_meta('user_email'), apply_filters('twentyten_author_bio_avatar_size', 60)); ?>
			</div>
			<!-- #author-avatar -->
				
			<div id="author-description">
				<h2><?php printf('About %s', get_the_author()); ?></h2>
				<?php the_author_meta( 'description' ); ?>
					
				<div id="author-link">
					<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
						View all posts by <?php the_author(); ?> <span class="meta-nav">&raquo;</span>
					</a>
				</div>
				<!-- #author-link	-->
			</div>
			<!-- #author-description -->
		</div>
		<!-- #entry-author-info -->
		<?php endif; ?>
		
		
		<div id="nav-below" class="navigation">
			<hr>
			<div class="pull-left">
				<?php next_post_link( '%link', '<span class="meta-nav">&laquo;</span> %title' ); ?>
			</div>
			<div class="pull-right">
				<?php previous_post_link( '%link', '%title <span class="meta-nav">&raquo;</span>' ); ?>
			</div>
			<div class="clear"></div>
			<hr>
		</div><!-- #nav-below -->
		
		
		<?php comments_template( '', true ); ?>
		
		
		<?php endwhile; ?>
	</div>

	<div class="span4">
		<?php get_sidebar('blog'); ?>
	</div>
</div>

<?php get_footer(); ?>