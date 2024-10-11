<?php
global $category_meta_data;
$category_meta_data = [
	'_custom_h1' => [
		'label' => 'Заголовок H1',
		'comment' => 'Заголовок H1 в архивах таксономий',
	],
];

add_filter('get_the_archive_title_prefix', '__return_empty_string');

add_action('category_edit_form_fields', 'bus_category_edit_form_fields', 10, 2);
function bus_category_edit_form_fields($term, $taxonomy)
{
	global $category_meta_data;
	foreach ($category_meta_data as $meta_key => $meta_field_data)
	{
		$meta_value = get_term_meta($term->term_id, $meta_key, true);

		if (in_array($meta_key, ['reklama1','reklama2']))
		{
			echo '<tr class="form-field">
					<th scope="row">
						<label for="'.$meta_key.'">'.$meta_field_data['label'].'</label>
					</th>
					<td>
						<textarea name="'.$meta_key.'" id="'.$meta_key.'" size="40">'.stripslashes($meta_value).'</textarea>
						<p class="description">'.$meta_field_data['comment'].'</p>
					</td>
				</tr>';
		}
		else
		{
			echo '<tr class="form-field">
					<th scope="row">
						<label for="'.$meta_key.'">'.$meta_field_data['label'].'</label>
					</th>
					<td>
						<input name="'.$meta_key.'" id="'.$meta_key.'" type="text" value="'.esc_attr($meta_value).'" size="40">
						<p class="description">'.$meta_field_data['comment'].'</p>
					</td>
				</tr>';
		}
	}
}

add_action('edited_category', 'bus_edited_category');
function bus_edited_category($term_id)
{
	global $category_meta_data;
	foreach ($category_meta_data as $meta_key => $meta_field_data)
	{
		$res = update_term_meta($term_id, $meta_key, $_POST[$meta_key]);
	}
}