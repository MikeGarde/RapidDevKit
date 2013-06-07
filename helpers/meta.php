<?php
class Meta extends Helper {
	
	// get meta for provided id or global post
	function get($id=null) {
		if(!$id) {
			global $post;
			$id = $post->ID;
		}
		return get_post_custom($id);
	}
	
	/*
	Parse provided meta.
	key: look for all items of a given key
	mult: return all values of key or just the first
	_return: a variable to return if nothing is found
	*/
	function parse($array=array(), $key=null, $mult = true, $_return = false) {
		if(!$array || !$key) return false;
		if($mult) {
			$return = (isset($array[$key][0]) ? $array[$key][0] : $_return);
		} else {
			$return = (isset($array[$key]) ? $array[$key] : $_return);
		}
		if($return) return $return; else return $_return;
	}
}