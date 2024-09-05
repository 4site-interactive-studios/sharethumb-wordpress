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
 * fsst_admin_notices
 * fsst_register_settings
 * fsst_get_settings_hidden_field_html
 * fsst_get_settings_checkboxes_field_html
 * fsst_get_settings_color_field_html
 * fsst_get_settings_image_field_html
 * fsst_get_settings_select_field_html
 * fsst_get_settings_text_field_html
 * fsst_get_settings_boolean_field_html
 * fsst_get_settings_general_section_html
 * fsst_render_settings_page_html
 * fsst_get_select_options
 * fsst_get_preview_thumbnail
 * 
 **/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}


add_action('admin_init',						'fsst_register_settings');
add_action('admin_menu',						'fsst_init_admin_menu');
add_action('admin_enqueue_scripts',	'fsst_enqueue_scripts');
add_action('admin_notices',					'fsst_admin_notices');
add_action("wp_ajax_fsst_preview_thumbnail", "fsst_get_preview_thumbnail");

// Add our menu option; SVG borrowed from fontawesome (free icon)
function fsst_init_admin_menu()
{
	add_menu_page(
		__('Settings', 'fsst'),
		__('ShareThumb', 'fsst'),
		'manage_options',
		'fsst_settings',
		'fsst_render_settings_page_html',
		'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48IS0tISBGb250IEF3ZXNvbWUgUHJvIDYuMi4xIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlIChDb21tZXJjaWFsIExpY2Vuc2UpIENvcHlyaWdodCAyMDIyIEZvbnRpY29ucywgSW5jLiAtLT48cGF0aCBkPSJNMzEzLjQgMzIuOWMyNiA1LjIgNDIuOSAzMC41IDM3LjcgNTYuNWwtMi4zIDExLjRjLTUuMyAyNi43LTE1LjEgNTIuMS0yOC44IDc1LjJINDY0YzI2LjUgMCA0OCAyMS41IDQ4IDQ4YzAgMjUuMy0xOS41IDQ2LTQ0LjMgNDcuOWM3LjcgOC41IDEyLjMgMTkuOCAxMi4zIDMyLjFjMCAyMy40LTE2LjggNDIuOS0zOC45IDQ3LjFjNC40IDcuMiA2LjkgMTUuOCA2LjkgMjQuOWMwIDIxLjMtMTMuOSAzOS40LTMzLjEgNDUuNmMuNyAzLjMgMS4xIDYuOCAxLjEgMTAuNGMwIDI2LjUtMjEuNSA0OC00OCA0OEgyOTQuNWMtMTkgMC0zNy41LTUuNi01My4zLTE2LjFsLTM4LjUtMjUuN0MxNzYgNDIwLjQgMTYwIDM5MC40IDE2MCAzNTguM1YzMjAgMjcyIDI0Ny4xYzAtMjkuMiAxMy4zLTU2LjcgMzYtNzVsNy40LTUuOWMyNi41LTIxLjIgNDQuNi01MSA1MS4yLTg0LjJsMi4zLTExLjRjNS4yLTI2IDMwLjUtNDIuOSA1Ni41LTM3Ljd6TTMyIDE5Mkg5NmMxNy43IDAgMzIgMTQuMyAzMiAzMlY0NDhjMCAxNy43LTE0LjMgMzItMzIgMzJIMzJjLTE3LjcgMC0zMi0xNC4zLTMyLTMyVjIyNGMwLTE3LjcgMTQuMy0zMiAzMi0zMnoiIGZpbGw9ImN1cnJlbnRDb2xvciIvPjwvc3ZnPg=='
	);

	add_Submenu_page(
		'fsst_settings',
		__('Preview', 'fsst'),
		__('Preview', 'fsst'),
		'manage_options',
		'fsst_settings_preview',
		'fsst_render_preview_page_html'
	);

	if (fsst_get_domain_verification_status()) {
		add_submenu_page(
			'fsst_settings',
			__('Upgrade', 'fsst'),
			'<span style="color: orange; font-weight: bold;">' . __('Upgrade', 'fsst') . '</span>',
			'manage_options',
			'fsst_settings_upgrade',
			'fsst_render_upgrade_page_html'
		);
	}
}

// Enqueue the scripts & styles for our settings page
function fsst_enqueue_scripts($hook)
{
	if ($hook === 'toplevel_page_fsst_settings') {

		wp_enqueue_media();
		wp_enqueue_style('select2', plugins_url('../assets/select2.min.css', __FILE__), [], '4.1.0-rc.0');
		wp_enqueue_script('select2', plugins_url('../assets/select2.min.js', __FILE__), ['jquery'], '4.1.0-rc.0', ['in_footer' => true]);
		wp_enqueue_script('jscolor', plugins_url('../assets/jscolor.min.js', __FILE__), [], '2.5.1', ['in_footer' => true]);
		wp_enqueue_script('settings-page-js', plugins_url('../settings-page.js', __FILE__), ['jquery', 'jscolor', 'select2'], '1.2', ['in_footer' => true]);
		wp_enqueue_style('settings-page-css', plugins_url('../settings-page.css', __FILE__), [], '1.2');

		$site_url = preg_replace("(^https?://)", "", get_site_url());
		wp_add_inline_script('settings-page-js', "
				const theme_url = '" . FSST_PREVIEW_URL . "';
				const domain = '" . $site_url . "';
				const image_preview_url = '" . admin_url('admin-ajax.php?action=fsst_preview_thumbnail&nonce=' . wp_create_nonce('fsst_preview_thumbnail')) . "';
				const settings_context = 'global';
			", 'before');
	}
}

// Check for any admin notices to display -- typically set during activation
function fsst_admin_notices()
{
	$notices = get_transient('fsst_admin_notices');
	if ($notices) {
		foreach ($notices as $notice) {
			printf('<div class="notice notice-%s is-dismissible" style="display: table; padding: 11px 15px;"><p style="font-size: 14px; font-weight: bold; margin: 0 0 0.5em 0;">%s</p><p>%s</p></div>', $notice['status'], $notice['title'], $notice['message']);
		}
		delete_transient('fsst_admin_notices');
	}
}

// Register our settings to be displayed on the settings page, using WP Settings API
function fsst_register_settings()
{
	register_setting('fsst_settings', 'fsst_settings', [
		'sanitize_callback' => 'fsst_sanitize_configuration'
	]);

	add_settings_section(
		'fsst_section_general',
		null,
		'fsst_get_settings_general_section_html',
		'fsst_settings'
	);

	add_settings_field(
		'fsst_api_key',
		__('API Key', 'sharethumb'),
		'fsst_get_settings_text_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'API Key',
			'label_for' => 'api_key',
			'class' => 'sharethumb-settings-row',
			'description' => "You can find the API Key on the settings page for your site at <a href='https://app.sharethumb.io/dashboard' target='_blank'>https://app.sharethumb.io/dashboard</a>."
		]
	);

	add_settings_field(
		'fsst_dv_code',
		__('Domain Verification Code', 'sharethumb'),
		'fsst_get_settings_text_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'DV Code',
			'label_for' => 'dv_code',
			'class' => 'sharethumb-settings-row',
			'description' => "You can find the Domain Validation code for your site at <a href='https://app.sharethumb.io/dashboard' target='_blank'>https://app.sharethumb.io/dashboard</a>."
		]
	);

	add_settings_field(
		'fsst_theme',
		__('Theme', 'sharethumb'),
		'fsst_get_settings_select_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Theme',
			'label_for' => 'theme',
			'class' => 'sharethumb-settings-row theme',
			'fetch_options_key' => 'theme'
		]
	);

	add_settings_field(
		'fsst_theme_custom',
		__('Custom Theme', 'sharethumb'),
		'fsst_get_settings_text_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Custom Theme',
			'label_for' => 'theme_custom',
			'class' => 'sharethumb-settings-row custom-theme'
		]
	);

	add_settings_field(
		'fsst_font',
		__('Font', 'sharethumb'),
		'fsst_get_settings_select_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Font',
			'label_for' => 'font',
			'class' => 'sharethumb-settings-row font',
			'fetch_options_key' => 'font'
		]
	);

	add_settings_field(
		'fsst_highlight_font',
		__('Highlight Font', 'sharethumb'),
		'fsst_get_settings_select_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Font',
			'label_for' => 'highlight_font',
			'class' => 'sharethumb-settings-row font',
			'fetch_options_key' => 'font'
		]
	);

	add_settings_field(
		'fsst_default_thumbnail',
		__('Default Image', 'sharethumb'),
		'fsst_get_settings_image_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Default Image',
			'label_for' => 'default-image',
			'class' => 'sharethumb-settings-row sharethumb-settings-image',
			'url_field_id' => 'default_thumbnail_url'
		]
	);

	add_settings_field(
		'fsst_default_thumbnail_url',
		__('Default Image URL', 'sharethumb'),
		'fsst_get_settings_hidden_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'label_for' => 'default_thumbnail_url',
			'class' => 'sharethumb-settings-row hidden'
		]
	);

	add_settings_field(
		'fsst_logo',
		__('Logo', 'sharethumb'),
		'fsst_get_settings_image_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Logo',
			'label_for' => 'logo',
			'class' => 'sharethumb-settings-row sharethumb-settings-image',
			'url_field_id' => 'logo_url'
		]
	);

	add_settings_field(
		'fsst_logo_url',
		__('Icon URL', 'sharethumb'),
		'fsst_get_settings_hidden_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'label_for' => 'logo_url',
			'class' => 'sharethumb-settings-row hidden'
		]
	);

	add_settings_field(
		'fsst_icon',
		__('Icon', 'sharethumb'),
		'fsst_get_settings_image_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Icon',
			'label_for' => 'icon',
			'class' => 'sharethumb-settings-row sharethumb-settings-image',
			'url_field_id' => 'icon_url'
		]
	);

	add_settings_field(
		'fsst_icon_url',
		__('Icon URL', 'sharethumb'),
		'fsst_get_settings_hidden_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'label_for' => 'icon_url',
			'class' => 'sharethumb-settings-row hidden'
		]
	);

	add_settings_field(
		'fsst_light_theme_font_color',
		__('Light Theme Font Color', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Select Color',
			'label_for' => 'light_theme_font_color',
			'class' => 'sharethumb-settings-row'
		]
	);

	add_settings_field(
		'fsst_light_theme_bg_color',
		__('Light Theme Background Color', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Select Color',
			'label_for' => 'light_theme_bg_color',
			'class' => 'sharethumb-settings-row',
			'include_message_field' => true
		]
	);

	add_settings_field(
		'fsst_dark_theme_font_color',
		__('Dark Theme Font Color', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Select Color',
			'label_for' => 'dark_theme_font_color',
			'class' => 'sharethumb-settings-row'
		]
	);

	add_settings_field(
		'fsst_dark_theme_bg_color',
		__('Dark Theme Background Color', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Select Color',
			'label_for' => 'dark_theme_bg_color',
			'class' => 'sharethumb-settings-row',
			'include_message_field' => true
		]
	);

	add_settings_field(
		'fsst_accent_color',
		__('Accent Color', 'sharethumb'),
		'fsst_get_settings_color_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'placeholder' => 'Select Color',
			'label_for' => 'accent_color',
			'class' => 'sharethumb-settings-row'
		]
	);

	add_settings_field(
		'fsst_post_types',
		__('Overridable Post Types', 'sharethumb'),
		'fsst_get_settings_checkboxes_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'label_for' => 'post_types',
			'class' => 'sharethumb-settings-row checkboxes',
			'options' => fsst_get_overridable_post_types(),
			'description' => 'Any posts that are enabled for overrides will permit the general ShareThumb settings to be overridden for that individual content.  Example: If you enable ShareThumb overrides for posts, any and all posts will have a new sidebar section when you edit it, "ShareThumb Overrides", where you can override the colors, font, theme, logo, and icon.'
		]
	);

	add_settings_field(
		'fsst_delete_on_uinstall',
		__('Delete All Settings on Uninstall', 'sharethumb'),
		'fsst_get_settings_boolean_field_html',
		'fsst_settings',
		'fsst_section_general',
		[
			'label_for' => 'delete_on_uninstall',
			'class' => 'sharethumb-settings-row boolean',
			'description' => 'If enabled, the post-override settings will be deleted when the plugin is uninstalled.  If disabled, post-override settings will be retained for future use.'
		]
	);
}

function fsst_sanitize_configuration($configuration)
{
	if (!empty($configuration['api_key'])) {

		$api_key = $configuration['api_key'];
		$result = fsst_api_validate_key($api_key);

		if ($result['api_key'] === true) {
			add_settings_error('sharethumb_messages', 'sharethumb_message', __('ShareThumb API key is valid.', 'sharethumb'), 'updated');

			// save the configuration to sharethumb			
			$global_config_save_result = fsst_api_save_global_configuration($configuration);
			if ($global_config_save_result === true) {
				add_settings_error('sharethumb_messages', 'sharethumb_message', __('Settings updated on ShareThumb.', 'sharethumb'), 'updated');
			} else {
				if ($global_config_save_result === false) {
					add_settings_error('sharethumb_messages', 'sharethumb_message', __('Error updating settings on ShareThumb.', 'sharethumb'), 'error');
				} else {
					$message = $global_config_save_result . '<pre>' . print_r($global_config_save_result, true) . '</pre>';
					add_settings_error('sharethumb_messages', 'sharethumb_message', __('Error updating settings on ShareThumb.', 'sharethumb') . " {$message}", 'error');
				}
			}

			fsst_set_domain_unverified(false);

		} else if ($result['api_key'] === false) {
			add_settings_error('sharethumb_messages', 'sharethumb_message', __('ShareThumb API key is invalid.', 'sharethumb'), 'error');
			$configuration['api_key'] = '';

			// Check domain verification status
			if ($result['domain'] === false) {
				fsst_set_domain_unverified();
			} else if ($result['domain'] === true) {
				fsst_set_domain_unverified(false);
			}
		} else { // $result['api_key'] === null
			// The call to the API failed, so we don't know the status
			add_settings_error('sharethumb_messages', 'sharethumb_message', __('Error contacting the ShareThumb endpoint.', 'sharethumb'), 'error');
		}
	}

	return $configuration;
}

function fsst_get_settings_boolean_field_html($args)
{
	$configuration = get_option('fsst_settings');
	$field_id = $args['label_for'];
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';
	$field_name = "fsst_settings[{$field_id}]";
	$checked = $field_value ? 'checked' : '';

	$field_description = isset($args['description']) ? $args['description'] : '';
	if ($field_description) {
		$field_description = "<div class='description'>" . wp_kses_post($field_description) . "</div>";
	}

	echo "
			<label>
				<input type='checkbox' name='" . esc_attr($field_name) . "' value='" . esc_attr($field_id) . "' {$checked} />
			</label>" . wp_kses($field_description, ['div' => ['class' => true]]) . "
	";
}

function fsst_get_settings_hidden_field_html($args)
{
	$configuration = get_option('fsst_settings');

	$field_id = $args['label_for'];
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';
	$field_name = "fsst_settings[{$field_id}]";

	echo "
		<input type='hidden' id='field-" . esc_attr($field_id) . "' name='" . esc_attr($field_name) . "' value='" . esc_attr($field_value) . "' />
	";
}

function fsst_get_settings_checkboxes_field_html($args)
{
	$configuration = get_option('fsst_settings');

	$field_id = $args['label_for'];
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';
	if (!is_array($field_value)) $field_value = [];
	$field_name = "fsst_settings[{$field_id}][]";

	$field_description = isset($args['description']) ? $args['description'] : '';
	if ($field_description) {
		$field_description = "<div class='description'>" . wp_kses_post($field_description) . "</div>";
	}

	$field_markup = '';
	foreach ($args['options'] as $key => $label) {
		$checked = in_array($key, $field_value) ? 'checked' : '';
		$field_markup .= "
			<label>
				<input type='checkbox' name='" . esc_attr($field_name) . "' value='" . esc_attr($key) . "' {$checked} />
				" . esc_html($label) . "
			</label>
		";
	}

	$allowed_html = [
		'label' => [],
		'input' => [
			'type' => true,
			'name' => true,
			'value' => true,
			'checked' => true
		],
		'div' => [
			'class' => true
		]
	];
	echo "<div class='column-flex'><div class='options'>" . wp_kses($field_markup, $allowed_html) .
		"</div>" . wp_kses($field_description, $allowed_html) .
		"</div>";
}

function fsst_get_settings_color_field_html($args)
{
	$configuration = get_option('fsst_settings');

	$field_placeholder = $args['placeholder'];
	$field_id = $args['label_for'];
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';
	$field_name = "fsst_settings[{$field_id}]";
	$show_message_field = isset($args['include_message_field']) ? $args['include_message_field'] : false;

	$field_description = isset($args['description']) ? $args['description'] : '';
	if ($field_description) {
		$field_description = "<div class='description'>" . wp_kses_post($field_description) . "</div>";
	}

	$allowed_html = [
		'div' => [
			'class' => true
		]
	];
	echo "<input id='field-" . esc_attr($field_id) .
		"' data-jscolor='{required:false}' name='" . esc_attr($field_name) .
		"' value='" . esc_attr($field_value) .
		"' placeholder='" . esc_attr($field_placeholder) .
		"' />" .
		wp_kses($field_description, $allowed_html) .
		($show_message_field ? "<div class='color-ratio-message'></div>" : '');
}

function fsst_get_settings_image_field_html($args)
{
	$configuration = get_option('fsst_settings');
	$field_id = $args['label_for'];
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : 0;
	$field_name = "fsst_settings[{$field_id}]";
	$url_field_id = $args['url_field_id'];

	$field_description = isset($args['description']) ? $args['description'] : '';
	if ($field_description) {
		$field_description = "<div class='description'>" . wp_kses_post($field_description) . "</div>";
	}

	$field_markup = '';
	if ($field_value) {
		$image_url = wp_get_attachment_image_url($field_value, 'medium');
		if ($image_url) {
			$image_url = $image_url;
			$field_markup = "				
				<span class='button image-upload' title='Upload Image'><img src='" . esc_url($image_url) . "' /></span>
				<span class='image-remove' title='Remove Image'></span>
			";
		}
	} else {
		$field_markup = "
			<span class='button image-upload' title='Upload Image'>Upload Image</span>
			<span class='image-remove' title='Remove Image'></span>
		";
	}

	$allowed_html = [
		'span' => ['class' => true],
		'img' => ['class' => true, 'src' => true],
		'div' => ['class' => true]
	];
	echo
	"<div class='sharethumb-image-container'>" .
		"<input id='field-" . esc_attr($field_id) .
		"' type='hidden' name='" . esc_attr($field_name) .
		"' value='" . esc_attr($field_value) .
		"' data-url-field-id='field-" . esc_attr($url_field_id) .
		"' />" .
		wp_kses($field_markup, $allowed_html) .
		wp_kses($field_description, $allowed_html) .
		"</div>";
}

function fsst_get_settings_select_field_html($args)
{
	$configuration = get_option('fsst_settings');

	$field_id = $args['label_for'];
	$field_placeholder = $args['placeholder'];
	$field_name = "fsst_settings[{$field_id}]";
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';
	$field_options = fsst_get_select_options($args['fetch_options_key']);

	$field_description = isset($args['description']) ? $args['description'] : '';
	if ($field_description) {
		$field_description = "<div class='description'>" . wp_kses_post($field_description) . "</div>";
	}

	$field_options_markup = '';
	foreach ($field_options as $key => $label) {
		$selected = selected($field_value, esc_attr($key), false);
		$field_options_markup .= "<option value='" . esc_attr($key) . "' {$selected}>" . esc_html($label) . "</option>";
	}

	$field_option_none_label = isset($args['post_id']) ? '' : '';
	$field_option_none = ($field_value) ? "<option value=''>" . esc_attr($field_option_none_label) . "</option>" : "<option value='' selected>" . esc_attr($field_option_none_label) . "</option>";

	$allowed_html = [
		'option' => [
			'value' => true,
			'selected' => true
		]
	];

	echo "<select id='field-" . esc_attr($field_id) .
		"' class='select2' name='" . esc_attr($field_name) .
		"' data-placeholder='" . esc_attr($field_placeholder) .
		"'>" . wp_kses($field_option_none, $allowed_html) .
		wp_kses($field_options_markup, $allowed_html) .
		"</select>";

	if ($args['fetch_options_key'] == 'theme') {
		echo "<img id='field-theme-preview' />";
	}
}

function fsst_get_settings_text_field_html($args)
{
	$configuration = get_option('fsst_settings');

	$field_id = $args['label_for'];
	$field_placeholder = $args['placeholder'];
	$field_name = "fsst_settings[{$field_id}]";
	$field_value = isset($configuration[$field_id]) ? $configuration[$field_id] : '';

	$field_description = isset($args['description']) ? $args['description'] : '';
	if ($field_description) {
		$field_description = "<div class='description'>" . wp_kses_post($field_description) . "</div>";
	}

	$allowed_html = [
		'div' => [
			'class' => true
		]
	];
	echo "<input id='field-" . esc_attr($field_id) .
		"' type='text' name='" . esc_attr($field_name) .
		"' placeholder='" . esc_attr($field_placeholder) .
		"' value='" . esc_attr($field_value) .
		"' />" . wp_kses($field_description, $allowed_html);
}

// Not doing anything with this
function fsst_get_settings_general_section_html($args) {}

// Render the global settings page
function fsst_render_settings_page_html()
{
	if (!current_user_can('manage_options')) {
		return;
	}

	if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'sharethumb_metabox') && isset($_GET['settings-updated'])) {
		add_settings_error('sharethumb_messages', 'sharethumb_message', __('Settings saved locally.', 'sharethumb'), 'updated');
	}
	settings_errors('sharethumb_messages');

	$page_title = esc_html(get_admin_page_title());
	echo "
		<div class='wrap'>
			<h1>" . esc_html($page_title) . "</h1>
			<form action='options.php' method='post'>
	";

	settings_fields('fsst_settings');
	do_settings_sections('fsst_settings');
	submit_button('Save Settings');

	echo "
			</form>
		</div>
		<p>Have questions about ShareThumb? Please <a href='mailto:support@4sitestudios.com'>email us</a> and we will get back to you within 24 business hours.</p>
	";
}

// Render the preview page
function fsst_render_preview_page_html()
{
	echo "
	<iframe src='https://4site-interactive-studios.github.io/sharethumb-compare/' style='width: 100%; height: 100vh; border: none;'></iframe>
	";
}

// Render the upgrade page
function fsst_render_upgrade_page_html() {
	echo "<script>window.location.replace('https://app.sharethumb.io/sign-up');</script>";
}

// Saving the options in a transient for 10 minutes.
// No need to constantly request the options if we don't need to, during a single configuration session.
function fsst_get_select_options($name)
{
	$choices = get_transient("fsst_{$name}_choices");
	if (empty($choices)) {
		$configuration = get_option('fsst_settings');
		$api_key = isset($configuration['api_key']) ? $configuration['api_key'] : '';
		$choices = fsst_api_fetch_options($name, $api_key);
		if (!empty($choices)) {
			set_transient("fsst_{$name}_choices", $choices, 600);
		}
	}
	return $choices;
}

// Fetches the Sharethumb preview thumbnail from the API
function fsst_get_preview_thumbnail()
{
	if (!wp_verify_nonce($_GET['nonce'], "fsst_preview_thumbnail")) {
		exit("Nonce invalid");
	}

	$configuration = get_option('fsst_settings');
	$configuration_overrides = fsst_sanitize_post_overrides($_POST);
	foreach ($configuration as $key => $value) {
		if (!empty($configuration_overrides[$key])) {
			$configuration[$key] = $configuration_overrides[$key];
		}
	}

	$response = fsst_api_get_preview_image($configuration);
	wp_send_json(['result' => $response, 'debug' => $_POST]);
}