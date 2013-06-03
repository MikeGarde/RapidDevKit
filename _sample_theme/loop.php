<?php if ( ! have_posts() ) : ?>
<div id="post-0" class="post error404 not-found">
	<h1 class="entry-title">Not Found</h1>
	<div class="entry-content">
		Apologies, but no results were found for the requested archive. Perhaps searching will help 
		find a related post.</p>
	</div><!-- .entry-content -->
</div><!-- #post-0 -->
<?php endif; ?>




<?php while ( have_posts() ) : the_post(); ?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<h2 class="entry-title">
		<a href="<?php the_permalink(); ?>" title="<?php printf('Permalink to %s', the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark">
			<?php the_title(); ?>
		</a>
	</h2>
	
	<div class="entry-meta">
		<?php //posted_on(); ?>
	</div><!-- .entry-meta -->

	<?php if(is_archive() || is_search()): ?>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
	<?php else : ?>
	<div class="entry-content">
		<?php the_content('Continue reading <span class="meta-nav">&raquo;</span>'); ?>
	</div><!-- .entry-content -->
	<?php endif; ?>
	
	<div class="entry-utility well">
		<?php if ( count( get_the_category() ) ) : ?>
		<span class="cat-links">
			<?php printf('<span class="%1$s">Posted in</span> %2$s', 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
		</span>
		<span class="meta-sep">|</span>
		<?php endif; ?>
		
		<?php
		$tags_list = get_the_tag_list( '', ', ' );
		if ( $tags_list ):
		?>
		<span class="tag-links">
			<?php printf('<span class="%1$s">Tagged</span> %2$s', 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
		</span>
		<span class="meta-sep">|</span>
		<?php endif; ?>
		
		<span class="comments-link">
			<?php comments_popup_link('Leave a comment', '1 Comment', '% Comments'); ?>
		</span>
		<?php edit_post_link('Edit', '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
	</div><!-- .entry-utility -->
</div><!-- #post-## -->


<?php comments_template( '', true ); ?>
<?php endwhile; ?>



<?php if($wp_query->max_num_pages > 1): ?>
<div id="nav-below" class="navigation">
	<hr>
	<div class="pull-left">
		<?php previous_posts_link('<span class="meta-nav">&laquo;</span> Prev'); ?>
		
	</div>
	<div class="pull-right">
		<?php next_posts_link('Next <span class="meta-nav">&raquo;</span>'); ?>
	</div>
	<div class="clear"></div>
	<hr>
</div><!-- #nav-below -->
<?php endif; ?>