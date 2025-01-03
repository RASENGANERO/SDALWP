<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="popup_val_in_tbl wpbe-button js_wpbe_tax_popup" 
	 onclick="wpbe_act_tax_popup(this)" 
	 data-post-id="<?php echo esc_html($post['ID']) ?>" 
	 id="popup_val_ids_<?php echo esc_attr($tax_key) ?>_<?php echo esc_attr($post['ID']) ?>" 
	 data-terms-ids="<?php echo esc_html(implode(',', $data['terms_ids'])) ?>" 
	 data-key="<?php echo esc_html($tax_key) ?>" 
	 data-name="<?php esc_html_e(htmlentities($post['post_title'], ENT_QUOTES)) ?>">
    <ul>
        <?php if (!empty($data['terms_ids'])): ?>
            <?php foreach ($data['terms_ids'] as $k => $term_id): ?>
                <li class="wpbe_li_tag"><?php echo wp_kses_post($data['terms_titles'][$k]) ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="wpbe_li_tag"><?php esc_html_e('no items', 'bulk-editor') ?></li>
            <?php endif; ?>
    </ul>
</div>
