<?php
/**
 *  Settings Page
 * 
 *  We add an admin_menu action, to add a ShareThumb Settings menu item on the backend.
 *  We add an admin_enqueue_scripts action, to enqueue all of the scripts & styles we require.
 * 
 *  Also relevant are settings-page.js, settings-page.css which are in this same folder.
 * 
 * fsst_init_admin_menu
 * fsst_enqueue_scripts
 * fsst_register_settings
 * fsst_get_settings_hidden_field_html
 * fsst_get_settings_checkboxes_field_html
 * fsst_get_settings_color_field_html
 * fsst_get_settings_image_field_html
 * fsst_get_settings_select_field_html
 * fsst_get_settings_text_field_html
 * fsst_get_settings_general_section_html
 * fsst_render_settings_page_html
 * fsst_get_select_options
 * 
 **/

if(!defined('ABSPATH')) { exit; }

add_action('admin_init',			'fsst_register_settings');
add_action('admin_menu',			'fsst_init_admin_menu');
add_action('admin_enqueue_scripts',	'fsst_enqueue_scripts');


// Add our menu option; SVG borrowed from fontawesome (free icon)
function fsst_init_admin_menu() {
	add_menu_page(
		__('Settings', 'fsst'),
		__('ShareThumb', 'fsst'),
		'manage_options',
		'sharethumb',
		'fsst_render_settings_page_html',
		'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48IS0tISBGb250IEF3ZXNvbWUgUHJvIDYuMi4xIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlIChDb21tZXJjaWFsIExpY2Vuc2UpIENvcHlyaWdodCAyMDIyIEZvbnRpY29ucywgSW5jLiAtLT48cGF0aCBkPSJNMzEzLjQgMzIuOWMyNiA1LjIgNDIuOSAzMC41IDM3LjcgNTYuNWwtMi4zIDExLjRjLTUuMyAyNi43LTE1LjEgNTIuMS0yOC44IDc1LjJINDY0YzI2LjUgMCA0OCAyMS41IDQ4IDQ4YzAgMjUuMy0xOS41IDQ2LTQ0LjMgNDcuOWM3LjcgOC41IDEyLjMgMTkuOCAxMi4zIDMyLjFjMCAyMy40LTE2LjggNDIuOS0zOC45IDQ3LjFjNC40IDcuMiA2LjkgMTUuOCA2LjkgMjQuOWMwIDIxLjMtMTMuOSAzOS40LTMzLjEgNDUuNmMuNyAzLjMgMS4xIDYuOCAxLjEgMTAuNGMwIDI2LjUtMjEuNSA0OC00OCA0OEgyOTQuNWMtMTkgMC0zNy41LTUuNi01My4zLTE2LjFsLTM4LjUtMjUuN0MxNzYgNDIwLjQgMTYwIDM5MC40IDE2MCAzNTguM1YzMjAgMjcyIDI0Ny4xYzAtMjkuMiAxMy4zLTU2LjcgMzYtNzVsNy40LTUuOWMyNi41LTIxLjIgNDQuNi01MSA1MS4yLTg0LjJsMi4zLTExLjRjNS4yLTI2IDMwLjUtNDIuOSA1Ni41LTM3Ljd6TTMyIDE5Mkg5NmMxNy43IDAgMzIgMTQuMyAzMiAzMlY0NDhjMCAxNy43LTE0LjMgMzItMzIgMzJIMzJjLTE3LjcgMC0zMi0xNC4zLTMyLTMyVjIyNGMwLTE3LjcgMTQuMy0zMiAzMi0zMnoiIGZpbGw9ImN1cnJlbnRDb2xvciIvPjwvc3ZnPg=='
	);
}

// Enqueue the scripts & styles for our settings page
function fsst_enqueue_scripts($hook) {
	if($hook === 'toplevel_page_sharethumb') {

		wp_enqueue_media();
		wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery']);
		wp_enqueue_script('jscolor', 'https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.5.1/jscolor.min.js');
		wp_enqueue_script('settings-page-js', plugins_url('../settings-page.js', __FILE__), ['jquery', 'jscolor', 'select2'], '1.0');
		wp_enqueue_style('settings-page-css', plugins_url('../settings-page.css', __FILE__), [], '1.0');

	}
}

// Register our settings to be displayed on the settings page, using WP Settings API
function fsst_register_settings() {
	register_setting('sharethumb', 'sharethumb_options');

	add_settings_section(
		'sharethumb_section_general',
		__('', 'sharethumb'), 
		'fsst_get_settings_general_section_html',
		'sharethumb'
	);

	add_settings_field(
		'api_key',
		__('API Key', 'sharethumb'),
		'fsst_get_settings_text_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'api_key',
			'class' => 'sharethumb-settings-row',
			'description' => "You can find the API Key on the settings page for your site at <a href='https://app.sharethumb.io/dashboard' target='_blank'>https://app.sharethumb.io/dashboard</a>."
		]
	);

	add_settings_field(
		'dv_code',
		__('Domain Verification Code', 'sharethumb'),
		'fsst_get_settings_text_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'dv_code',
			'class' => 'sharethumb-settings-row',
			'description' => "You can find the Domain Validation code for your site at <a href='https://app.sharethumb.io/dashboard' target='_blank'>https://app.sharethumb.io/dashboard</a>."
		]
	);

	add_settings_field(
		'theme',
		__('Theme', 'sharethumb'),
		'fsst_get_settings_select_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'theme',
			'class' => 'sharethumb-settings-row theme',
			'fetch_options_key' => 'theme'
		]
	);

	add_settings_field(
		'theme_custom',
		__('Custom Theme', 'sharethumb'),
		'fsst_get_settings_text_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'theme_custom',
			'class' => 'sharethumb-settings-row custom-theme'
		]
	);

	add_settings_field(
		'font',
		__('Font', 'sharethumb'),
		'fsst_get_settings_select_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'font',
			'class' => 'sharethumb-settings-row font',
			'fetch_options_key' => 'font'
		]
	);

	add_settings_field(
		'logo',
		__('Logo', 'sharethumb'),
		'fsst_get_settings_image_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'logo',
			'class' => 'sharethumb-settings-row',
			'url_field_id' => 'logo_url'
		]
	);

	add_settings_field(
		'logo_url',
		__('Icon URL', 'sharethumb'),
		'fsst_get_settings_hidden_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'logo_url',
			'class' => 'sharethumb-settings-row hidden'
		]
	);

	add_settings_field(
		'icon',
		__('Icon', 'sharethumb'),
		'fsst_get_settings_image_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'icon',
			'class' => 'sharethumb-settings-row',
			'url_field_id' => 'icon_url'
		]
	);

	add_settings_field(
		'icon_url',
		__('Icon URL', 'sharethumb'),
		'fsst_get_settings_hidden_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'icon_url',
			'class' => 'sharethumb-settings-row hidden'
		]
	);

	add_settings_field(
		'foreground',
		__('Foreground', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'foreground',
			'class' => 'sharethumb-settings-row'
		]
	);

	add_settings_field(
		'background',
		__('Background', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'background',
			'class' => 'sharethumb-settings-row'
		]
	);

	add_settings_field(
		'accent',
		__('Accent', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'accent',
			'class' => 'sharethumb-settings-row'
		]
	);

	add_settings_field(
		'secondary',
		__('Secondary', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'secondary',
			'class' => 'sharethumb-settings-row'
		]
	);

	add_settings_field(
		'post_types',
		__('Overridable Post Types', 'sharethumb'),
		'fsst_get_settings_checkboxes_field_html',
		'sharethumb',
		'sharethumb_section_general',
		[
			'label_for' => 'post_types',
			'class' => 'sharethumb-settings-row checkboxes',
			'options' => fsst_get_overridable_post_types()
		]
	);
}

function fsst_get_settings_hidden_field_html($args) {
	$configuration = get_option('sharethumb_options');

	$field_id = esc_attr($args['label_for']);	 
	$field_value = isset($configuration[$field_id]) ? esc_attr($configuration[$field_id]) : '';
	$field_name = "sharethumb_options[{$field_id}]";

	echo "
		<input type='hidden' id='field-{$field_id}' name='{$field_name}' value='{$field_value}' />
	";
}

function fsst_get_settings_checkboxes_field_html($args) {
	$configuration = get_option('sharethumb_options');

	$field_id = esc_attr($args['label_for']);	 
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';
	if(!is_array($field_value)) $field_value = [];
	$field_name = "sharethumb_options[{$field_id}][]";

	$field_description = isset($args['description']) ? $args['description'] : '';
	if($field_description) {
		$field_description = "<div class='description'>{$field_description}</div>";
	}

	$field_markup = '';

	foreach($args['options'] as $key => $label) {
		$checked = in_array($key, $field_value) ? 'checked' : '';
		$field_markup .= "
			<label>
				<input type='checkbox' name='{$field_name}' value='{$key}' {$checked} />
				{$label}
			</label>
		";
	}

	echo "
		{$field_markup}
		{$field_description}
	";
}

function fsst_get_settings_color_field_html($args) {
	$configuration = get_option('sharethumb_options');

	$field_id = esc_attr($args['label_for']);	 
	$field_value = isset($configuration[$field_id]) ? esc_attr($configuration[$field_id]) : '';
	$field_name = "sharethumb_options[{$field_id}]";

	$field_description = isset($args['description']) ? $args['description'] : '';
	if($field_description) {
		$field_description = "<div class='description'>{$field_description}</div>";
	}

	echo "
		<input id='field-{$field_id}' data-jscolor='{required:false}' name='{$field_name}' value='{$field_value}' />
		{$field_description}
	";
}

function fsst_get_settings_image_field_html($args) {
	$configuration = get_option('sharethumb_options');

	$field_id = esc_attr($args['label_for']);	 
	$field_value = isset($configuration[$field_id]) ? esc_attr($configuration[$field_id]) : 0;
	$field_name = "sharethumb_options[{$field_id}]";
	$url_field_id = $args['url_field_id'];

	$field_description = isset($args['description']) ? $args['description'] : '';
	if($field_description) {
		$field_description = "<div class='description'>{$field_description}</div>";
	}

	$field_markup = '';
	if($field_value) {
		$image_url = wp_get_attachment_image_url($field_value, 'medium');
		if($image_url) {
			$image_url = esc_url($image_url);
			$field_markup = "				
				<a href='#' class='button image-upload'><img src='{$image_url}' /></a>
				<a href='#' class='button image-remove'>Remove Image</a>
			";
		}
	} else {
		$field_markup = "
			<a href='#' class='button image-upload'>Upload Image</a>
			<a href='#' class='button image-remove' style='display: none;'>Remove Image</a>
		";
	}

	echo "
			{$field_markup}
			<input id='field-{$field_id}' type='hidden' name='{$field_name}' value='{$field_value}' data-url-field-id='field-{$url_field_id}' />
			{$field_description}
	";
}

function fsst_get_settings_select_field_html($args) {
	$configuration = get_option('sharethumb_options');

	$field_id = esc_attr($args['label_for']);	 
	$field_name = "sharethumb_options[{$field_id}]";
	$field_value = isset($configuration[$field_id]) ? esc_attr($configuration[$field_id]) : '';
	$field_options = fsst_get_select_options($args['fetch_options_key']);

	$field_description = isset($args['description']) ? $args['description'] : '';
	if($field_description) {
		$field_description = "<div class='description'>{$field_description}</div>";
	}

	$field_options_markup = '';
	foreach($field_options as $key => $label) {
		$selected = selected($field_value, esc_attr($key), false);
		$field_options_markup .= "<option value='{$key}' {$selected}>{$label}</option>";
	}

	$field_option_none_label = isset($args['post_id']) ? 'Global Default' : 'None';
	$field_option_none = ($field_value) ? "<option value=''>{$field_option_none_label}</option>" : "<option value='' selected>{$field_option_none_label}</option>";

	echo "
		<select id='field-{$field_id}' class='select2' name='{$field_name}' data-placeholder='{$field_label}'>
			{$field_option_none}
			{$field_options_markup}
		</select>
	";
}

function fsst_get_settings_text_field_html($args) {
	$configuration = get_option('sharethumb_options');

	$field_id = esc_attr($args['label_for']);	 
	$field_name = "sharethumb_options[{$field_id}]";
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';

	$field_description = isset($args['description']) ? $args['description'] : '';
	if($field_description) {
		$field_description = "<div class='description'>{$field_description}</div>";
	}

	echo "
		<input id='field-{$field_id}' type='text' name='{$field_name}' placeholder='{$field_label}' value='{$field_value}' />
		{$field_description}
	";
}

// Not doing anything with this
function fsst_get_settings_general_section_html($args) {}

// Render the global settings page
function fsst_render_settings_page_html() {
	if(!current_user_can('manage_options')) {
		return;
	}

	if(isset($_GET['settings-updated'])) {
		add_settings_error('sharethumb_messages', 'sharethumb_message', __('Settings saved locally.', 'sharethumb'), 'updated');

		$configuration = get_option('sharethumb_options');
		$api_key = $configuration['api_key'];
		if($api_key) {
			$result = fsst_api_validate_key($api_key);
			if($result) {
				add_settings_error('sharethumb_messages', 'sharethumb_message', __('ShareThumb API key is valid.', 'sharethumb'), 'updated');

				// save the configuration to sharethumb
				$result = fsst_api_save_global_configuration($configuration);
				if($result === true) {
					add_settings_error('sharethumb_messages', 'sharethumb_message', __('Settings updated on ShareThumb.', 'sharethumb'), 'updated');
				} else {
					if($result === false) {
						add_settings_error('sharethumb_messages', 'sharethumb_message', __('Error updating settings on ShareThumb.', 'sharethumb'), 'error');
					} else {
						add_settings_error('sharethumb_messages', 'sharethumb_message', __('Error updating settings on ShareThumb.', 'sharethumb') . " {$result}", 'error');
					}
				}
			} else {
				add_settings_error('sharethumb_messages', 'sharethumb_message', __('ShareThumb API key is invalid.', 'sharethumb'), 'error');
			}
		}
	}
	settings_errors('sharethumb_messages');

	$page_title = esc_html(get_admin_page_title());
	echo "
		<div class='wrap'>
			<h1>{$page_title}</h1>
			<form action='options.php' method='post'>
	";

	settings_fields('sharethumb');
	do_settings_sections('sharethumb');
	submit_button('Save Settings');

	echo "
			</form>
		</div>
		<p>Have questions about ShareThumb? Please <a href='mailto:support@4sitestudios.com'>email us</a> and we will get back to you within 24 business hours.</p>
	";
}

// Saving the options in a transient for 10 minutes.
// No need to constantly request the options if we don't need to, during a single configuration session.
function fsst_get_select_options($name) {
    $choices = get_transient("st_{$name}_choices");
    if(empty($choices)) {
        $choices = fsst_api_fetch_options($name);
        if(!empty($choices)) {
            set_transient("st_{$name}_choices", $choices, 600);
        }
    }
    return $choices;
}
