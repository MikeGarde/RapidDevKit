<?php
/* 
Template Name: Home Page
*/
$rdk->set_post();
$slides = $rdk->Slide->get_all();

get_header();
?>



<?php if($slides): ?>
<div class="row">
	<div class="span12">
		<div id="slideshow_home" class="carousel">
			<div class="carousel-inner">
				<?php
				$first = true;
				foreach($slides as $slide):
					$img = ($slide->meta['image'][0] ? $slide->meta['image'][0] : false);
					$link = ($slide->meta['link'][0] ? $slide->meta['link'][0] : false);
					if($img):
				?>
				<div class="<?php if($first) echo 'active '; ?>item">
					<?php if($link): ?><a href="<?php echo $link; ?>"><?php endif; ?>
					<?php $rdk->Html->img_resize($img, 1200, 300); ?>
					<?php if($link): ?></a><?php endif; ?>
						
					<div class="carousel-caption">
						<h4><?php echo $slide->post_title; ?></h4>
						<?php $rdk->Text->format($slide->post_content); ?>
					</div>
				</div>
				<?php
						$first = false;
					endif;
				endforeach;
				?>
			</div><!-- .carousel-inner -->
			
			<a class="carousel-control left" href="#slideshow_home" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#slideshow_home" data-slide="next">&rsaquo;</a>
		</div><!-- #slideshow_home.carousel -->
	</div><!-- .span12 -->
</div><!-- .row -->
<?php endif; ?>



<div class="row">
	<div class="span6">
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>
	</div><!-- .span6 -->
	
	<div class="span3">
		<?php dynamic_sidebar( 'homepage-1' ); ?>
	</div><!-- .span3 -->
	
	<div class="span3">
		<?php dynamic_sidebar( 'homepage-2' ); ?>
	</div><!-- .span3 -->
</div><!-- .row -->


<?php get_footer(); ?>