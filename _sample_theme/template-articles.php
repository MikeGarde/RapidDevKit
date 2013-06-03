<?php
/* 
Template Name: Articles
*/
get_header();
?>
<div class="row">
	<div class="span8">
		<?php get_template_part('loop', 'articles'); ?>
	</div>

	<div class="span4">
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer(); ?>