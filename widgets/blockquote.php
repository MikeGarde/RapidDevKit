<?php
/*
 * Subpage List widget
 */
class RDKBlockquoteWidget extends WP_Widget {
	/**
	 * Construction method for adding widget to the WordPress admin.
	 */
	function RDKBlockquoteWidget() {
		$widget_ops = array(
			'classname' => 'rdk_blockquote_widget',
			'description' => __("Display a blockquote", 'rdk_blockquote_widget')
		);
		$this->WP_Widget('rdk_blockquote', __('RDK Blockquote', 'rdk_blockquote_widget'), $widget_ops);
	}
	
	/**
	 * Formats a widgets content and echos it to the screen.
	 * 
	 * @param array $args					key/value array of WordPress widget default variables
	 * @param array $instance				key/value array of Widget options
	 */
	function widget($args, $instance) {
		extract($args);
		global $post;
		
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$quote = empty($instance['quote']) ? '' : apply_filters('the_content', $instance['quote']);
		$cite = empty($instance['cite']) ? '' : apply_filters('widget_cite', $instance['cite']);
		$override = empty($instance['override']) ? 0 : $instance['override'];
		
		if($override) {
			$_quote = get_post_meta($post->ID, 'Quote', true);
			$_cite = get_post_meta($post->ID, 'Cite', true);
			
			if($_quote) {
				$quote = apply_filters('the_content', $_quote);
				$cite = $_cite;
			}
		}
		
		echo $before_widget;
		if($title) echo $before_title . $title . $after_title;
		echo '<blockquote class="pullquote">';
		echo $quote;
		if($cite) echo '<cite>' . $cite . '</cite>';
		echo '</blockquote>';
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
		$instance['quote'] = strip_tags($new_instance['quote']);
		$instance['cite'] = strip_tags($new_instance['cite']);
		$instance['override'] = strip_tags($new_instance['override']);
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
		$quote = esc_attr($instance['quote']);
		$cite = esc_attr($instance['cite']);
		$override = esc_attr($instance['override']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"
				type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		
		<textarea class="widefat" id="<?php echo $this->get_field_id('quote'); ?>" name="<?php echo $this->get_field_name('quote'); ?>"><?php echo $quote; ?></textarea>
		
		<p>
			<label for="<?php echo $this->get_field_id('cite'); ?>">
				<?php _e('Cite:'); ?>
				<input class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('cite'); ?>" name="<?php echo $this->get_field_name('cite'); ?>"
				type="text" value="<?php echo $cite; ?>" />
			</label>
		</p>
		
		<p>
      		<input id="<?php echo $this->get_field_id('override'); ?>" name="<?php echo $this->get_field_name('override'); ?>" type="checkbox" value="1" <?php checked( '1', $override ); ?>/>
    		<label for="<?php echo $this->get_field_id('override'); ?>">Use page quote if exists<br /><em><small>(Custom Fields 'Quote' and 'Cite')</small></em></label>
		</p>
		
		<?php 
	}
}




/*
 * Register the RDK Subpage List Widget
 */
if(!function_exists('rdk_blockquote_widget_register')) {
	function rdk_blockquote_widget_register() {
		if(class_exists('RDKBlockquoteWidget')) register_widget('RDKBlockquoteWidget');
	}
	
	add_action("widgets_init", 'rdk_blockquote_widget_register');
}