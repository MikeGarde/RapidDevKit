<?php
class Model {
	var $post_type;
	var $options;
	var $db_option;
	var $db_version;
	var $order_by = 'post_date';
	var $order = 'desc';
	var $errors = array();
	
	
	/*
	----------------------------------------
	POST QUERIES
	----------------------------------------
	*/
	// basic query function
	function query($query = false, $nest = false, $field = 'post_parent') {
		global $wpdb;
		if(!$query) return false;
		
		if(stristr($query, 'SELECT')) {
			$results = $wpdb->get_results($query);
			if($results) {
				if($nest) return $this->nest($results, $field);
				else return $results;
			} else {
				return array();
			}
		} else {
			return $wpdb->query($query);
		}
	}
	
	
	// nests results by provided field
	function nest($array = false, $field='post_parent') {
		if(!$array || !$field) return;
		$_array = array();
		$_array_children = array();
		
		// separate children from parents
		foreach($array as $a) {
			if(!$a->post_parent) {
				$_array[] = $a;
			} else {
				$_array_children[$a->post_parent][] = $a;
			}
		}
		
		// nest children and parents
		foreach($_array as $a) {
			$a->children = array();
			if(isset($_array_children[$a->ID])) $a->children = $_array_children[$a->ID];
		}
		return $_array;
	}
	
	
	// get field from a post
	function get_field($id=false, $field=false) {
		if(!$id && !$field) return false;
		global $wpdb;
		$query = "SELECT wposts.$field FROM $wpdb->posts wposts WHERE wposts.ID=$id";
		$results = $this->query($query);
		if(isset($results[0])) return $results[0]->$field;
	}
	
	
	function single($slug=null, $fields='*', $post_type=null) {
		global $wpdb;
		if(!$post_type) $post_type = $this->post_type;
		$_and = (is_numeric($slug) ? 'ID='.$slug : "post_name='$slug'");
		$result = $wpdb->get_results("
			SELECT $fields FROM {$wpdb->posts} wpost
			WHERE wpost.post_type='$post_type'
			AND wpost.post_status='publish'
			AND wpost.{$_and}
		");
		if($result) {
			if(strtolower($fields) == 'id') return $result[0]->ID;
			else return $result[0];
		}
	}
	
	
	// paginate results
	// args get merged into all arguments
	function pagination($_args=array(), $custom=false) {
		if($custom) {
			global $__app_paged;
			$__app_paged = $custom;
			$paged = $custom;
		} else {
			$paged = 'paged';
		}
		
		$page = ( get_query_var( $paged ) ) ? get_query_var( $paged ) : 1;
		$args = array_merge(array(
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'paged' => $page,
			'orderby' => $this->order_by,
			'order' => $this->order
		), $_args);
		
		global $___wp_query;
		$___wp_query = new WP_Query($args);
		
		add_action('wp_head', array($this, '__paginate_action'));
	}
	
	
	function __paginate_action() {
		global $___wp_query;
		if($___wp_query) {
			global $wp_query;
			global $_wp_query;
			$_wp_query = $wp_query;
			$wp_query = $___wp_query;
		}
	}
	/*
	----------------------------------------
	END POST QUERIES
	----------------------------------------
	*/
	
	
	
	/*
	----------------------------------------
	COMMON POST QUERIES
	----------------------------------------
	*/
	// return all posts
	function all($num=-1, $category=null, $tax='tax_name') {
		return get_posts(array(
			'numberposts'	=> $num,
			$tax			=> $category,
			'post_type'		=> $this->post_type,
			'orderby'		=> $this->order_by,
			'order'			=> $this->order
		));
	}
	
	
	// return recent results
	function recent($num=5, $category=null, $tax='tax_name') {
		return get_posts(array(
			'numberposts'	=> $num,
			$tax			=> $category,
			'post_type'		=> $this->post_type,
			'orderby'		=> $this->order_by,
			'order'			=> $this->order
		));
	}


	// get meta of results
	function get_meta($items) {
		if($items) {
			if(is_array($items)) {
				foreach($items as $item) {
					$item->meta = get_post_custom($item->ID);
				}
			} else {
				$items->meta = get_post_custom($items->ID);
			}
		}
		return $items;
	}
	/*
	----------------------------------------
	END COMMON POST QUERIES
	----------------------------------------
	*/
	
	
	
	/*
	----------------------------------------
	CRUD NON POSTS
	----------------------------------------
	*/
	function return_table($table) {
		if(!$table) {
			$array_keys = array_keys($this->table_schema);
			$table = $array_keys[0];
		}
		if($table) {
			global $wpdb;
			return $wpdb->prefix . $table;
		}
		return false;
	}
	
	
	function return_first_table() {
		reset($this->table_schema);
		return key($this->table_schema);
	}
	
	
	function save($_post=array(), $table=false) {
		$table = ($table ? $table : $this->return_first_table());
 		if(!$_post || !is_array($_post) || !$table || !$this->table_schema[$table]) return false;
		if(isset($_post[$table])) $post = $_post[$table];
		else $post = $_post;
		
		$table_schema = $this->table_schema[$table];
		$table = $this->return_table($table);
		
		$_fields = array_keys($table_schema);
		$save = array();
		
		$insert = true;
		if(isset($_post[$_fields[0]]) && $_post[$_fields[0]]) $insert = false;		
		
		foreach($_fields as $f) {
			if(isset($post[$f])) {
				if(isset($table_schema[$f][1])) {
					if(!$this->__validate($post[$f], $table_schema[$f][1])) {
						if(isset($table_schema[$f][2])) {
							$this->errors[$f] = $table_schema[$f][2];
						}
					} else {
						$save[$f] = $post[$f];
					}
				} else {
					$save[$f] = $post[$f];
				}
			} else {
				if(isset($table_schema[$f][2]) && $insert) {
					$this->errors[$f] = $table_schema[$f][2];
				}
			}
		}
		
		if(!$this->errors && $save) {
			global $wpdb;
			if(isset($save[$_fields[0]])) {
				return $wpdb->update($table, $save, array($_fields[0] => $save[$_fields[0]]));
			} else {
				if($wpdb->insert($table, $save)) {
					return $wpdb->insert_id;
				}
			}
			return $return;
		}
	}


	function save_field($id, $field, $value, $id_field='id', $table=null) {
		$table = ($table ? $table : $this->return_first_table());
		if(!$table || !$id || !$field || !$value || !$id_field) return false;
		$table = $this->return_table($table);
		global $wpdb;
		return $wpdb->update($table, array($field => $value), array($id_field => $id));
	}



	function read($id = null, $field='id', $table=null) {
		$table = ($table ? $table : $this->return_first_table());
		if($id && $field && $table) {
			if(!is_numeric($id)) $id = "'$id'";
			return $this->find('single', array('conditions' => array($field.'='.$id)), $table);
		}
	}
	
	
	function del($val=null, $field='id', $limit=1, $table=null) {
		$table = ($table ? $table : $this->return_first_table());
		if(!$table || !$val || !$field) return false;
		$table = $this->return_table($table);
		if(!is_numeric($val)) $val = "'$val'";
		if($limit) $limit = 'LIMIT ' . $limit;
		echo "DELETE FROM $table WHERE $table.$field=$val $limit";
		return $this->query("DELETE FROM $table WHERE $table.$field=$val $limit");
	}
	
	
	function find($amount='all', $elements = array(), $table=null) {
		$table = ($table ? $table : $this->return_first_table());
		$table = $this->return_table($table);
		
		$_elements = array_merge(array(
			'fields'		=> '*',
			'conditions'	=> array(),
			'order'			=> 'id DESC',
			'limit'			=> false,
		), $elements);
		
		$_conditions = $this->__conditions($_elements['conditions']);
		$_order = ($_elements['order'] ? "ORDER BY {$_elements['order']}" : '');
		$_limit = ($_elements['limit'] ? "LIMIT {$_elements['limit']}" : $this->__limit($amount));
		
		$sql = "SELECT {$_elements['fields']} FROM {$table} $_conditions $_order $_limit";
		$results = $this->__query($sql, $amount);
		return $results;
	}
	
	
	function sort_items($array, $id='id', $field='ord', $table=null) {
		$table = ($table ? $table : $this->return_first_table());
		$table = $this->return_table($table);
		foreach($array as $k => $v) {
			$this->query("UPDATE $table SET $field=$k WHERE $id=$v");
		}
	}
	
	
	function __conditions($conditions=array()) {
		if(!$conditions) return;
		$return = '';
		if(is_array($conditions)) {
			$return = 'WHERE ' ;
			$num = count($conditions);
			$i=0;
			foreach($conditions as $ck => $condition) {
				$i++;
				$return.= $condition;
				if($i !== $num) $return.= ' AND ';
			}
		} else {
			$return = "WHERE $conditions";
		}
		return $return;
	}
	
	function __limit($amount='') {
		$limit = ($amount === 'first' ? 'LIMIT 1' : '');
		return $limit;
	}
	
	function __query($sql=false, $amount='all') {
		if(!$sql) return false;
		global $wpdb;
		
		if(strtolower(substr($sql, 0, 6)) == 'select') {
			$results = $wpdb->get_results($sql);
			if($results) {
				if($amount !== 'all') return $results[0];
				else return $results;
			} else {
				return array();
			}
		} else {
			$return = $wpdb->query($sql);
			return $return;
		}	
	}
	/*
	----------------------------------------
	END CRUD NON POSTS
	----------------------------------------
	*/
	
	
	
	/*
	----------------------------------------
	VALIDATION
	----------------------------------------
	*/
	function __validate($val=false, $type=false) {
		if(!$val || !$type) return false;
		$val = trim($val);
		switch($type) {
			case 'numeric':
				if(is_numeric($val)) return true;
				break;
			case 'exists':
				if($val && $val != '' && $val !== '') return true;
				break;
			case 'email':
				if(!ereg("^[^@]{1,64}@[^@]{1,255}$", $val)) return false;
				if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $val)) return false;
				return true;
				break;
			case 'password':
				if(preg_match("/^[a-zA-Z0-9_-]{8,20}$/i", $val)) return true;
				break;
			case 'bool':
				if($val === true || $val === false) return true;
				break;
		}
		return false;
	}
	/*
	----------------------------------------
	END VALIDATION
	----------------------------------------
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
	
	
	
	/*
	----------------------------------------
	CUSTOM FIELDS
	----------------------------------------
	*/
	// get all custom fields and assign to an array
	// if field does not exist, create element so a if(array[field]) does not fail
	function merge_custom_fields($id=false, $fields = array(), $single = true) {
		if(!$fields || !$id) return;
		$custom = get_post_custom($id);
		if(!$custom) return;
		
		$return = array();
		
		foreach($fields as $field) {
			if(isset($custom[$field])) {
				$return[$field] = $custom[$field];
				if($single) {
					$return[$field] = $custom[$field][0];
				}
			} else {
				$return[$field] = null;
			}
		}
		return $return;
	}
	/*
	----------------------------------------
	END CUSTOM FIELDS
	----------------------------------------
	*/
	
	
	
	/*
	----------------------------------------
	OPTIONS
	----------------------------------------
	*/
	// return option
	function option($option) {
		return get_option($option);
	}
	
	
	// save an option by updating if exists or adding if it doesn't
	function option_save($name=null, $val=null, $auto='no') {
		if(!$name) return false;
		if(get_option($name) != $val) {
			return update_option($name, $val);
		} else {
			return add_option($name, $val, ' ', $auto);
		}
	}
	
	
	// option delete. same as delete_option but adds a conditional delete
	// if val == option value then delete
	function option_delete($name=null, $val=null) {
		if(!$name) return false;
		$return = false;
		if($val) {
			if(get_option($name) == $val) $return = delete_option($name);
		} else {
			$return = delete_option($name);
		}
		return $return;
	}
	
	
	function option_sort($name=null, $array=null) {
		if(!$name || !$array) return false;
		$results = get_option($name);
		if(is_array($results)) {
			$results['order'] = $array;
			$this->option_save($name, $results);
		}
	}
	
	
	function option_sort_save($name=null, $val=null) {
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
		$this->option_save($name, $array);
	}
	
	
	function option_sort_del($name, $key) {
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
			$this->option_save($name, $array);
		}
	}
	
	
	// if option results are an array merge that array with new array
	// if it is not an array and is empty, save new array as option value
	function option_array_merge($name=null, $add_array=array()) {
		if(!$name && !$add_array) return false;
		$array = get_option($name);
		if(is_array($array)) {
			$_array = array_merge($array, $add_array);
			return $this->option_save($name, $_array);
		} else {
			if(!$array) {
				return $this->option_save($name, $add_array);
			}
		}
		return false;
	}
	
	
	// remove an item from from an options stored array by matching a key
	// or by matching values
	function option_remove_array_val($name=null, $field=null, $val=null) {
		if(!$name) return false;
		if(!$field || !$val) return false;
		
		$array = get_option($name);
		if(is_array($array)) {
			if($field) if(isset($array[$field])) unset($array[$field]);
			if($val) foreach($array as $k => $v) if($v == $val) unset($array[$k]);
			$this->option_save($name, $array);
			return true;
		}
		return false;
	}
	/*
	----------------------------------------
	END OPTIONS
	----------------------------------------
	*/
	
	
	
	/*
	----------------------------------------
	DATABASE INSTALL / UPGRADE
	----------------------------------------
	*/
	// requires db_option, db_version, and db_sql() function
	// checks database version and installs if the same
	function db_check() {
		if(!is_admin() || !$this->db_option || !$this->db_version || !$this->table_schema) return false;
		global $wpdb;
		$current_db_version = get_option($this->db_option);
		if($this->db_version != $current_db_version) {
			$this->db_install_options();
			$this->db_install($sql);
		}
	}
	
	
	// install wp options
	function db_install_options() {
		if(!is_array($this->options) || !$this->options) return false;
		foreach($this->options as $option) if(!get_option($option)) add_option($option);
	}
	
	
	// installs database
	function db_install($sql) {
		global $wpdb;
		foreach($this->table_schema as $ts_key => $ts_array) {
			$sql = "CREATE TABLE {$wpdb->prefix}{$ts_key} (\n";
			$keys = array_keys($ts_array);
			foreach($ts_array as $k => $row) {
				$sql.= "$k {$row[0]},\n";
			}
			$sql.= "UNIQUE KEY id({$keys[0]})
			);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		update_option($this->db_option, $this->db_version);
	}
	/*
	----------------------------------------
	END DATABASE INSTALL / UPGRADE
	----------------------------------------
	*/
	
	
	
	/*
	----------------------------------------
	CREATE POST TYPES & TAXONOMIES
	----------------------------------------
	*/
	function create_post_type($slug=false, $single = false, $plural=null, $type='page', $_args=array()) {
		global $wp_rewrite;
		if(!$slug && !$single) return;
		if($single && !$plural) $plural = $single . 's';
		if($single && $plural) {
			$labels = array(
				'name' => _x($plural, 'post type general name'),
				'singular_name' => _x($single, 'post type singular name'),
				'add_new' => _x('Add New', strtolower($single)),
				'add_new_item' => __('Add New ' . $single),
				'edit_item' => __('Edit ' . $single),
				'new_item' => __('New ' . $single),
				'view_item' => __('View ' . $single),
				'search_items' => __('Search ' . $plural),
				'not_found' =>  __('No ' . strtolower($plural) . ' found'),
				'not_found_in_trash' => __('No ' . strtolower($plural) . ' found in Trash'), 
				'parent_item_colon' => '',
				'menu_name' => _x($plural, 'post type general name')
			);
			if($type=='page') {
				$args = array_merge(array(
					'labels' => $labels,
					'public' => true,
					'publically_queryable' => true,
					'exclude_from_search' => false,
					'show_ui' => true,
					'show_in_menu' => true,
					'query_var' => true,
					'rewrite' => array("slug" => $slug),
					'capability_type' => 'page',
					'hierarchical' => true,
					'supports' => array('title', 'editor', 'author', 'excerpt', 'custom-fields', 'page-attributes')
				), $_args);
			}
			if($type=='post') {
				$args = array_merge(array(
					'labels' => $labels,
					'public' => true,
					'publicly_queryable' => true,
					'has_archive' => true,
					'show_ui' => true,
					'show_in_menu' => true,
					'query_var' => true,
					'rewrite' => array("slug" => $slug),
					'capability_type' => 'post',
					'hierarchical' => false,					
					'menu_position' => null,
					'supports' => array('title', 'editor', 'author', 'excerpt', 'custom-fields', 'comments')
				), $_args);
			}
			register_post_type($slug, $args);
		}
	}


	
	function create_post_type_tax($taxname=null, $object_type=null, $single=null, $plural=null, $args=array()) {
		if(!$taxname) return;
		if($single && !$plural) $plural = $single . 's';
		if(!$single || !$plural) return;
		$labels = array(
			'name' => __( $plural ),
			'singular_name' => __( $single ),
			'search_items' => __( 'Search ' . $plural ),
			'popular_items' => __( 'Popular ' . $plural ),
			'all_items' => __( 'All ' . $plural ),
			'parent_item' => __( 'Parent ' . $single ),
			'parent_item_colon' => __( 'Parent ' . $single . ':' ),
			'edit_item' => __( 'Edit ' . $single ),
			'update_item' => __( 'Update ' . $single ),
			'add_new_item' => __( 'Add New ' . $single ),
			'new_item_name' => __( 'New ' . $single . ' Name' ),
		);
		$_args = array_merge(array(
			'label' => $plural,
			'labels' => $labels,
			'public' => true,
			'show_ui' => true,
			'show_tagcloud' => true,
			'hierarchical' => true,
			'update_count_callback' => null,
			'rewrite' => true,
		), $args);
		register_taxonomy($taxname, $object_type, $_args);
	}
	/*
	----------------------------------------
	END CREATE POST TYPES & TAXONOMIES
	----------------------------------------
	*/
}