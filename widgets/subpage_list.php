<?php
/*
 * Subpage List widget
 */
class RDXSubpageWidget extends WP_Widget {
	/**
	 * Construction method for adding widget to the WordPress admin.
	 */
	function RDXSubpageWidget() {
		$widget_ops = array(
			'classname' => 'rdx_subpage_list_widget',
			'description' => __("Display the subpages of the current page", 'rdx_subpage_list_widget')
		);
		$this->WP_Widget('rdx_subpage_list', __('RDX Subpage List', 'rdx_subpage_list_widget'), $widget_ops);
	}
	
	/**
	 * Formats a widgets content and echos it to the screen.
	 * 
	 * @param array $args					key/value array of WordPress widget default variables
	 * @param array $instance				key/value array of Widget options
	 */
	function widget($args, $instance) {
		global $rdk;
		extract($args);
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		
		echo $before_widget;
		if($title) echo $before_title . $title . $after_title;
		echo '<ul class="subpage_list">' . $rdk->subpage_list() . '</ul>';
		echo $after_widget;
	}
	
	/**
	 * Cleans input values before saving
	 * 
	 * @param array $new_instance			key/value array of form inputs
	 * @param array $old_instance			key/value array of available options
	 * @return array $instance				key/value array of cleaned form inputs
	 */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	/**
	 * Creation of Widget form for the Widgets page in WordPress admin.
	 * 
	 * @param array $instance				key/value array of saved fields
	 * @echo string							echos form to the admin screen
	 */
	function form($instance) {
		$title = esc_attr($instance['title']);
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:'); ?>*
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"
				type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<small><em>*optional</em></small>
		
		<?php 
	}
}




/*
 * Register the RDX Subpage List Widget
 */
if(!function_exists('rdx_subpage_list_widget_register')) {
	function rdx_subpage_list_widget_register() {
		if(class_exists('RDXSubpageWidget')) register_widget('RDXSubpageWidget');
	}
	
	add_action("widgets_init", 'rdx_subpage_list_widget_register');
}