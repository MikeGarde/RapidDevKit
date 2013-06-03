<?php
class Page extends Model {
	var $post_type = 'page'; // name of post type, if used
	var $order_by = 'menu_order';
	var $order = 'asc';
}
