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
 * 
 **/

if(!defined('ABSPATH')) { exit; }


function fsst_api_get_permitted_keys() {
	return [
		'logo', 
		'icon', 
		'theme', 
		'theme_custom', 
		'font',
		'font_color',
		'background_color',
		'accent_color',
		'secondary_color',
		'title'
	];
}

function fsst_api_validate_key($api_key) {
	if(!$api_key) {
		return false;
	}

	$response = wp_remote_get(FSST_VALIDATE_KEY_URL, ['headers' => ['sharethumb-api-key' => $api_key]]);
	if($response && !is_wp_error($response)) {
		$encoded_response = json_decode(wp_remote_retrieve_body($response));
		if($encoded_response->statusCode == 200 && isset($encoded_response->isValid) && $encoded_response->isValid) {
			return true;
		}
	}

	return false;
}

// Fetch the drop-down options from the ShareThumb public endpoint
function fsst_api_fetch_options($name) {
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

function fsst_api_get_thumbnail_id($api_key, $post_url) {
	// don't bother if we don't have an API Key or a post_url
	if(!$api_key || !$post_url) {
		return;
	}

	$query_string = http_build_query(['url' => $post_url]);
	$response = wp_remote_get(FSST_GET_THUMBNAIL_ID_URL . '?' . $query_string, [
		'headers' => [
			'sharethumb-api-key' => $api_key
		]
	]);

	if($response && !is_wp_error($response)) {
		$encoded_response = json_decode(wp_remote_retrieve_body($response));
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

	$api_key = $configuration['api_key'];

	if(isset($configuration['logo_url'])) {
		$configuration['logo'] = $configuration['logo_url'];
		unset($configuration['logo_url']);
	}
	if(isset($configuration['icon_url'])) {
		$configuration['icon'] = $configuration['icon_url'];
		unset($configuration['icon_url']);
	}

	$permitted_keys = fsst_api_get_permitted_keys();
	foreach($configuration as $key => $value) {
		if(!in_array($key, $permitted_keys)) {
			unset($configuration[$key]);
		}
	}

	$json_configuration = json_encode($configuration);
	$response = wp_remote_post(FSST_REGENERATE_THUMBNAIL_URL . '/' . $thumbnail_id, [
		'method' => 'PUT',
		'headers' => [
			'sharethumb-api-key' => $api_key,
			'Content-Type' => 'application/json'
		],
		'body' => $json_configuration
	]);
	
	if($response && !is_wp_error($response)) {
		$encoded_response = json_decode(wp_remote_retrieve_body($response));
		if(!empty($encoded_response->statusCode) && $encoded_response->statusCode == 200) {
			return true;
		}
	}

	return false;
}

function fsst_api_save_global_configuration($configuration) {
	// don't bother if we don't have an API Key
	if(empty($configuration['api_key'])) {
		return false;
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
	if(isset($configuration['post_types'])) {
		unset($configuration['post_types']);
	}

	$json_configuration = json_encode($configuration);
	$response = wp_remote_post(FSST_SETTINGS_URL, [
		'method' => 'PUT',
		'headers' => [
			'sharethumb-api-key' => $api_key,
			'Content-Type' => 'application/json'
		],
		'body' => $json_configuration
	]);

	if($response && !is_wp_error($response)) {
		$encoded_response = json_decode(wp_remote_retrieve_body($response));

		if(!empty($encoded_response->statusCode) && $encoded_response->statusCode == 200) {
			return true;
		} else if(!empty($encoded_response->message)) {
			return $encoded_response->message;
		}
	}

	return false;
}