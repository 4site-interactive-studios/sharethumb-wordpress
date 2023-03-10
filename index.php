<?php
/**
 * Plugin Name: ShareThumb Wordpress Plugin
 * Version: 1.0.2
 * Plugin URI: TBD
 * Description: TBD
 * Author: 4Site Interactive Studios
 * Author URI: https://4sitestudios.com
 */

if(!defined('ABSPATH')) { exit; }

define('FSST_FONT_URL', 'https://api.sharethumb.app/fonts');
define('FSST_THEME_URL', 'https://api.sharethumb.app/themes');
define('FSST_SETTINGS_URL', 'https://og.sharethumb.app/save-settings');
define('FSST_REGENERATE_THUMBNAIL_URL', 'https://og.sharethumb.app/regenerate-thumb');
define('FSST_GET_THUMBNAIL_ID_URL', 'https://og.sharethumb.app/get-thumb-id');
define('FSST_IS_VALID_API_KEY_URL', 'https://og.sharethumb.app/validate-api-key');

// This base URL must end in a slash
define('FSST_IMAGE_BASE_URL', 'https://og.sharethumb.app/og/');


/**
 *  Metatag Management
 * 
 * Here, we add a wp_head action, get the saved ShareThumb configuration, and output the tags.
 * We also a wp_head filter, removing select metatags if they exist so that we can use our own.
 * 
 **/

// Output the necessary metatags to support sharethumb
add_action('wp_head', function() {
	$st_config = fsst_get_configuration();

	echo "\n";
	if(!empty($st_config['dv_code']) && is_front_page()) {
		echo "<meta name='sharethumb' content='{$st_config['dv_code']}'>\n";
	}
	if(!empty($st_config['logo_url'])) {
		echo "<meta property='st:logo' content='{$st_config['logo_url']}'>\n";
	}
	if(!empty($st_config['icon_url'])) {		
		echo "<meta property='st:icon' content='{$st_config['icon_url']}'>\n";
	}
	if(!empty($st_config['font'])) {
		echo "<meta property='st:font' content='{$st_config['font']}'>\n";
	}
	if(!empty($st_config['theme'])) {
		echo "<meta property='st:theme' content='{$st_config['theme']}'>\n";
	}
	if(!empty($st_config['foreground'])) {
		echo "<meta property='st:font_color' content='{$st_config['foreground']}'>\n";
	}
	if(!empty($st_config['background'])) {
		echo "<meta property='st:background_color' content='{$st_config['background']}'>\n";
	}
	if(!empty($st_config['accent'])) {
		echo "<meta property='st:accent_color' content='{$st_config['accent']}'>\n";
	}
	if(!empty($st_config['secondary'])) {
		echo "<meta property='st:secondary_color' content='{$st_config['secondary']}'>\n";
	}

	$featured_image_url = get_the_post_thumbnail_url(null, 'large');
	if($featured_image_url) {
		echo "<meta property='st:image' content='{$featured_image_url}'>\n";
	}


	global $wp;
	$page_url = home_url($wp->request);
	$page_url = preg_replace("(^https?://)", "", $page_url);
	// Add a random number to the end of the URL to force a refresh of the image
	$page_url .= '?' . rand(1000, 999999);

	$page_title = fsst_get_page_title();
	$image_base_url = FSST_IMAGE_BASE_URL;

	// We remove the original metatags in the wp_head filter
	echo "<meta name='twitter:title' content='{$page_title}'>\n";
	echo "<meta name='twitter:image' content='{$image_base_url}{$page_url}'>\n";
	echo "<meta name='twitter:card' content='summary_large_image'>\n";
	echo "<meta property='og:title' content='{$page_title}'>\n";
	echo "<meta property='og:image' content='{$image_base_url}{$page_url}'>\n";
	echo "<meta property='og:image:width' content='1200' />\n";
	echo "<meta property='og:image:height' content='630' />\n";
}, 0);

// Mostly copied from wp_title().  Created my own version in case that function gets deprecated (there's a warning about it in the WP docs)
function fsst_get_page_title() {
	$sep = ' ??? ';
	$seplocation = 'right';

	if(is_front_page()) {
		return get_bloginfo('name') . $sep . get_bloginfo('description');
	}

	global $wp_locale;

	$m        = get_query_var('m');
	$year     = get_query_var('year');
	$monthnum = get_query_var('monthnum');
	$day      = get_query_var('day');
	$search   = get_query_var('s');
	$title    = '';

	$t_sep = '%WP_TITLE_SEP%'; // Temporary separator, for accurate flipping, if necessary.

	if(is_single() ||(is_home() && ! is_front_page()) ||(is_page() && ! is_front_page())) {
		$title = single_post_title('', false);
	}

	if(is_post_type_archive()) {
		$post_type = get_query_var('post_type');
		if(is_array($post_type)) {
			$post_type = reset($post_type);
		}
		$post_type_object = get_post_type_object($post_type);
		if(! $post_type_object->has_archive) {
			$title = post_type_archive_title('', false);
		}
	}

	if(is_category() || is_tag()) {
		$title = single_term_title('', false);
	}

	if(is_tax()) {
		$term = get_queried_object();
		if($term) {
			$tax   = get_taxonomy($term->taxonomy);
			$title = single_term_title($tax->labels->name . $t_sep, false);
		}
	}

	if(is_author() && ! is_post_type_archive()) {
		$author = get_queried_object();
		if($author) {
			$title = $author->display_name;
		}
	}

	if(is_post_type_archive() && $post_type_object->has_archive) {
		$title = post_type_archive_title('', false);
	}

	if(is_archive() && ! empty($m)) {
		$my_year  = substr($m, 0, 4);
		$my_month = substr($m, 4, 2);
		$my_day   =(int) substr($m, 6, 2);
		$title    = $my_year .
			($my_month ? $t_sep . $wp_locale->get_month($my_month) : '') .
			($my_day ? $t_sep . $my_day : '');
	}

	if(is_archive() && ! empty($year)) {
		$title = $year;
		if(! empty($monthnum)) {
			$title .= $t_sep . $wp_locale->get_month($monthnum);
		}
		if(! empty($day)) {
			$title .= $t_sep . zeroise($day, 2);
		}
	}

	if(is_search()) {
		/* translators: 1: Separator, 2: Search query. */
		$title = sprintf(__('Search Results %1$s %2$s'), $t_sep, strip_tags($search));
	}

	if(is_404()) {
		$title = __('Page not found');
	}

	// removing the separator
	$sep = '';
	$prefix = '';
	if(! empty($title)) {
		$prefix = " $sep ";
	}

	$title_array = apply_filters('wp_title_parts', explode($t_sep, $title));

	if('right' === $seplocation) { // Separator on right, so reverse the order.
		$title_array = array_reverse($title_array);
		$title       = implode(" $sep ", $title_array) . $prefix;
	} else {
		$title = $prefix . implode(" $sep ", $title_array);
	}

	$title = apply_filters('wp_title', $title, $sep, $seplocation);

	return trim($title);
}

// Capture all the metatags output by other plugins and WP Core, and then remove the metatags
// we replace in the wp_head action.
add_filter('wp_head', function() {
	ob_start();
}, 0);

add_filter('wp_head', function() {
	$output = ob_get_clean();

	// Remove og:title, og:image, og:image:height, og:image:width
	$output = preg_replace('/<meta property=["\']og:(?:title|image[^\s]*)["\'] content=[\'"].*[\'"][ \/>]*/', "", $output);

	// Remove twitter:card, twitter:title, twitter:image, twitter:image:width, twitter:image:height metatags
	$output = preg_replace('/<meta name=["\']twitter:(?:card|title|image[^\s]*)["\'] content=[\'"].*[\'"][ \/>]*/', "", $output);	

	echo $output;
}, PHP_INT_MAX);




/**
 * Regenerate thumbnails whenever a page/post title changes
 * 
 **/

add_action('post_updated', function($post_ID, $post_after, $post_before){
	if($post_after->post_title !== $post_before->post_title) {
		$configuration = fsst_get_configuration();
		$thumbnail_id = fsst_api_get_thumbnail_id($configuration, get_the_permalink($post_ID));
		if($thumbnail_id) {
			fsst_api_regenerate_thumbnail($configuration, $thumbnail_id);
		}
	}
}, PHP_INT_MAX, 3);



/**
 *  Configuration Save & Load Functions
 * 
 *  Here, we have the functions responsible for saving and loading the selected options (stored as a WP option).
 *  We filter out any option names we aren't expecting, and on save, we save time by storing the logo & icon URLs,
 *  so that we don't have to do it on every page load.
 * 
 **/

// Any keys not present here will be removed on save
function fsst_get_default_configuration() {
	return [
		'api_key' => '',	// ShareThumb API key
		'dv_code' => '',	// ShareThumb Domain Verification code
		'logo' => 0,		// image ID
		'icon' => 0,		// image ID
		'theme' => '',		// selected from a list of options provided by the ShareThumb API
		'font' => '',		// selected from a list of options provided by the ShareThumb API
		'foreground' => '',	// text color
		'background' => '',	// background color
		'accent' => '',		// accent color
		'secondary' => '',	// secondary color
		'icon_url' => '',	// only populated when saving the configuration set
		'logo_url' => '',	// only populated when saving the configuration set
		'plan' => '',		// stores the subscribed sharethumb plan -- populated via frontend with a call to the sharethumb API during save
	];
}

function fsst_save_configuration($configuration) {
	$default_configuration = fsst_get_default_configuration();
	
	// Remove any key:value pairs we don't want to save
	$configuration = array_intersect_key($configuration, $default_configuration);

	// Ensure we have all keys
	$configuration = array_replace($default_configuration, $configuration);

	// Save the image URLs so that we don't have to look it up every time
	$configuration['logo_url'] = ($configuration['logo']) ? wp_get_attachment_image_url($configuration['logo'], 'large') : '';
	$configuration['icon_url'] = ($configuration['icon']) ? wp_get_attachment_image_url($configuration['icon'], 'large') : '';

	if($configuration['api_key']) {
		$validateApiKey = fsst_api_validate_api_key($configuration['api_key']);
		if($validateApiKey['statusCode'] === 200 && $validateApiKey['plan']) {
			$configuration['plan'] = $validateApiKey['plan'];
			update_option('fsst_configuration', $configuration);
			fsst_api_save_configuration($configuration);
			return '';
		}
		return $validateApiKey['message'];
	}
	update_option('fsst_configuration', $configuration);
}

function fsst_get_configuration() {
	$default_configuration = fsst_get_default_configuration();
	$configuration = array_replace($default_configuration, get_option('fsst_configuration', []));
	return $configuration;
}




/**
 *  Settings Page
 * 
 *  We add an admin_menu action, to add a ShareThumb Settings menu item on the backend.
 *  We add an admin_enqueue_scripts action, to enqueue all of the scripts & styles we require.
 *  Also present are a number of helper functions.
 * 
 *  Also relevant are settings-page.php, settings-page.js, settings-page.css which are in this same folder.
 * 
 **/

// Add our menu option; SVG borrowed from fontawesome (free icon)
add_action('admin_menu', function() {
	add_menu_page(
		__('Settings', 'fsst'),
		__('ShareThumb', 'fsst'),
		'manage_options',
		'sharethumb',
		'fsst_admin_page_settings',
		'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48IS0tISBGb250IEF3ZXNvbWUgUHJvIDYuMi4xIGJ5IEBmb250YXdlc29tZSAtIGh0dHBzOi8vZm9udGF3ZXNvbWUuY29tIExpY2Vuc2UgLSBodHRwczovL2ZvbnRhd2Vzb21lLmNvbS9saWNlbnNlIChDb21tZXJjaWFsIExpY2Vuc2UpIENvcHlyaWdodCAyMDIyIEZvbnRpY29ucywgSW5jLiAtLT48cGF0aCBkPSJNMzEzLjQgMzIuOWMyNiA1LjIgNDIuOSAzMC41IDM3LjcgNTYuNWwtMi4zIDExLjRjLTUuMyAyNi43LTE1LjEgNTIuMS0yOC44IDc1LjJINDY0YzI2LjUgMCA0OCAyMS41IDQ4IDQ4YzAgMjUuMy0xOS41IDQ2LTQ0LjMgNDcuOWM3LjcgOC41IDEyLjMgMTkuOCAxMi4zIDMyLjFjMCAyMy40LTE2LjggNDIuOS0zOC45IDQ3LjFjNC40IDcuMiA2LjkgMTUuOCA2LjkgMjQuOWMwIDIxLjMtMTMuOSAzOS40LTMzLjEgNDUuNmMuNyAzLjMgMS4xIDYuOCAxLjEgMTAuNGMwIDI2LjUtMjEuNSA0OC00OCA0OEgyOTQuNWMtMTkgMC0zNy41LTUuNi01My4zLTE2LjFsLTM4LjUtMjUuN0MxNzYgNDIwLjQgMTYwIDM5MC40IDE2MCAzNTguM1YzMjAgMjcyIDI0Ny4xYzAtMjkuMiAxMy4zLTU2LjcgMzYtNzVsNy40LTUuOWMyNi41LTIxLjIgNDQuNi01MSA1MS4yLTg0LjJsMi4zLTExLjRjNS4yLTI2IDMwLjUtNDIuOSA1Ni41LTM3Ljd6TTMyIDE5Mkg5NmMxNy43IDAgMzIgMTQuMyAzMiAzMlY0NDhjMCAxNy43LTE0LjMgMzItMzIgMzJIMzJjLTE3LjcgMC0zMi0xNC4zLTMyLTMyVjIyNGMwLTE3LjcgMTQuMy0zMiAzMi0zMnoiIGZpbGw9ImN1cnJlbnRDb2xvciIvPjwvc3ZnPg=='
	);
});

function fsst_admin_page_settings() {
	$api_error = '';
	if(!empty($_POST)) {
		$api_error .= fsst_save_configuration($_POST);
	}
	include 'settings-page.php';	
}

// Enqueue the scripts & styles for our settings page
add_action("admin_enqueue_scripts", function($hook) {
	if($hook === 'toplevel_page_sharethumb') {
		wp_enqueue_media();
		wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery']);
		wp_enqueue_script('jscolor', 'https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.5.1/jscolor.min.js');
		wp_enqueue_script('settings-page-js', plugins_url('settings-page.js', __FILE__), ['jquery', 'jscolor', 'select2']);
		wp_enqueue_style('settings-page-css', plugins_url('settings-page.css', __FILE__));
	}
});

// Fetch the drop-down options from the ShareThumb public endpoint
function fsst_fetch_options($name) {
	$options = [];
	if($name === 'font') {
		$json_encoded_data = file_get_contents(FSST_FONT_URL);
		if($json_encoded_data) {
			$font_names = json_decode($json_encoded_data);
			foreach($font_names as $font_name) {
				$options[$font_name] = $font_name;
			}
		}
	} else if($name === 'theme') {
		$json_encoded_data = file_get_contents(FSST_THEME_URL);
		if($json_encoded_data) {
			$themes = json_decode($json_encoded_data);
			foreach($themes as $theme) {
				$options[$theme->name] = $theme->key;
			}
		}
	}
	return $options;
}

// Saving the options in a transient for 10 minutes.
// No need to constantly request the options if we don't need to, during a single configuration session.
function fsst_get_select_options($name) {
	$choices = get_transient("st_{$name}_choices");
	if(empty($choices)) {
		$choices = fsst_fetch_options($name);
		if(!empty($choices)) {
			set_transient("st_{$name}_choices", $choices, 600);
		}
	}
	return $choices;
}

function fsst_get_text_field($field_label, $field_name, $configuration) {
	$field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : '';
	return "
		<div class='input-wrapper'>
			<label for='field-{$field_name}'>{$field_label}</label>
			<input id='field-{$field_name}' type='text' name='{$field_name}' value='{$field_value}' />
		</div>
	";
}

function fsst_get_image_field($field_label, $field_name, $configuration) {
	$field_markup = '';
	$field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : 0;
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

	return "
		<div class='input-wrapper'>
			<label for='field-{$field_name}'>{$field_label}</label>
			{$field_markup}
			<input id='field-{$field_name}' type='hidden' name='{$field_name}' value='{$field_value}' />
		</div>
	";
}

function fsst_get_hidden_field($field_name, $configuration) {
	$field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : '';
	return "
		<div class='input-wrapper'>
			<input id='field-{$field_name}' type='hidden' name='{$field_name}' value='{$field_value}' />
		</div>
	";
}

function fsst_get_select_field($field_label, $field_name, $configuration) {
	$field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : '';
	$options = fsst_get_select_options($field_name);

	// If, for whatever reason the ShareThumb API isn't reachable, let's just show a text field
	if(!is_array($options) || !count($options)) {

		return "
			<div class='input-wrapper'>
				<label for='field-{$field_name}'>{$field_label}</label>
				<input id='field-{$field_name}' type='text' name='{$field_name}' value='{$field_value}' />
			</div>
		";

	} else {

		$options_markup = '';
		foreach($options as $key => $label) {
			if($field_value == $key) {
				$options_markup .= "<option value='{$key}' selected>{$label}</option>";
			} else {
				$options_markup .= "<option value='{$key}'>{$label}</option>";
			}
		}
		$option_none = ($field_value) ? "<option value=''>None</option>" : "<option value='' selected>None</option>";
		return "
			<div class='input-wrapper'>
				<label for='field-{$field_name}'>{$field_label}</label>
				<select id='field-{$field_name}' name='{$field_name}' data-placeholder='{$field_label}'>
					{$option_none}
					{$options_markup}
				}
				</select>
			</div>
		";

	}
}

// Functionality for this field is supplemented by the jscolor script
function fsst_get_color_picker_field($field_label, $field_name, $configuration) {
	$field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : '';
	return "
		<div class='input-wrapper'>
			<label for='field-{$field_name}'>{$field_label}</label>			 
			<input id='field-{$field_name}' data-jscolor='{required:false}' name='{$field_name}' value='{$field_value}' />
		</div>
	";
}


/**
 *  ShareThumb API Interactions
 * 
 **/

 function fsst_api_validate_api_key($api_key) {
	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => FSST_IS_VALID_API_KEY_URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => [
			'sharethumb-api-key: ' . $api_key,
			'Content-Type: application/json',
		],
	]);

	$response = curl_exec($ch);
	curl_close($ch);
	$response = json_decode($response, true);

	return $response;
}


function fsst_api_get_thumbnail_id($configuration, $post_url) {
	// don't bother if we don't have an API Key
	if(empty($configuration['api_key'])) {
		return;
	}

	$query_string = http_build_query(['url' => $post_url]);
	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => FSST_GET_THUMBNAIL_ID_URL . '?' . $query_string,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => [
			'sharethumb-api-key: ' . $configuration['api_key']
		]
	]);
	$response = curl_exec($ch);
	curl_close($ch);

	if($response) {
		$encoded_response = json_decode($response);
		if($encoded_response->statusCode == 200 && isset($encoded_response->id)) {
			return $encoded_response->id;
		}
	}

	return false;
}

function fsst_api_regenerate_thumbnail($configuration, $thumbnail_id) {
	// don't bother if we don't have an API Key
	if(empty($configuration['api_key'])) {
		return;
	}

	$ch = curl_init();
	$opts = [
		CURLOPT_URL => FSST_REGENERATE_THUMBNAIL_URL . '/' . $thumbnail_id,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CUSTOMREQUEST => 'PUT',
		CURLOPT_HTTPHEADER => [
			'sharethumb-api-key: ' . $configuration['api_key']
		]
	];
	
	curl_setopt_array($ch, $opts);
	$response = curl_exec($ch);
	curl_close($ch);
	
	if($response && !empty($response['statusCode']) && $response['statusCode']) {
		return true;
	}
	return false;
}

function fsst_api_save_configuration($configuration) {
	// don't bother if we don't have an API Key
	if(empty($configuration['api_key'])) {
		return;
	}

	// remove empty keys per sharethumb API requirements
	foreach($configuration as $key => $value) {
		if(!$value) {
			unset($configuration[$key]);
		}
	}

	// remove unwated fields, and update fields to their preferred type of value (WP File ID to WP File URL, notably)
	$api_key = $configuration['api_key'];
	unset($configuration['api_key']);
	if(isset($configuration['plan'])) {
		unset($configuration['plan']);
	}
	if(isset($configuration['dv_code'])) {
		unset($configuration['dv_code']);
	}
	if(isset($configuration['logo_url'])) {
		$configuration['logo'] = $configuration['logo_url'];
		unset($configuration['logo_url']);
	}
	if(isset($configuration['icon_url'])) {
		$configuration['icon'] = $configuration['icon_url'];
		unset($configuration['icon_url']);
	}

	$json_configuration = json_encode($configuration);
	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => FSST_SETTINGS_URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CUSTOMREQUEST => 'PUT',
		CURLOPT_POSTFIELDS => $json_configuration,
		CURLOPT_HTTPHEADER => [
			'sharethumb-api-key: ' . $api_key,
			'Content-Type: application/json'
		]
	]);
	$response = curl_exec($ch);
	curl_close($ch);
}