<?php

/**
 *  Post Overrides
 * 
 *  Here, we add metaboxes to the sidebar of post types that have been enabled for overrides in the global settings page.
 * 
 *  fsst_get_post_configuration : returns the saved post settings; defaults to merging it with the global settings
 *  fsst_add_post_override_boxes : adds the override fields to the enabled post types
 *  fsst_render_metabox_html : enqueues all scripts, styles, and renders the override fields themselves
 *  fsst_save_post_override_configuration : saves the post override configuration to the postmeta table
 *  fsst_regenerate_thumbnail_on_post_update : automatically triggers a regeneration of the thumbnail when a post is updated
 *  fsst_get_overrides_text_field_html : renders the text input used in the post overrides config
 *  fsst_get_overrides_image_field_html : renders the image input used in the post overrides config
 *  fsst_get_overrides_select_field_html : renders the select input used in the post overrides config
 *  fsst_get_overrides_color_picker_field_html : renders the color picker input used in the post overrides config
 *  fsst_get_default_post_configuration : returns an array of permitted configuration keys and their default values
 *  fsst_get_overridable_post_types : returns a list of post types that overrides are enabled for
 * 
 **/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}


add_action('add_meta_boxes',		'fsst_add_post_override_boxes', 10, 2);
add_action('save_post',					'fsst_save_post_override_configuration', PHP_INT_MAX, 3);

function fsst_get_post_configuration($post_id)
{
	$post_configuration = get_post_meta($post_id, 'sharethumb');

	if (isset($post_configuration[0]) && is_array($post_configuration[0])) {
		$post_configuration = $post_configuration[0];
	} else if (!is_array($post_configuration)) {
		$post_configuration = [];
	}

	$global_configuration = get_option('fsst_settings');
	$post_configuration['api_key_set'] = !empty($global_configuration['api_key']);

	return $post_configuration;
}

function fsst_add_post_override_boxes($post_type, $post)
{
	$configuration = get_option('fsst_settings');
	if (is_array($configuration['post_types']) && count($configuration['post_types'])) {
		add_meta_box(
			'sharethumb-meta-box',
			__('ShareThumb Overrides'),
			'fsst_render_metabox_html',
			$configuration['post_types'],
			'side',
			'default'
		);
	}
}

function fsst_render_metabox_html($post)
{
	wp_enqueue_style('select2', plugins_url('../assets/select2.min.css', __FILE__), [], '4.1.0-rc.0');
	wp_enqueue_script('select2', plugins_url('../assets/select2.min.js', __FILE__), ['jquery'], '4.1.0-rc.0', ['in_footer' => true]);
	wp_enqueue_script('jscolor', plugins_url('../assets/jscolor.min.js', __FILE__), [], '2.5.1', ['in_footer' => true]);
	wp_enqueue_style('settings-page-css', plugins_url('../settings-page.css', __FILE__), [], '1.2');
	wp_enqueue_script('settings-page-js', plugins_url('../settings-page.js', __FILE__), ['jquery', 'jscolor', 'select2'], '1.2', ['in_footer' => true]);

	$site_url = preg_replace("(^https?://)", "", get_site_url());

	wp_add_inline_script('settings-page-js', "
			const theme_url = '" . FSST_PREVIEW_URL . "';
			const domain = '" . $site_url . "';
			const image_preview_url = '" . admin_url('admin-ajax.php?action=fsst_preview_thumbnail&nonce=' . wp_create_nonce('fsst_preview_thumbnail')) . "';
			const settings_context = 'override';
		", 'before');

	include fsst_plugin_path() . '/template-post-override-settings.php';
}

function fsst_sanitize_post_overrides($user_submitted_overrides)
{
	$retval = [];

	// depending on from where these values came, the keys may be prefixed with fsst_
	foreach ($user_submitted_overrides as $key => $value) {
		$sanitized_value = '';
		$configuration_key = '';
		switch ($key) {
			case 'logo':
			case 'icon':
			case 'default_thumbnail':
			case 'fsst_logo':
			case 'fsst_icon':
			case 'fsst_default_thumbnail':
				$configuration_key = str_replace('fsst_', '', $key);
				$sanitized_value = (int) $value;
				break;
			case 'theme':
			case 'theme_custom':
			case 'font':
			case 'highlight_font':
			case 'fsst_theme':
			case 'fsst_theme_custom':
			case 'fsst_font':
			case 'fsst_highlight_font':
				$configuration_key = str_replace('fsst_', '', $key);
				$sanitized_value = sanitize_text_field($value);
				break;
			case 'light_theme_font_color':
			case 'light_theme_bg_color':
			case 'dark_theme_font_color':
			case 'dark_theme_bg_color':
			case 'accent_color':
			case 'fsst_light_theme_font_color':
			case 'fsst_light_theme_bg_color':
			case 'fsst_dark_theme_font_color':
			case 'fsst_dark_theme_bg_color':
			case 'fsst_accent_color':
				$configuration_key = str_replace('fsst_', '', $key);
				$sanitized_value = sanitize_hex_color($value);
				break;
			case 'icon_url':
			case 'logo_url':
			case 'default_thumbnail_url':
			case 'fsst_icon_url':
			case 'fsst_logo_url':
			case 'fsst_default_thumbnail_url':
				$configuration_key = str_replace('fsst_', '', $key);
				$sanitized_value = sanitize_url($value);
				break;
		}

		if ($configuration_key) {
			$retval[$configuration_key] = $sanitized_value;
		}
	}

	return $retval;
}

function fsst_save_post_override_configuration($post_id, $post, $update)
{
	if (empty($_POST['sharethumb_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sharethumb_nonce'])), 'sharethumb_metabox')) {
		return $post_id;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
	}

	$default_configuration = fsst_get_default_post_configuration();
	$post_configuration = [];
	$user_submitted_values = fsst_sanitize_post_overrides($_POST);

	foreach ($default_configuration as $key => $value) {
		if (isset($user_submitted_values[$key])) {
			$post_configuration[$key] = $user_submitted_values[$key];
		} else {
			$post_configuration[$key] = $value;
		}
	}

	$post_configuration['logo_url'] = ($post_configuration['logo']) ? wp_get_attachment_image_url($post_configuration['logo'], 'large') : '';
	$post_configuration['icon_url'] = ($post_configuration['icon']) ? wp_get_attachment_image_url($post_configuration['icon'], 'large') : '';

	update_post_meta($post_id, 'sharethumb', $post_configuration);

	$configuration = get_option('fsst_settings');
	$thumbnail_id = fsst_api_get_thumbnail_id($configuration['api_key'], get_the_permalink($post_id));
	if ($thumbnail_id) {
		foreach ($configuration as $key => $value) {
			if (empty($post_configuration[$key])) {
				$post_configuration[$key] = $value;
			}
		}
		$post_configuration['title'] = get_the_title($post_id);
		fsst_api_regenerate_thumbnail($post_configuration, $thumbnail_id);
	}
}

function fsst_get_overrides_text_field_html($field_label, $field_name, $configuration, $description = '')
{
	$configuration_key = str_replace('fsst_', '', $field_name);
	$field_value = isset($configuration[$configuration_key]) ? $configuration[$configuration_key] : '';
	if ($description) {
		$description = "<div class='description'>" . wp_kses_post($description) . "</div>";
	}
	return "
        <label for='field-" . esc_attr($field_name) . "'>" . esc_html($field_label) . "</label>
        <input id='field-" . esc_attr($field_name) . "' type='text' name='" . esc_attr($field_name) . "' placeholder='" . esc_attr($field_label) . "' value='" . esc_attr($field_value) . "' />
        {$description}
    ";
}

function fsst_get_overrides_image_field_html($field_label, $field_name, $configuration)
{
	$configuration_key = str_replace('fsst_', '', $field_name);
	$field_markup = '';
	$field_value = isset($configuration[$configuration_key]) ? $configuration[$configuration_key] : 0;
	$image_url = '';
	if ($field_value) {
		$image_url = wp_get_attachment_image_url($field_value, 'medium');
		if ($image_url) {
			$field_markup = "               
					<span class='button image-upload' title='Upload Image'><img src='" . esc_url($image_url) . "' /></span>
					<span class='image-remove' title='Remove Image'></span>
			";
		}
	} else {
		$field_markup = "
				<span class='button image-upload'>Upload Image</span>
				<span class='image-remove' title='Remove Image'></span>
		";
	}

	return "
				<label for='field-" . esc_attr($field_name) . "'>" . esc_html($field_label) . "</label>
				<div class='sharethumb-image-container'>
					{$field_markup}
					<input 
						id='field-" . esc_attr($field_name) . "' 
						type='hidden' 
						name='" . esc_attr($field_name) . "'    
						value='" . esc_attr($field_value) . "' 
						data-url-field-id='field-" . esc_attr($field_name) . "_url' 
					/>
					<input 
						id='field-" . esc_attr($field_name) . "_url' 
						type='hidden' 
						name='" . esc_attr($field_name) . "_url' 
						value='" . esc_url($image_url) . "' 
					/>
				</div>
    ";
}

function fsst_get_overrides_select_field_html($field_label, $field_name, $configuration)
{
	$configuration_key = str_replace('fsst_', '', $field_name);
	$field_value = isset($configuration[$configuration_key]) ? $configuration[$configuration_key] : '';
	$options = fsst_get_select_options($configuration_key);

	// If, for whatever reason the ShareThumb API isn't reachable, let's just show a text field
	if (!is_array($options) || !count($options)) {

		return "
            <label for='field-" . esc_attr($field_name) . "'>" . esc_html($field_label) . "</label>
            <input id='field-" . esc_attr($field_name) . "' type='text' name='" . esc_attr($field_name) . "' value='" . esc_attr($field_value) . "' />
        ";
	} else {

		$options_markup = '';
		foreach ($options as $key => $label) {
			if ($field_value == $key) {
				$options_markup .= "<option value='" . esc_attr($key) . "' selected>" . esc_html($label) . "</option>";
			} else {
				$options_markup .= "<option value='" . esc_attr($key) . "'>" . esc_html($label) . "</option>";
			}
		}

		$option_none_label = 'Global Default';
		$option_none = ($field_value) ? "<option value=''>{$option_none_label}</option>" : "<option value='' selected>{$option_none_label}</option>";

		return "
			<label for='field-" . esc_attr($field_name) . "'>" . esc_html($field_label) . "</label>
			<select id='field-" . esc_attr($field_name) .
			"' class='select2' name='" . esc_attr($field_name) .
			"' placeholder='" . esc_attr($field_label) .
			"' data-placeholder='" . esc_attr($field_label) . "'>
			{$option_none}
			{$options_markup}
			</select>
			";
	}
}

// Functionality for this field is supplemented by the jscolor script
function fsst_get_overrides_color_picker_field_html($field_label, $field_name, $configuration, $show_message_field = false)
{
	$configuration_key = str_replace('fsst_', '', $field_name);
	$field_value = isset($configuration[$configuration_key]) ? $configuration[$configuration_key] : '';
	return "
			<label for='field-" . esc_attr($field_name) . "'>" . esc_html($field_label) . "</label>
			<input id='field-" . esc_attr($field_name) . "' data-jscolor='{required:false}' name='" . esc_attr($field_name) . "' value='" . esc_attr($field_value) . "' placeholder='Select Color' />" .
		($show_message_field ? "<div class='color-ratio-message'></div>" : "");
}


// Post-specific overrides
function fsst_get_default_post_configuration()
{
	return [
		'logo' => 0,
		'icon' => 0,
		'default_thumbnail' => 0,
		'theme' => '',
		'theme_custom' => '',
		'font' => '',
		'highlight_font' => '',
		'dark_theme_font_color' => '',
		'dark_theme_bg_color' => '',
		'light_theme_font_color' => '',
		'light_theme_bg_color' => '',
		'accent_color' => '',
		'icon_url' => '',
		'logo_url' => '',
		'default_thumbnail_url' => ''
	];
}


function fsst_get_overridable_post_types()
{
	$all_post_types = get_post_types([], 'objects');
	$default_excluded_post_types = ['attachment', 'custom_css', 'nav_menu_item', 'revision', 'attachment', 'seopress_schemas', 'seopress_404', 'seopress_bot', 'acf-field', 'acf-taxonomy', 'acf-field-group', 'acf-post-type', 'cbxchangelog', 'wp_navigation', 'wp_global_styles', 'wp_template_part', 'wp_template', 'wp_block', 'user_request', 'oembed_cache', 'customize_changeset'];
	$overridable_post_types = [];
	foreach ($all_post_types as $key => $value) {
		if (!in_array($key, $default_excluded_post_types)) {
			$overridable_post_types[$key] = $value->label;
		}
	}
	return $overridable_post_types;
}
