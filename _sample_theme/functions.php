<?php
/*
----------------------------------------
THEME FUNCTIONS
----------------------------------------
*/
/*
----------------------------------------
END THEME FUNCTIONS
----------------------------------------
*/



/*
----------------------------------------
THEME VARIABLES
----------------------------------------
*/
define('THEME_CSS', get_bloginfo('template_url') . '/css');
define('THEME_JS', get_bloginfo('template_url') . '/js');
/*
----------------------------------------
END THEME VARIABLES
----------------------------------------
*/


/*
----------------------------------------
NAVIGATION & SIDEBARS
----------------------------------------
*/
// REGISTER MENUS
register_nav_menus(array(
	'topnav'	=> 'Top Nav',
	'lowernav'	=> 'Lower Nav'
));


// REGISTER SIDEBARS
function app_register_sidebars() {	
	register_sidebar( array(
		'name' => __( 'Homepage Sidebar Left', 'app' ),
		'id' => 'homepage-1',
		'description' => __( 'An optional widget area one for your homepage', 'app' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Homepage Sidebar Right', 'app' ),
		'id' => 'homepage-2',
		'description' => __( 'An optional widget area one for your homepage', 'app' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Sidebar', 'app' ),
		'id' => 'sidebar-generic',
		'description' => __( 'An optional widget for your sidebar', 'app' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Blog Sidebar', 'app' ),
		'id' => 'sidebar-blog',
		'description' => __( 'An optional widget area one for your blog sidebar', 'app' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Article Sidebar', 'app' ),
		'id' => 'sidebar-article',
		'description' => __( 'An optional widget area one for your blog sidebar', 'app' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'app_register_sidebars' );
/*
----------------------------------------
END NAVIGATION & SIDEBARS
----------------------------------------
*/



/*
----------------------------------------
SCRIPTS & STYLES
----------------------------------------
*/
// add those front end css and js scripts
function frontend_scripts_styles() {
	//css
	wp_enqueue_style('bootstrap', THEME_CSS . '/bootstrap.css');
	
	//js
	wp_enqueue_script('jquery');
	wp_enqueue_script('bootstrap', THEME_JS . '/bootstrap.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'frontend_scripts_styles');


// adds scripts to admin header
function admin_scripts_styles() {
	//css
	wp_enqueue_style('admin_css', THEME_CSS . '/admin.css');
	
	//js
	//wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'admin_scripts_styles');
/*
----------------------------------------
SCRIPTS & STYLES
----------------------------------------
*/





/*
----------------------------------------
SHORT CODES
----------------------------------------
*/
// add your short codes here.
function my_shortcode() {
	//add your short code info here.
}
//add_shortcode('my_shortcode', 'my_shortcode');
/*
----------------------------------------
END SHORT CODES
----------------------------------------
*/