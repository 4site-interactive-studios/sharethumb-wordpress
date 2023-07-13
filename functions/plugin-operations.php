<?php

// Activation & Deactivation
register_activation_hook(FSST_PLUGIN_PATH . 'index.php', 'fsst_activate');
register_deactivation_hook(FSST_PLUGIN_PATH . 'index.php', 'fsst_deactivate');

function fsst_activate() {
    //fsst_register_settings();
    update_option('sharethumb_options', [
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
    delete_option('sharethumb_options');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'sharethumb'");
}