<?php
class Article extends Model {
	var $post_type = 'article'; // name of post type, if used
	
	function __construct() {
		$this->create_post_type($this->post_type, 'Article', 'Articles', 'post');
		$this->create_post_type_tax('articlecategory', $this->post_type, 'Category', 'Categories');
	}
}