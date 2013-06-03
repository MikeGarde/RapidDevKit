<?php
/*
The template for displaying Comments.
*/
?>


<div id="comments">
	<?php if(post_password_required()): ?>
	<p class="nopassword">This post is password protected. Enter the password to view any comments.</p>
	<?php return; endif; ?>
	
	
	<?php if(have_comments()): ?>
	
	
	<?php 
	$num = get_comments_number();
	if($num == 1) $title = '1 Response to <em>' . get_the_title() . '</em>';
	else $title = get_comments_number() . ' Responses to <span>' . get_the_title() . '</span>';
	?>
	<h3 id="comments-title"><?php echo $title; ?></h3>

	<?php if(get_comment_pages_count() > 1): ?>
	<div class="navigation">
		<div class="nav-previous"><?php previous_comments_link('<span class="meta-nav">&larr;</span> Older Comments'); ?></div>
		<div class="nav-next"><?php next_comments_link('Newer Comments <span class="meta-nav">&rarr;</span>'); ?></div>
	</div>
	<!-- .navigation -->
	<?php endif; ?>

	<ol class="commentlist"><?php wp_list_comments(); ?></ol>

	<?php if(get_comment_pages_count() > 1): ?>
	<div class="navigation">
		<div class="nav-previous"><?php previous_comments_link('<span class="meta-nav">&larr;</span> Older Comments'); ?></div>
		<div class="nav-next"><?php next_comments_link('Newer Comments <span class="meta-nav">&rarr;</span>'); ?></div>
	</div>
	<!-- .navigation -->
	<?php endif; ?>
	
	<?php else: //else no comments ?>
	
	<?php if(!comments_open()): ?>
	<p class="nocomments">Comments are closed.</p>
	<?php endif; ?>
	
	
	<?php endif; //end if comments ?>


	<?php comment_form(); ?>
</div>
<!-- #comments -->