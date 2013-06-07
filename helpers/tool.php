<?php
class Tool extends Helper {
	
	/*
	-------------------------------------------------------------------
	THE LOOP
	-------------------------------------------------------------------
	*/
	// pagination with page numbers
	function pagination($args = array(), $echo = true) {
		global $wp_query;
		
		$big = 999999999;
		$_args = array_merge(array(
			'base'         => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
			'format'       => '/page/%#%/',
			'total'        => $wp_query->max_num_pages,
			'current'      => max( 1, get_query_var('paged') ),
			'show_all'     => False,
			'end_size'     => 1,
			'mid_size'     => 2,
			'prev_next'    => True,
			'prev_text'    => __('&laquo;'),
			'next_text'    => __('&raquo;'),
			'type'         => 'plain',
			'add_args'     => False,
			'add_fragment' => ''
		), $args);
		
		$result = paginate_links($_args);
		
		if($echo) {
			echo $result;
		} else {
			return $result;
		}
	}
	/*
	-------------------------------------------------------------------
	END THE LOOP
	-------------------------------------------------------------------
	*/
	
	
	
	/*
	-------------------------------------------------------------------
	PAGES
	-------------------------------------------------------------------
	*/
	function subpage_list($post=null, $depth=0, $before='', $after='', $num=null) {
		if(!$post) {
			global $post;
		}
		$pid = $this->__page_find_parent($post, $num);
		return $this->__nav($pid, $depth, null, $before, $after);
	}

	
	function __page_find_parent($post=null, $num=null) {
		if(empty($post)) return;
		$parents = $this->__parse_parents($post, $num);
		return (isset($parents[0]) ? $parents[0] : null);
	}
	
	
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
	END PAGES
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
	
	
	// return referer
	function referer() {
		if($_SERVER['HTTP_REFERER']) {
			return $_SERVER['HTTP_REFERER'];
		}
		return false;
	}
	
	
	// check if referred from site
	function referer_chk() {
		$url = $this->site_url();
		$num = strlen($url);
		if(substr($_SERVER['HTTP_REFERER'], 0, $num) == $url) {
			return true;
		} else {
			return false;
		}

	}
	
	
	// redirect to url (must be called before headers are sent)
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
	----------------------------------------
	DATA MANIPULATION
	----------------------------------------
	*/	
	// return value in array if it isset
	function array_val($field=null, $array=array()) {
		if($field && $array) {
			$val = (isset($array[$field]) ? $array[$field] : null);
			return $val;
		}
	}
	
	
	// sort results by a comma separated list of ids
	// used to store ids and sorting in a single meta item
	function sort_by_list($results=array(), $list=false, $field='ID') {
		if(!$results || !$list) return $results;
		$_results = array();
		$_results_unlisted = array();
		
		$list = explode(',', $list);
		foreach($results as $result) {
			$k = array_search($result->$field, $list);
			if($k !== false) {
				$_results[$k] = $result;
			} else {
				$_results_unlisted[] = $result;
			}
		}
		
		ksort($_results);
		foreach($_results_unlisted as $item) $_results[] = $item;
		
		return $_results;
	}
	
	
	// deletes an item from a comma separated list
	// good for removing an id from a list of ids stored in a single meta item
	function del_from_list($list=false, $item=false) {
		if(!$list || !$item) return $list;
		$list = explode(',', $list);
		$key = array_search($item, $list);
		if($key !== false) {
			unset($list[$key]);
		}
		return implode(',', $list);
	}
	/*
	----------------------------------------
	END DATA MANIPULATION
	----------------------------------------
	*/
}