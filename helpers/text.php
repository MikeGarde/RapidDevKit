<?php
class Text extends Helper {
	/* text formatter for custom fields */
	function format($text, $echo=true) {
		$_text = apply_filters('the_content', $text);
		if($echo) echo $_text;
		else return $_text;
	}
}