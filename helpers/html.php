<?php
class Html extends Helper {
	/*
	-------------------------------------------------------------------
	EMBEDS
	-------------------------------------------------------------------
	*/
	//include css file
	function css($file='', $folder='css') {
		if(!$folder) $folder = '';
		if($file) {
			$url = get_bloginfo('template_url') . '/' . $folder . '/' . $file . '.css';
		} else {
			$url = get_bloginfo('stylesheet_url');
		}
		echo $this->__tag('link', array('rel' => 'stylesheet', 'href' => $url, 'type' => 'text/css', 'media' => 'screen'), true);
	}
	
	
	//include javascript file
	function js($file='', $folder='/js') {
		$file = $file . '.js';
		if($folder) $url = get_bloginfo('template_url') . $folder . '/' . $file;
		else $url = $file;

		echo $this->__tag('script', array('type' => 'text/javascript', 'src' => $url), true);
	}
	
	
	//add javascript
	function javascript($script=null, $echo = true) {
		$return = null;
		if($script) $return = $this->__tag('script', array('type' => 'text/javascript'), true, $script);
		if($echo) echo $return; else return $return;
	}
	
	
	//include favicon file
	function favicon($folder='/img') {
		$img = 'favicon.ico';
		if($folder) {
			$url = get_bloginfo('template_url') . $folder . '/' . $img;
		} else {
			$url = '/favicon.ico';
		}
		echo $this->__tag('link', array('rel' => 'shortcut icon', 'href' => $url, 'type' => 'image/x-icon'), true);
	}


	/*
	Displays an image
	*/
	function img($src=null, $w=null, $h=null, $options=array(), $echo=true) {
		if(is_numeric($src)) $src = wp_get_attachment_url($src);
		if($src) {
			if($w) $options['width'] = $w;
			if($h) $options['height'] = $h;
			if(!isset($options['src'])) $options['src'] = $src;
			$img = $this->__tag('img', $options, false, null, true);
			if($echo) echo $img; else return $img;
		} else {
			return false;
		}
	}
	
	
	/*
	Returns or echos an image from the template's img directory. If display is true, 
	prints the image. If display is false it returns the image's url.
	options ex: array('width' => 150, 'height' => 100, 'class' => 'img')
	add 'url' => url_link' to the options array to quickly make the image a link
	*/
	function img_tp($img=null, $w=null, $h=null, $options=array(), $display=true, $dir='/img', $echo = true) {
		if(strpos($img, '/') === false) $img = $dir . '/' . $img;
		$img = get_bloginfo('template_url') . $img;
		
		$link = null;
		if(isset($options['url'])) {
			$link = $options['url'];
			unset($options['url']);
		}
		$_echo = true;
		if(isset($options['echo'])) {
			$_echo = $options['echo'];
			unset($options['echo']);
		}
		
		$img = $this->img($img, $w, $h, $options, false);
		if($link) $img = $this->mk_link($link, $img, null, false);
		if(!$_echo) return $img;
		if($echo) echo $img; else return $img;
	}
	
	
	/*
	Returns the path to the template image.
	*/
	function img_tp_url($img=null, $dir='/img') {
		if(strpos($img, '/') === false) $img = $dir . '/' . $img;
		$img = get_bloginfo('template_url') . $img;
		return $img;
	}


	/*
	Returns a resized image
	*/
	function img_resize($src=null, $w=null, $h=null, $prefix='', $options=array(), $q=85, $echo=true, $return = false) {
		if(!$src) return;
		if(is_numeric($src)) $src = wp_get_attachment_url($src);
		
		$uploads = wp_upload_dir();
		$file_path = str_replace($uploads['baseurl'], $uploads['basedir'], $src);
		
		if(!$w || !$h){
			list($o_width, $o_height, $o_type, $o_attr) = getimagesize($file_path);

			if(!$w)	$w = round(($h / $o_height) * $o_width);
			else	$h = round(($w / $o_width) * $o_height);
		}

		$file_hash = md5(filesize($file_path) . basename($src));
		$file_ext  = pathinfo($src, PATHINFO_EXTENSION);
		$new_filename  = $file_hash .'_'.$w .'x'. $h .'_'. $q .'.'. $file_ext;
		$new_file_path = RDK_DIR_CACHE . $new_filename;

		if(preg_match("/^http(s)?:\/{2}/", $file_path)) {
			$cache_remote = $dir_cache . $file_hash .'.'. $file_ext;

			if(!file_exists($cache_remote))
				copy($file_path, $cache_remote);

			$file_path = $cache_remote;
			unset($cache_remote);
		}

		if(!file_exists($new_file_path)) {
			include_once(RDK_DIR_LIBS . 'GDImage.php');
			$img = new GDImage($file_path);
			$img->resize($w, $h);
			$img->save($new_file_path, $q);
		}
		
		global $wpdb;
		$query = "SELECT ID FROM $wpdb->posts wpost WHERE guid='$src'";
		$result = $wpdb->get_results($query);
		$add='';
		if($result) {
			$alt = get_post_meta($result[0]->ID, '_wp_attachment_image_alt', true);
			if($alt) $add=' alt="' . $alt . '"';
		}
		
		$src = str_replace(RDK_DIR_CACHE, RDK_URL_CACHE, $new_file_path);
		$_options = ($options ? img_resize_options($options) : '');
		
		$full_img = '<img src="' . $src . '" width="' . $w . '" height="' . $h . '" ' . $_options . $add . '/>';
		if($echo) echo $full_img;
		elseif($return) return $full_img;
		else return $src;
	}


	function file_url($id=null) {
		if($id && is_numeric($id)) return wp_get_attachment_url($id);
		else return false;
	}
	/*
	-------------------------------------------------------------------
	END EMBEDS
	-------------------------------------------------------------------
	*/

	
	
	/*
	-------------------------------------------------------------------
	LINKS & URLS
	-------------------------------------------------------------------
	*/
	/*
	makes a link from url, title and an array of options
	options ex: array('width' => 150, 'height' => 100, 'class' => 'img')
	*/
	function mk_link($url=null, $title=null, $options=array(), $echo = true) {
		if(!stristr($url, 'http://')) $url = get_site_url(). $url;
		if(!$options['href']) $options['href'] = $url;
		if($title) $_title = $title; else $_title = $url;
		$return = $this->__tag('a', $options, true, $_title);
		if($echo) echo $return; else return $return;
	}
	/*
	-------------------------------------------------------------------
	END LINKS & URLS
	-------------------------------------------------------------------
	*/
	
	
	
	/*
	-------------------------------------------------------------------
	FORMS
	-------------------------------------------------------------------
	*/
	function form($kind, $name=false, $value=false, $_options = array(), $echo=true) {
		$tag = '';
		$close = false;
		$_value = '';
		if(isset($_POST[$name])) $_value = $_POST[$name];
		
		if($kind == 'open' || $kind == 'open_file') {
			if(!$name) $name = $this->page_url();
			$options = array(
				'action' => $name,
				'method' => $value,
			);
			if($kind == 'open_file') $options['enctype'] = 'multipart/form-data';
			$tag = 'form';
		}
		
		if($kind == 'close') {
			if($echo) echo '</form>';
			else return '</form';
			return;
		}
		
		if($kind == 'select') {
			$content = '';
			if(isset($_options['empty'])) $content.= '<option value=""></option>';
			foreach($value as $k => $v) {
				$select = ($_value == $k ? 'selected' : '');
				$content.= '<option value="' . $k . '" ' . $select . '>' . $v . '</option>';
			}
			$options = array(
				'id' => $name,
				'name' => $name,
				'content' => $content
			);
			$tag = 'select';
			$close = true;
		}
		
		if($kind == 'hidden') {
			if(!$value) $value = $_value;
			$options = array(
				'id' => $name,
				'name' => $name,
				'value' => $value,
				'type' => 'hidden'
			);
			$tag = 'input';
		}
		
		if($kind == 'text') {
			if(!$value) $value = $_value;
			$options = array(
				'id' => $name,
				'name' => $name,
				'value' => $value,
				'type' => 'text'
			);
			$tag = 'input';
		}

		if($kind == 'password') {
			if(!$value) $value = $_value;
			$options = array(
				'id' => $name,
				'name' => $name,
				'value' => $value,
				'type' => 'password'
			);
			$tag = 'input';
		}
		
		if($kind == 'textarea') {
			if(!$value) $value = $_value;
			$options = array(
				'id' => $name,
				'name' => $name,
				'content' => $value
			);
			$tag = 'textarea';
			$close = true;
		}
		
		// need to implement checked and selected in __tag
		if($kind == 'radio') {
			$options = array(
				'name' => $name,
				'value' => $value,
				'type' => 'radio'
			);
			if(isset($_POST[$name])) if($_POST[$name] == $value) $options['addition'] = 'checked';
			$tag = 'input';
		}
		
		if($kind == 'checkbox') {
			$options = array(
				'name' => $name . '[]',
				'value' => $value,
				'type' => 'checkbox'
			);
			if(isset($_POST[$name])) if(in_array($value, $_POST[$name])) $options['addition'] = 'checked';
			$tag = 'input';
		}
		
		if($kind == 'file') {
			$options = array(
				'id' => $name,
				'name' => $name,
				'type' => 'file'
			);
			$tag = 'input';
		}
		
		if($kind == 'submit' || $kind == 'image') {
			$options = array(
				'id' => $name,
				'name' => $name,
				'value' => $value,
				'type' => 'submit'
			);
			if($kind == 'image') $options['type'] = 'image'; // src should be in $_options
			$tag = 'input';
		}
		
		if($kind == 'reset') {
			$options = array(
				'id' => $name,
				'type' => 'reset',
				'value' => 'value'
			);
		}

		if($kind == 'button') {
			$options = array(
				'id' => $name,
				'name' => $name,
				'content' => $value,
				'type' => 'submit'
			);
			$tag = 'button';
			$close = true;
		}
		
		$options = array_merge($options, $_options);
		$form = $this->__tag($tag, $options, $close);
		
		if($echo) echo $form;
		else return $form;
	}
	/*
	-------------------------------------------------------------------
	END FORMS
	-------------------------------------------------------------------
	*/
	
	
	function messages($form=null, $item=false) {
		if(!$form) return false;
		global $errors;
		global $messages;
		
		if(isset($errors[$form])) {
			$error = null;
			if($item) {
				if(isset($errors[$form][$item])) {
					$err = $errors[$form][$item];
				}
			} else {
				$err = implode('<br />', $errors[$form]);
			}
			if($err):
			?>
			<div class="alert alert-warning">
				<p><?php echo $err; ?></p>
			</div>
			<?php
			endif;
		}
		
		
		if(isset($messages[$form])) {
			$message = null;
			if($item) {
				if(isset($messages[$form][$item])) {
					$message = $messages[$form][$item];
				}
			} else {
				$message = implode('<br />', $messages[$form]);
			}
			if($message):
			?>
			<div class="alert alert-success">
				<p><?php echo $message; ?></p>
			</div>
			<?php
			endif;
		}
	}
}

if(!function_exists('img_resize_options')) {
	function img_resize_options($options=array()) {
		if(!is_array($options)) return;
		$_options = null;
		foreach($options as $k => $v) $_options.= " $k=\"$v\"";
		return $_options;
	}
}
