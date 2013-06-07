<?php
/*
Plugin Name: Rapid Dev Kit
Plugin URI: http://www.iamparagon.com/
Description: Paragon's Rapid Development Kit
Author: Philip Joyner
Version: 0.1
Author URI: http://philipjoyner.com/
Updated: 2010-11-18
*/

if(!class_exists("RDK")) {
	
	
// INCLUDES
// required and classes from config file
$required_classes = array(
	'models' => array('page', 'post'),
	'helpers' => array('html', 'text', 'tool')
);
include_once('config.php');


// widgets
include_once('widgets/subpage_list.php');
include_once('widgets/blockquote.php');


// load base classes
include('libs/model.php');
include('libs/helper.php');



// RDK Class
class RDK {
	var $_classes = array();
	
	var $dir_base = null;
	var $dir_models = 'models/';
	var $dir_helpers = 'helpers/';
	var $dir_cache = 'cache/';
	var $dir_libs = 'libs/';
	
	var $url_base = null;
	
	
	// initiate class
	function __construct($classes) {
		// save models
		$this->_classes = $classes;
		
		// dir and url base paths
		$this->dir_base = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
		$this->url_base = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
		
		define('RDK_DIR_MODELS', $this->dir_base . $this->dir_models);
		define('RDK_DIR_HELPERS', $this->dir_base . $this->dir_helpers);
		define('RDK_DIR_CACHE', $this->dir_base . $this->dir_cache);
		define('RDK_DIR_LIBS', $this->dir_base . $this->dir_libs);
		
		define('RDK_URL_CACHE', $this->url_base . 'cache/');
	}
	
	
	
	
	// load all classes and make instances
	function __load_all_classes() {
		$this->__load_classes($this->_classes, $this->dir_base);
	}
	
	
	// load classes
	function __load_classes($classes, $path=null, $create=true) {
		if(!$classes || !$path) return false;
		
		if($classes) {
			foreach($classes as $type => $type_array) {
				foreach($type_array as $class) {
					$filename = strtolower($class . '.php');
					$path_file = $path . $type . '/' . $filename;
					$class_name = ucfirst($class);
					
					if(!class_exists($class_name)) {
						if(is_file($path_file)) {
							include($path_file);
						}
					}
					
					if($create) {
						global $$class_name;
						$this->$class_name = new $class_name();
						if(is_admin() && method_exists($class_name, 'db_check')) $this->$class_name->db_check();
					}
				}
			}
		}
	}
	
	
	
	
	// Setup post data, set readmore value and return custom fields
	function set_post($_post=false, $custom=true, $readmore=false) {
		global $more;
		global $post;
		if(!$_post) {
			$_post = $post;
			$readmore = false;
			$custom = true;
		}
		
		$post = $_post;
		setup_postdata($post);
		$more = ($readmore ? true : false);
		if($custom) {
			$post->meta = get_post_custom($post->ID);
		}
	}
	/*
	-------------------------------------------------------------------
	END PAGES
	-------------------------------------------------------------------
	*/
}


// merge required and requested classes
$_load_classes = array_merge_recursive($required_classes, $load_classes);


// Instance RDK with models list
global $rdk;
$rdk = new RDK($_load_classes);

add_action('init', array(&$rdk, '__load_all_classes'));


} /* if RDK doesn't exist */