<?php
/**
 * Plugin Name: ShareThumb
 * Version: 1.3
 * Plugin URI: https://sharethumb.io/
 * Description: Configure the ShareThumb service directly via your own website.
 * Author: 4Site Interactive Studios
 * Author URI: https://4sitestudios.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if(!defined('ABSPATH')) { exit; }

define('FSST_FONT_URL', 'https://api.sharethumb.app/fonts');
define('FSST_THEME_URL', 'https://api.sharethumb.app/themes');
define('FSST_SETTINGS_URL', 'https://use.sharethumb.io/save-settings');
define('FSST_REGENERATE_THUMBNAIL_URL', 'https://use.sharethumb.io/regenerate-thumb');
define('FSST_GET_THUMBNAIL_ID_URL', 'https://use.sharethumb.io/get-thumb-id');
define('FSST_VALIDATE_KEY_URL', 'https://use.sharethumb.io/validate-api-key');
define('FSST_PREVIEW_URL', 'https://use.sharethumb.io/preview');
define('FSST_PREVIEW_SETTINGS_URL', 'https://use.sharethumb.io/preview-settings');

// These base URLs must end in a slash
define('FSST_IMAGE_BASE_URL', 'https://use.sharethumb.io/image/');  // Used for override image preview
define('FSST_SHARE_IMAGE_BASE_URL', 'https://use.sharethumb.io/og/');  // Used for actual og:imge metatag on pages

// Registers & renders the global ShareThumb settings page
include 'functions/settings-page.php';
// Individual posts can override the general settings
include 'functions/post-overrides.php';
// Inserts the ShareThumb metatags into the head
include 'functions/metatags-insert.php';
// Communicates with the ShareThumb API
include 'functions/sharethumb-api.php';
// Activation & Deactivation hooks
include 'functions/plugin-operations.php';
// Common functions that are used throughout the plugin
include 'functions/helpers.php';

// For convenience, defining these here so that we can use __FILE__ for plugin functions
function fsst_plugin_data() {
    if(!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    return get_plugin_data(__FILE__);
}
function fsst_plugin_basename() {
    return plugin_basename(__FILE__);
}
function fsst_plugin_path() {
    return plugin_dir_path(__FILE__);
}