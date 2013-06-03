<?php
class Helper {
	function __make_options($options=array()) {
		$_options = null;
		foreach($options as $k => $v) $_options.= " $k=\"$v\"";
		return $_options;
	}
	
	function __tag($tag=null, $options=array(), $close=false, $single=true, $content=null) {
		if($tag) {
			if(isset($options['content'])) {
				$content = $options['content'];
				unset($options['content']);
			}
			
			$_options = $this->__make_options($options);
			if($_options) $_options = ' ' . $_options;
			
			$build = '<' . $tag . $_options;
			if($close) {
				$build.= '>' . $content . '</' . $tag . '>';
			} elseif(!$close && $single) {
				$build.= ' />';
			} else {
				$build.= '>';
			}
			return $build;
		} else {
			return false;
		}
	}
	
	
	function page_url($get=true) {
		$pageURL = 'http';
		if(isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		if(!$get) {
			if($_GET) {
				$pageURL = str_replace('?'.$_SERVER['QUERY_STRING'], '', $pageURL);
			}
		}
		return $pageURL;
	}


	function build_get_url($gets=array(), $strip_key=false, $merge=true) {
		$_gets = $gets;
		if($gets && $_GET && $merge) $_gets = array_merge($_GET, $gets);
		if(!$gets) return $this->page_url();

		$url = $this->page_url() . '?';
		foreach($gets as $k => $get) {
			$add = $k . '=' . $get;
			if($strip_key != $k) $url.= $add . '&';
		}
		$url = substr($url, 0, -1);
		return $url;
	}
}