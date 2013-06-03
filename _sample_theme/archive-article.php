<?php
get_header();

if(is_day()) {
	$title = 'Daily Archives: <span>' . get_the_date() . '</span>';
} elseif (is_month()) {
	$title = 'Monthly Archives: <span>' . get_the_date('F Y') . '</span>';
} elseif (is_year()) {
	$title = 'Yearly Archives: <span>' . get_the_date('Y') . '</span>';
} else {
	$title = 'Article Archives';
}
?>

<div class="row">
	<div class="span8">
		<?php if(have_posts()): ?>
		
		<h1><?php echo $title; ?></h1>
		<?php
		rewind_posts();
		get_template_part( 'loop', 'archive' );
		?>
		
		<?php else: ?>
			
		<h1>Nothing Found</12>
		<p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>		
		
		<?php endif; ?>
	</div>

	<div class="span4">
		<?php get_sidebar('article'); ?>
	</div>
</div>

<?php get_footer(); ?>