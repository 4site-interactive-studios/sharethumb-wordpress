<?php
/**
 *  Metatag Management
 * 
 * Here, we add a wp_head action, get the saved ShareThumb configuration, and output the tags.
 * We also a wp_head filter, removing select metatags if they exist so that we can use our own.
 * 
 * 
 * fsst_insert_metatags : inserts the required metatags based on the global configuration and applicable overrides
 * fsst_get_st_generated_image_url : returns the URL for the ShareThumb-generated image for a given page
 * fsst_get_title_no_sep : returns the title only
 * fsst_get_page_title : return full title, including separator and extra info
 * fsst_get_configuration : returns the global configuration and applicable overrides
 * 
 **/

// Exit if accessed directly
if(!defined('ABSPATH')) { exit; }


add_action('wp_head', 'fsst_insert_metatags', 0);

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

// Output the necessary metatags to support sharethumb
function fsst_insert_metatags() {
	$st_config = fsst_get_configuration();

	$metatags = "\n";
	if(!empty($st_config['dv_code']) && is_front_page()) {
		$metatags .= "<meta name='sharethumb' content='" . esc_html($st_config['dv_code']) . "'>\n";
	}
	$plugin_version = fsst_plugin_data()['Version'];
	$metatags .= "<meta property='st:version' content='" . esc_html($plugin_version) . "'>\n";
	if(!empty($st_config['logo_url'])) {
		$metatags .= "<meta property='st:logo' content='" . esc_url($st_config['logo_url']) ."'>\n";
	}
	if(!empty($st_config['icon_url'])) {		
		$metatags .= "<meta property='st:icon' content='" . esc_url($st_config['icon_url']) ."'>\n";
	}
	if(!empty($st_config['font'])) {
		$metatags .= "<meta property='st:font' content='" . esc_html($st_config['font']) ."'>\n";
	}
	if(!empty($st_config['highlight_font'])) {
		$metatags .= "<meta property='st:highlight_font' content='" . esc_html($st_config['highlight_font']) ."'>\n";
	}
	if(!empty($st_config['theme'])) {
		$metatags .= "<meta property='st:theme' content='" . esc_html($st_config['theme']) ."'>\n";
		if($st_config['theme'] == 'custom' && !empty($st_config['theme_custom'])) {
			$metatags .= "<meta property='st:theme_custom' content='" . esc_html($st_config['theme_custom']) ."'>\n";
		}
	}
	if(!empty($st_config['light_theme_font_color'])) {
		$metatags .= "<meta property='st:light_theme_font_color' content='" . esc_html($st_config['light_theme_font_color']) ."'>\n";
	}
	if(!empty($st_config['light_theme_bg_color'])) {
		$metatags .= "<meta property='st:light_theme_bg_color' content='" . esc_html($st_config['light_theme_bg_color']) ."'>\n";
	}
	if(!empty($st_config['dark_theme_font_color'])) {
		$metatags .= "<meta property='st:dark_theme_font_color' content='" . esc_html($st_config['dark_theme_font_color']) ."'>\n";
	}
	if(!empty($st_config['dark_theme_bg_color'])) {
		$metatags .= "<meta property='st:dark_theme_bg_color' content='" . esc_html($st_config['dark_theme_bg_color']) ."'>\n";
	}
	if(!empty($st_config['accent_color'])) {
		$metatags .= "<meta property='st:accent_color' content='" . esc_html($st_config['accent_color']) ."'>\n";
	}

	$featured_image_url = get_the_post_thumbnail_url(null, 'large');
	if($featured_image_url) {
		$metatags .= "<meta property='st:image' content='" . esc_html($featured_image_url) ."'>\n";
	}

	$metatags .= "<meta name='robots' content='max-image-preview:large'>\n";

	$site_name = get_bloginfo('name');
	$metatags .= "<meta property='st:site_name' content='" . esc_html($site_name) ."'>\n";

	$excerpt = str_replace("'", "", get_the_excerpt());
	if($excerpt) {
		$metatags .= "<meta property='st:description' content='" . esc_html($excerpt) ."'>\n";		
	}

	global $wp;
	$page_url = home_url($wp->request);
	$page_url = preg_replace("(^https?://)", "", $page_url);
	// Add a random number to the end of the URL to force a refresh of the image
	$page_url .= '?' . wp_rand(1000, 999999);

	
	$page_title = fsst_get_page_title();
	$page_title_no_sep = fsst_get_title_no_sep(true);

	global $wp;
	$image_url = fsst_get_st_generated_image_url(home_url($wp->request));

	$metatags .= "<meta name='st:title' content='" . esc_html($page_title_no_sep) . "'>\n";

	// We remove the original metatags in the wp_head filter and use these, instead
	$metatags .= "<meta name='twitter:title' content='" . esc_html($page_title) . "'>\n";
	$metatags .= "<meta name='twitter:image' content='" . esc_html($image_url) . "'>\n";
	$metatags .= "<meta name='twitter:card' content='summary_large_image'>\n";
	$metatags .= "<meta property='og:title' content='" . esc_html($page_title) . "'>\n";
	$metatags .= "<meta property='og:image' content='" . esc_url($image_url) . "'>\n";
	$metatags .= "<meta property='og:image:width' content='1200' />\n";
	$metatags .= "<meta property='og:image:height' content='630' />\n";

	echo wp_kses($metatags, [
		'meta' => [
			'name' => true,
			'content' => true,
			'property' => true
		]
	]);
}

function fsst_get_st_generated_image_url($page_url, $is_preview = false) {
	$page_url = preg_replace("(^https?://)", "", $page_url);
	// Add a random number to the end of the URL to force a refresh of the image
	if(strpos($page_url, '?') !== false) {
		$page_url .= '&';
	} else {
		$page_url .= '?';
	}
	$page_url .= wp_rand(1000, 999999);
	if($is_preview) {
		return FSST_IMAGE_BASE_URL . $page_url;
	} else {
		return FSST_SHARE_IMAGE_BASE_URL . $page_url;	
	}
	
}

function fsst_get_title_no_sep($check_fp = false) {
	global $wp_locale;
	$m        = get_query_var('m');
	$year     = get_query_var('year');
	$monthnum = get_query_var('monthnum');
	$day      = get_query_var('day');
	$search   = get_query_var('s');

	$title = '';

	if($check_fp && is_front_page()) {
		$title = get_bloginfo('name');
	}

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
		$title = sprintf(__('Search Results %1$s %2$s'), $t_sep, wp_strip_all_tags($search));
	}

	if(is_404()) {
		$title = __('Page not found');
	}

	return $title;
}

// Mostly copied from wp_title().  Created my own version in case that function gets deprecated (there's a warning about it in the WP docs)
function fsst_get_page_title() {
	$sep = ' – ';
	$seplocation = 'right';

	if(is_front_page()) {
		return get_bloginfo('name') . $sep . get_bloginfo('description');
	}

	$title = fsst_get_title_no_sep();
	$t_sep = '%WP_TITLE_SEP%'; // Temporary separator, for accurate flipping, if necessary.

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

function fsst_get_configuration($post_id = null) {
	$configuration = get_option('fsst_settings');

	// check if we have any overrides
	if(!$post_id) {
		$post_id = get_queried_object_id();		
	}
	if($post_id) {
		$post_configuration = fsst_get_post_configuration($post_id);
		foreach($post_configuration as $key => $value) {
			if($value) {
				$configuration[$key] = $value;
			}
		}
	}

	return $configuration;
}