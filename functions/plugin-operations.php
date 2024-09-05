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
if (!defined('ABSPATH')) {
    exit;
}


// Activation & Deactivation
register_activation_hook(fsst_plugin_path() . 'sharethumb.php', 'fsst_activate');
register_deactivation_hook(fsst_plugin_path() . 'sharethumb.php', 'fsst_deactivate');



function fsst_activate()
{
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
        'post_types' => ['post', 'page'],
        'delete_on_uinstall' => false
    ]);

    set_transient('fsst_admin_notices', [
        [
            'title' => 'ShareThumb Activated',
            'message' => 'Thank you for installing ShareThumb! Register & activate your domain <strong>for free</strong> to unlock more features!  <a href="https://app.sharethumb.io/" target="_blank">https://app.sharethumb.io/</a>',
            'status' => 'success'
        ]
    ]);

    $configuration = get_option('fsst_settings');
    if(empty($configuration['api_key'])) {
        fsst_set_domain_unverified();
    }
}

function fsst_deactivate()
{
    $configuration = get_option('fsst_settings');
    if (!empty($configuration['delete_on_uninstall'])) {
        // clear all data
        delete_option('fsst_settings');
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'fsst_settings'");
    }
}