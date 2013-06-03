<?php
class Slide extends Model {
	var $post_type = 'slide'; // name of post type, if used
	var $order_by = 'menu_order';
	var $order = 'asc';
	
	// method used for setting up post type
	function __construct() {
		// post type creation
		$this->create_post_type($this->post_type, 'Slide', 'Slides', 'page', array(
			'exclude_from_search' => true,
			'supports' => array('title', 'editor', 'custom-fields')
		));
	}
	
	
	// get all slides
	function get_all() {
		$slides = $this->get_meta($this->all());
		return $slides;
	}
}