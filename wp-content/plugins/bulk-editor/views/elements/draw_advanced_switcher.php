<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $WPBE;
?>

<input type="checkbox" <?php if (!intval($WPBE->settings->load_switchers)): ?>
	   onclick="wpbe_click_checkbox(this, '<?php echo esc_attr($numcheck) ?>')"<?php endif; ?> 
	   data-numcheck='<?php echo esc_html($numcheck) ?>' 
	   data-true='<?php esc_html_e($labels['true']) ?>' 
	   data-false='<?php esc_html_e($labels['false']) ?>' 
	   data-val-true='<?php echo esc_attr($vals['true']) ?>' 
	   data-val-false='<?php echo esc_html($vals['false']) ?>' 
	   data-trigger-target='<?php echo esc_html($trigger_target) ?>' 
	   data-class-true='label-inverse-success' 
	   data-class-false='label-inverse-default' 
	   class="js-switch js-check-change" <?php checked($is) ?> />
<input type="hidden" 
	   id="js_check_<?php esc_html_e($numcheck) ?>" 
	   data-hidden-numcheck='<?php esc_html_e($numcheck) ?>' 
	   value="<?php echo esc_attr($vals[($is ? 'true' : 'false')]) ?>" 
	   name="<?php echo esc_html($name) ?>" />
<label data-label-numcheck='<?php esc_html_e($numcheck) ?>' class="label <?php echo esc_html($css_classes) ?> <?php echo ($is ? 'label-inverse-success' : 'label-inverse-default') ?> check-change js-check-change-field">
	<?php echo esc_html($is ? $labels['true'] : $labels['false']) ?>
</label>
