<?php
/**
 * Plugin Name: ShareThumb Wordpress Plugin
 * Version: 1.0.4
 * Plugin URI: https://sharethumb.io/
 * Description: Configure the ShareThumb service directly via your own website.
 * Author: 4Site Interactive Studios
 * Author URI: https://4sitestudios.com
 */

if(!defined('ABSPATH')) { exit; }

define('FSST_PLUGIN_PATH', plugin_dir_path(__FILE__));

define('FSST_FONT_URL', 'https://api.sharethumb.app/fonts');
define('FSST_THEME_URL', 'https://api.sharethumb.app/themes');
define('FSST_SETTINGS_URL', 'https://use.sharethumb.io/save-settings');
define('FSST_REGENERATE_THUMBNAIL_URL', 'https://use.sharethumb.io/regenerate-thumb');
define('FSST_GET_THUMBNAIL_ID_URL', 'https://use.sharethumb.io/get-thumb-id');
define('FSST_VALIDATE_KEY_URL', 'https://use.sharethumb.io/validate-api-key');

// This base URL must end in a slash
define('FSST_IMAGE_BASE_URL', 'https://use.sharethumb.io/image/');

// Registers & renders the global ShareThumb settings page
include 'functions/settings-page.php';
// Individual posts can override the general settings
include 'functions/post-overrides.php';
// Inserts the ShareThumb metatags into the head
include 'functions/metatags-insert.php';
// Communicates with the ShareThumb API
include 'functions/sharethumb-api.php';