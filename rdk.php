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
	
	
	
	
	/*
	-------------------------------------------------------------------
	BLOG INFO
	-------------------------------------------------------------------
	*/
	function tag() {
		if(!$this->tag) $this->tag = get_bloginfo('description');
		return $this->tag;
	}

	
	function site_url() {
		if(!$this->site_url) $this->site_url = get_bloginfo('siteurl');
		return $this->site_url;
	}
	/*
	-------------------------------------------------------------------
	END BLOG INFO
	-------------------------------------------------------------------
	*/
	
	
	
	/*
	-------------------------------------------------------------------
	PAGES
	-------------------------------------------------------------------
	*/
	/*
	Setup post data, set readmore value and return custom fields
	*/
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
	
	
	function subpage_list($post=null, $depth=0, $before='', $after='', $num=null) {
		if(!$post) {
			global $post;
		}
		$pid = $this->page_find_parent($post, $num);
		return $this->__nav($pid, $depth, null, $before, $after);
	}

	
	function page_find_parent($post=null, $num=null) {
		if(empty($post)) return;
		$parents = $this->__parse_parents($post, $num);
		return (isset($parents[0]) ? $parents[0] : null);
	}
	/*
	-------------------------------------------------------------------
	END PAGES
	-------------------------------------------------------------------
	*/
	
	
	
	
	
	/*
	-------------------------------------------------------------------
	CATEGORIES
	-------------------------------------------------------------------
	cat_posts($id=null, $num=10, $order='DESC', $orderby='post_date')
	category_latest($slug=null, $num=5, $child=false)
	category_list($id=null, $echo = false, $args = array())
	*/
	/*
	get a category's posts by an array or string of ids
	or by category slug
	*/
	function category_posts($id=null, $num=10, $order='DESC', $orderby='post_date') {
		//find if slug or ids
		$key = '';
		if(is_numeric($id) || is_array($id) || strstr($id, ',')) $key = 'category'; else $key = 'category_name';
		
		//make get_post call
		$post = get_posts(array(
			$key			=> $id,
			'post_type'		=> 'post',
			'numberposts'	=> $num,
			'order'			=> $order,
			'orderby'		=> $orderby
		));
		return $post;
	}


	/*
	uses a category slug to gets last posts of the category. if $child is set to true,
	results will include the category's sub-categories
	*/
	function category_latest($slug=null, $num=5, $child=false) {
		if(!$slug) return false;
		global $wpdb;

		$category  = $wpdb->get_results("SELECT * FROM {$wpdb->terms} wterms WHERE wterms.slug='" . $slug . "'", OBJECT);
		$cat = null;

		$children = array();

		if(isset($category[0]->term_id)) {
			$cat = $category[0]->term_id;
			if($cat && $child) {
				$child = get_categories('child_of='.$cat);
				if($child) {
					foreach($child as $c) {
						$children[] = $c->term_id;
					}
				}
			}
		}

		if($children) {
			$children = implode("','", $children);
			$find = "IN ('$cat','$children')";
		} else {
			$find = " = $cat";
		}

		$querystr = "
			SELECT DISTINCT wposts.*
			FROM {$wpdb->posts} wposts, {$wpdb->term_relationships} wpostcats
			WHERE wposts.ID = wpostcats.object_id 
			AND wpostcats.term_taxonomy_id $find
			AND wposts.post_status = 'publish'
			AND wposts.post_type = 'post' 
			ORDER BY wposts.post_date desc
			LIMIT $num
		";

		$results = $wpdb->get_results($querystr, OBJECT);
		return $results;
	}


	/*
	list all categories or list all subcategories by slug/id
	*/
	function category_list($id=null, $echo = false, $args = array()) {
		if($id) {
			if(is_numeric($id)) {
				$child = $id;
			} else {
				$cat = get_category_by_slug($id);
				$child = $cat->term_id;
			}
		} else {
			$child = null;
		}

		$arguments = array_merge(
			array(
				'title_li'	=> null,
				'child_of'	=> $child,
				'echo'		=> $echo,
			),
			$args
		);
		return wp_list_categories($arguments);
	}
	/*
	-------------------------------------------------------------------
	END CATEGORIES
	-------------------------------------------------------------------
	*/
	
	
	
	
	
	/*
	-------------------------------------------------------------------
	TIME SAVERS
	-------------------------------------------------------------------
	*/
	function session() {
		session_start();
	}
	
	
	function now($format="Y-m-d H:i:s") {
		return date("Y-m-d H:i:s");
	}
	
	function meta_return($array=array(), $field=null, $value=true, $_return = null, $mult=true) {
		if(!$array || !$field) return false;
		if($mult) {
			$return = (isset($array[$field][0]) ? $array[$field][0] : $_return);
		} else {
			$return = (isset($array[$field]) ? $array[$field] : $_return);
		}
		if($return) return $return; else return $_return;
	}
	function meta_ret($array=array(), $field=null, $value=true, $_return = null, $mult=true) {
		return $this->meta_return($array, $field, $value, $_return, $mult);
	}
	
	
	/* get random result from an array */
	function random($array = null, $num=1) {
		if(is_array($array)) {
			$found=array();
			$i=0;
			for($i=0;$i<$num;$i++) {
				$count = count($array);
				$rand = rand(0, $count-1);
				$found[$i] = $array[$rand];
				unset($array[$rand]);
			}
			if($num === 1 && $found) return $found[0];
			if($num>1) return $found;
		} else { 
			return false;
		}
	}
	
	
	function pagination($prev='&laquo; Previous', $next='Next &raquo;', $gets=true, $normal=true, $type='list', $echo=false) {
		global $wp_query;
		global $__app_paged;
		$page_var = ($__app_paged ? $__app_paged : 'paged');
		$page = $wp_query->query_vars[$page_var];
		$big = 999999999;
		if($normal) {
			$return = paginate_links( array(
				'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
				'format' => '/page/%#%/',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $wp_query->max_num_pages,
				'type' => $type,
				'prev_text' => $prev,
				'next_text' => $next
			) );
		} else {
			$gets = $_GET;
			$string = '';
			if($gets) {
				foreach($gets as $k => $v) {
					if($k!=='page') $string.= $k . '=' . $v . '&';
				}
				if($string) $string = '?' + $string;
			}
			$return = paginate_links( array(
				'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
				'format' => '/page/%#%/',
				'current' => max( 1, $page ),
				'total' => $wp_query->max_num_pages,
				'type' => $type,
				'prev_text' => $prev,
				'next_text' => $next
			) );
		}
		if($echo) echo $return; else return $return;
	}
	
	
	function referer_chk() {
		$url = $this->site_url();
		$num = strlen($url);
		if(substr($_SERVER['HTTP_REFERER'], 0, $num) == $url) {
			return true;
		} else {
			return false;
		}

	}
	
	
	function redirect($url=null) {
		if(!$url) return;
		header('Location: ' . $url);
	}
	/*
	-------------------------------------------------------------------
	END TIME SAVERS
	-------------------------------------------------------------------
	*/
	
	
	
	
	/*
	-------------------------------------------------------------------
	WORKER BEES
	-------------------------------------------------------------------
	*/
	/*
	parses a posts ancestors splitting it at the needle.
	ex: array(2, 4, 6, 8, 10) and needle = 6, returns array(10, 8, 6)*
	*order depends on "reverse" value
	*/
	function __parse_parents($post=null, $needle = null, $reverse = true) {
		$key = null;
		if($post && isset($post->ancestors) && $post->ancestors) {
			$ancestors = $post->ancestors;
			$total = count($ancestors);
			if($reverse) {
				$ancestors = array_reverse($ancestors);
				$ancestors[] = $post->ID;
			}
			if($needle) {
				$key = array_search($needle, $ancestors) + 1;
				if($key) $ancestors = array_slice($ancestors, $key, $total);
			}
			return $ancestors;
		} else {
			$ancestors = array_reverse($this->__get_parents($post, $needle));
			return $ancestors;
		}
	}


	/*
	get an array of ids of page parents. returns a list similar to "ancestors".
	if keep_needle is true, it will attach it to the array of ids
	*/
	function __get_parents($post = null, $needle=null, $keep_needle = true, $results = array(), $loop = true) {
		global $wpdb;
		if($post) {
			$results[] = $post->ID;
			$result = $wpdb->get_results("SELECT wpost.ID, wpost.post_parent FROM {$wpdb->posts} wpost WHERE wpost.ID={$post->post_parent} ORDER BY wpost.menu_order asc");
			if(isset($result[0]->ID) && $loop) {
				if($needle) if($result[0]->post_parent == $needle) $loop = false;
				if(!$result[0]->post_parent) $loop = false;
				$results = $this->__get_parents($result[0], $needle, $keep_needle, $results, $loop);
			}
			if(!$loop) if($keep_needle && ($post->post_parent == $needle)) $results[] = $needle;
		}
		return $results;
	}


	/*
	returns a quick navigation include, depth, merging an argument array, before/after
	*/
	function __nav($include=null, $depth=0, $add=null, $before='', $after='') {
		if(!is_array($add)) $add = array();
		
		$args = array_merge(array(
			'child_of'		=> $include,
			'depth'			=> $depth,
			'title_li'		=> '',
			'link_before'	=> $before,
			'link_after'	=> $after,
			'echo'			=> false,
		), $add);
		
		$nav = wp_list_pages($args);
		
		return $nav;
	}
	/*
	-------------------------------------------------------------------
	END WORKER BEES
	-------------------------------------------------------------------
	*/
}


// Instance RDK with models list
global $rdk;
$rdk = new RDK($load_classes);

add_action('init', array(&$rdk, '__load_all_classes'));


} /* if RDK doesn't exist */