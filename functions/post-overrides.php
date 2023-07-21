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

if(!defined('ABSPATH')) { exit; }

add_action('add_meta_boxes',		'fsst_add_post_override_boxes', 10, 2);
add_action('save_post',				'fsst_save_post_override_configuration', PHP_INT_MAX, 3);

function fsst_get_post_configuration($post_id) {
	$post_configuration = get_post_meta($post_id, 'sharethumb');

	if(is_array($post_configuration[0])) {
		$post_configuration = $post_configuration[0];
	} else if(!is_array($post_configuration)) {
		$post_configuration = [];
	}

	$global_configuration = get_option('sharethumb_options');
	$post_configuration['api_key_set'] = !empty($global_configuration['api_key']);

	return $post_configuration;
}

function fsst_add_post_override_boxes($post_type, $post) {
	$configuration = get_option('sharethumb_options');
	if(is_array($configuration['post_types']) && count($configuration['post_types'])) {
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

function fsst_render_metabox_html($post) {
	wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
	wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery']);
	wp_enqueue_script('jscolor', 'https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.5.1/jscolor.min.js');
	wp_enqueue_script('settings-page-js', plugins_url('../settings-page.js', __FILE__), ['jquery', 'jscolor', 'select2'], '1.0');
	wp_enqueue_style('settings-page-css', plugins_url('../settings-page.css', __FILE__), [], '1.0');

	ob_start();
	include FSST_PLUGIN_PATH . '/template-post-override-settings.php';
	echo ob_get_clean();
}


function fsst_save_post_override_configuration($post_id, $post, $update) {
	if(empty($_POST['sharethumb_nonce']) || !wp_verify_nonce($_POST['sharethumb_nonce'], 'sharethumb_metabox')) {
		return $post_id;
	}
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	if('page' == $_POST['post_type']) {
		if(!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} else {
		if(!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
	}

	$default_configuration = fsst_get_default_post_configuration();
	$post_configuration = [];

	foreach($default_configuration as $key => $value) {		
		if(isset($_POST[$key])) {
			$post_configuration[$key] = $_POST[$key];
		} else {
			$post_configuration[$key] = $value;
		}
	}

	$post_configuration['logo_url'] = ($post_configuration['logo']) ? wp_get_attachment_image_url($post_configuration['logo'], 'large') : '';
	$post_configuration['icon_url'] = ($post_configuration['icon']) ? wp_get_attachment_image_url($post_configuration['icon'], 'large') : '';

	update_post_meta($post_id, 'sharethumb', $post_configuration);

	$configuration = get_option('sharethumb_options');
	$thumbnail_id = fsst_api_get_thumbnail_id($configuration['api_key'], get_the_permalink($post_id));
	if($thumbnail_id) {
		$post_configuration['title'] = $post->post_title;
		foreach($configuration as $key => $value) {
			if(empty($post_configuration[$key])) {
				$post_configuration[$key] = $value;
			}
		}
		fsst_api_regenerate_thumbnail($post_configuration, $thumbnail_id);
	}
}

function fsst_get_overrides_text_field_html($field_label, $field_name, $configuration, $description = '') {
    $field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : '';
    if($description) {
        $description = "<div class='description'>{$description}</div>";
    }
    return "
        <label for='field-{$field_name}'>{$field_label}</label>
        <input id='field-{$field_name}' type='text' name='{$field_name}' placeholder='{$field_label}' value='{$field_value}' />
        {$description}
    ";
}

function fsst_get_overrides_image_field_html($field_label, $field_name, $configuration) {
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
        <label for='field-{$field_name}'>{$field_label}</label>
        {$field_markup}
        <input id='field-{$field_name}' type='hidden' name='{$field_name}' value='{$field_value}' />
    ";
}

function fsst_get_overrides_select_field_html($field_label, $field_name, $configuration) {
    $field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : '';
    $options = fsst_get_select_options($field_name);

    // If, for whatever reason the ShareThumb API isn't reachable, let's just show a text field
    if(!is_array($options) || !count($options)) {

        return "
            <label for='field-{$field_name}'>{$field_label}</label>
            <input id='field-{$field_name}' type='text' name='{$field_name}' value='{$field_value}' />
        ";

    } else {

        $options_markup = '';
        foreach($options as $key => $label) {
            if($field_value == $key) {
                $selected_option = true;
                $options_markup .= "<option value='{$key}' selected>{$label}</option>";
            } else {
                $options_markup .= "<option value='{$key}'>{$label}</option>";
            }
        }

        $option_none_label = ($post_id) ? 'Global Default' : 'None';
        $option_none = ($field_value) ? "<option value=''>{$option_none_label}</option>" : "<option value='' selected>{$option_none_label}</option>";

        return "
            <label for='field-{$field_name}'>{$field_label}</label>
            <select id='field-{$field_name}' class='select2' name='{$field_name}' data-placeholder='{$field_label}'>
                {$option_none}
                {$options_markup}
            }
            </select>
        ";

    }
}

// Functionality for this field is supplemented by the jscolor script
function fsst_get_overrides_color_picker_field_html($field_label, $field_name, $configuration) {
    $field_value = isset($configuration[$field_name]) ? $configuration[$field_name] : '';
    return "
        <label for='field-{$field_name}'>{$field_label}</label>          
        <input id='field-{$field_name}' data-jscolor='{required:false}' name='{$field_name}' value='{$field_value}' />
    ";
}


// Post-specific overrides
function fsst_get_default_post_configuration() {
	return [
		'logo' => 0,
		'icon' => 0,
		'theme' => '',		
		'theme_custom' => '',
		'font' => '',		
		'font_color' => '',	
		'background_color' => '',	
		'accent_color' => '',		
		'secondary_color' => '',	
		'icon_url' => '',	
		'logo_url' => ''
	];
}


function fsst_get_overridable_post_types() {
    $all_post_types = get_post_types([], 'objects');
    $default_excluded_post_types = ['attachment', 'custom_css', 'nav_menu_item', 'revision', 'attachment', 'seopress_schemas', 'seopress_404', 'seopress_bot', 'acf-field', 'acf-taxonomy', 'acf-field-group', 'acf-post-type', 'cbxchangelog', 'wp_navigation', 'wp_global_styles', 'wp_template_part', 'wp_template', 'wp_block', 'user_request', 'oembed_cache', 'customize_changeset'];
    $overridable_post_types = [];
    foreach($all_post_types as $key => $value) {
        if(!in_array($key, $default_excluded_post_types)) {
            $overridable_post_types[$key] = $value->label;
        }
    }
    return $overridable_post_types;
}