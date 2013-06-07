<?php
class Option extends Helper {
	// return option
	function get($option) {
		return get_option($option);
	}
	
	
	// save an option by updating if exists or adding if it doesn't
	function save($name=null, $val=null, $auto='no') {
		if(!$name) return false;
		if(get_option($name) != $val) {
			return update_option($name, $val);
		} else {
			return add_option($name, $val, ' ', $auto);
		}
	}
	
	
	// option delete. same as delete_option but adds a conditional delete
	// if val == option value then delete
	function delete($name=null, $val=null) {
		if(!$name) return false;
		$return = false;
		if($val) {
			if(get_option($name) == $val) $return = delete_option($name);
		} else {
			$return = delete_option($name);
		}
		return $return;
	}
	
	
	function sort($name=null, $array=null) {
		if(!$name || !$array) return false;
		$results = get_option($name);
		if(is_array($results)) {
			$results['order'] = $array;
			$this->save($name, $results);
		}
	}
	
	
	function sort_save($name=null, $val=null) {
		if(!$name || !$val) return false;
		$array = get_option($name);
		if($array) {
			$num = count($array['order']);
			$array['items'][] = $val;
			$array['order'][] = $num;
		} else {
			$array['items'][] = $val;
			$array['order'][] = 0;
		}
		$this->save($name, $array);
	}
	
	
	function sort_del($name, $key) {
		if(!$name) return false;
		if(!is_numeric($key)) return false;
		$array = get_option($name);
		if(isset($array['items'][$key])) {
			unset($array['items'][$key]);
			$_key = array_search($key, $array['order']);
			unset($array['order'][$_key]);
			$i = 0;
			foreach($array['order'] as $k => $o) {
				$array['order'][$k] = $i;
				$item = $array['items'][$o];
				unset($array['items'][$o]);
				$array['items'][$i] = $item;
				$i++;
			}
			$this->save($name, $array);
		}
	}
	
	
	// if option results are an array merge that array with new array
	// if it is not an array and is empty, save new array as option value
	function merge_array($name=null, $add_array=array()) {
		if(!$name && !$add_array) return false;
		$array = get_option($name);
		if(is_array($array)) {
			$_array = array_merge($array, $add_array);
			return $this->save($name, $_array);
		} else {
			if(!$array) {
				return $this->save($name, $add_array);
			}
		}
		return false;
	}
	
	
	// remove an item from from an options stored array by matching a key
	// or by matching values
	function remove_array_val($name=null, $field=null, $val=null) {
		if(!$name) return false;
		if(!$field || !$val) return false;
		
		$array = get_option($name);
		if(is_array($array)) {
			if($field) if(isset($array[$field])) unset($array[$field]);
			if($val) foreach($array as $k => $v) if($v == $val) unset($array[$k]);
			$this->save($name, $array);
			return true;
		}
		return false;
	}
}
