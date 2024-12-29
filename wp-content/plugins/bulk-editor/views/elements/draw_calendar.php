<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<input type="text" 
	   onmouseover="wpbe_init_calendar(this)" 
	   data-title="<?php esc_html_e(str_replace('"', '', strip_tags($post_title))) ?>" 
	   data-val-id="calendar_<?php echo esc_attr($field_key) ?>_<?php echo esc_attr($post_id) ?>" 
	   value="<?php if ($val) echo wp_kses_post(date('d/m/Y H:i', $val)) ?>" 
	   class="wpbe_calendar" 
	   placeholder="<?php echo esc_html($print_placeholder ? $post_title : '') ?>" />
<input type="hidden" 
	   data-key="<?php echo esc_attr($field_key) ?>" 
	   data-post-id="<?php echo esc_html($post_id) ?>" 
	   id="calendar_<?php echo esc_attr($field_key) ?>_<?php echo esc_attr($post_id) ?>" 
	   value="<?php echo esc_attr($val) ?>" 
	   name="<?php echo esc_html($name) ?>" />
<a href="javascript: void(0);" class="wpbe_calendar_cell_clear" title="<?php esc_html_e('Will never reset published date, and for modified date field  will set current time!', 'bulk-editor') ?>"><?php esc_html_e('clear', 'bulk-editor') ?></a>
