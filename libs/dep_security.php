<?php
class Security {
	// creation of nonce hash
	function nonce($file=null) {
		if($file) $file = basename($file);
		else $file = basename(__FILE__);
		return wp_create_nonce($file); // create nonce based on this filename
	}
	
	
	// check nonce hash is valid and user is allowed
	function nonce_chk($nonce=false, $file=null, $option='edit_posts') {
		if(!$nonce) return false;
		
		$allow = false;
		if($file) $file = basename($file);
		else $file = basename(__FILE__);
		
		if(wp_verify_nonce($nonce, $file)) $allow = true; // check nonce value against this file name
		if($option) if(!current_user_can($option)) $allow = false;
		
		return $allow;
	}
}
