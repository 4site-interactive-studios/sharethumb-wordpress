<?php

/**
 *  ShareThumb API Interactions
 * 
 * fsst_api_validate_key : validates the provided API key
 * fsst_api_fetch_options : returns all the available fonts or themes that are valid for use in the configuration
 * fsst_api_get_thumbnail_id : returns the ID for a thumbnail for a particular page
 * fsst_api_regenerate_thumbnail : regenerates the thumbnail for a particular page
 * fsst_api_save_global_configuration : updates the configuration on the ShareThumb website to match the locally-saved configuration
 * fsst_api_get_permitted_keys : helper function returning the keys permitted for calls to the API
 * fsst_api_get_preview_image : returns the preview image for a particular configuration
 * 
 **/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}


function fsst_api_get_permitted_keys()
{
	return [
		'logo',
		'icon',
		'default_thumbnail',
		'theme',
		'theme_custom',
		'font',
		'highlight_font',
		'dark_theme_font_color',
		'dark_theme_bg_color',
		'light_theme_font_color',
		'light_theme_bg_color',
		'accent_color',
		'title'
	];
}

function fsst_api_validate_key($api_key)
{
	if (!$api_key) {
		return ['api_key' => false, 'domain' => null];
	}

	$response = wp_remote_get(FSST_VALIDATE_KEY_URL, ['headers' => ['sharethumb-api-key' => $api_key]]);
	if ($response && !is_wp_error($response)) {
		$decoded_response = json_decode(wp_remote_retrieve_body($response));
		$status_code = $decoded_response->statusCode;
		$is_valid = isset($decoded_response->isValid) ? $decoded_response->isValid : false;

		if ($status_code >= 400 && $status_code < 500) {
			return ['api_key' => true, 'domain' => false];
		} else if ($status_code == 200 && $is_valid) {
			return ['api_key' => true, 'domain' => true];
		}
	}

	return ['api_key' => null, 'domain' => null];
}

// Fetch the drop-down options from the ShareThumb public endpoint
function fsst_api_fetch_options($name, $api_key = '')
{
	$options = [];
	if ($name === 'font' || $name === 'highlight_font') {
		$response = wp_remote_get(FSST_FONT_URL);
		if (!is_wp_error($response)) {
			$json_encoded_data = wp_remote_retrieve_body($response);
			$font_names = json_decode($json_encoded_data);
			foreach ($font_names as $font_name) {
				$options[$font_name] = $font_name;
			}
		}
	} else if ($name === 'theme') {
		// this endpoint supports an optional API Key to pull down private themes
		$endpoint_url = FSST_THEME_URL;
		if ($api_key) {
			$query_string = http_build_query(['sharethumb-api-key' => $api_key]);
			$endpoint_url .= str_contains($endpoint_url, '?') ? '&' : '?';
			$endpoint_url .= $query_string;
		}

		$response = wp_remote_get($endpoint_url);
		if (!is_wp_error($response)) {
			$json_encoded_data = wp_remote_retrieve_body($response);
			$themes = json_decode($json_encoded_data);
			foreach ($themes as $theme) {
				$options[$theme->key] = $theme->name;
			}
		}
	}
	return $options;
}

function fsst_api_get_thumbnail_id($api_key, $post_url)
{
	// don't bother if we don't have an API Key or a post_url
	if (!$api_key || !$post_url) {
		return;
	}

	$query_string = http_build_query(['url' => $post_url]);
	$response = wp_remote_get(FSST_GET_THUMBNAIL_ID_URL . '?' . $query_string, [
		'headers' => [
			'sharethumb-api-key' => $api_key
		]
	]);

	if ($response && !is_wp_error($response)) {
		$decoded_response = json_decode(wp_remote_retrieve_body($response));
		if ($decoded_response->statusCode == 200 && isset($decoded_response->id)) {
			return $decoded_response->id;
		}
	}

	return false;
}

function fsst_api_regenerate_thumbnail($configuration, $thumbnail_id)
{
	if (empty($configuration['api_key'])) {
		return false;
	}
	$api_key = $configuration['api_key'];
	$configuration = fsst_prepare_configuration_for_api($configuration);

	$json_configuration = wp_json_encode($configuration);
	$response = wp_remote_post(FSST_REGENERATE_THUMBNAIL_URL . '/' . $thumbnail_id, [
		'method' => 'PUT',
		'headers' => [
			'sharethumb-api-key' => $api_key,
			'Content-Type' => 'application/json'
		],
		'body' => $json_configuration
	]);

	if ($response && !is_wp_error($response)) {
		$decoded_response = json_decode(wp_remote_retrieve_body($response));
		if (!empty($decoded_response->statusCode) && $decoded_response->statusCode == 200) {
			return true;
		}
	}

	return false;
}

function fsst_api_save_global_configuration($configuration)
{
	if (empty($configuration['api_key'])) {
		return false;
	}
	$api_key = $configuration['api_key'];
	$configuration = fsst_prepare_configuration_for_api($configuration);

	$json_configuration = wp_json_encode($configuration);
	$response = wp_remote_post(FSST_SETTINGS_URL, [
		'method' => 'PUT',
		'headers' => [
			'sharethumb-api-key' => $api_key,
			'Content-Type' => 'application/json'
		],
		'body' => $json_configuration
	]);

	if (is_wp_error($response)) {
		return $response->get_error_message();
	} else if ($response) {
		$decoded_response = json_decode(wp_remote_retrieve_body($response));
		if (!empty($decoded_response->statusCode) && $decoded_response->statusCode == 200) {
			return true;
		} else if (!empty($decoded_response->message)) {
			return $decoded_response->message;
		}
	}

	return false;
}

function fsst_prepare_configuration_for_api($configuration)
{
	// update fields to their preferred type of value (WP File ID to WP File URL, notably)
	if (isset($configuration['logo_url'])) {
		$configuration['logo'] = $configuration['logo_url'];
		unset($configuration['logo_url']);
	}
	if (isset($configuration['icon_url'])) {
		$configuration['icon'] = $configuration['icon_url'];
		unset($configuration['icon_url']);
	}
	if (isset($configuration['default_thumbnail_url'])) {
		$configuration['default_thumbnail'] = $configuration['default_thumbnail_url'];
		unset($configuration['default_thumbnail_url']);
	}

	// remove empty keys per sharethumb API requirements; and remove any keys that are not permitted
	$permitted_keys = fsst_api_get_permitted_keys();
	foreach ($configuration as $key => $value) {
		if (!in_array($key, $permitted_keys) || !$value) {
			unset($configuration[$key]);
		}
	}

	return $configuration;
}

function fsst_api_get_preview_image($configuration)
{
	if (empty($configuration['api_key'])) {
		return false;
	}

	$api_key = $configuration['api_key'];
	$configuration = fsst_prepare_configuration_for_api($configuration);

	$url = FSST_PREVIEW_SETTINGS_URL . '/' . !empty($configuration['theme']) ? $configuration['theme'] : 'default';
	$json_configuration = wp_json_encode($configuration);

	$response = wp_remote_post($url, [
		'headers' => [
			'Content-Type' => 'application/json',
			'ShareThumb-Api-Key' => $api_key
		],
		'body' => $json_configuration
	]);

	if ($response && !is_wp_error($response)) {
		$image_blob = wp_remote_retrieve_body($response);
		return base64_encode($image_blob);
	}

	return false;
}
