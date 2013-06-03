<?php
$rdk->set_post();
get_header();
?>

<div class="row">
	<div class="span8">
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>
	</div>

	<div class="span4">
		<?php get_sidebar(); ?>
	</div>
</div>

<?php get_footer(); ?>