<?php
/**
 *  Plugin Operations
 * 
 *  We set up default options object on activation, and clear all settings on deactivation.
 * 
 * fsst_activate
 * fsst_deactivate
 * 
 **/

// Exit if accessed directly
if(!defined('ABSPATH')) { exit; }


// Activation & Deactivation
register_activation_hook(fsst_plugin_path() . 'index.php', 'fsst_activate');
register_deactivation_hook(fsst_plugin_path() . 'index.php', 'fsst_deactivate');

function fsst_activate() {
    update_option('fsst_settings', [
        'api_key' => '',
        'dv_code' => '',
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
        'logo_url' => '',
        'post_types' => ['post', 'page']
    ]);
}

function fsst_deactivate() {
    // clear all data
    delete_option('fsst_settings');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'fsst_settings'");
}