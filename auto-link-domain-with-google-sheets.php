<?php
/**
 * Plugin Name: Auto-Link Domain with Google Sheets
 * Description: Create a new domain and automatically connect it with Google Sheets.
 * Version: 2.1
 * Author: WPPOOL
 */

function demo_with_google_sheets_activate_osgs() {
    if (get_option('demo_one_time_load_osgs') === false) {
        add_option('demo_one_time_load_osgs', false);
    }
    update_option('demo_one_time_load_osgs', false);
}
register_activation_hook(__FILE__, 'demo_with_google_sheets_activate_osgs');

add_action('admin_notices', 'show_current_domain_notice_osgs');

function enqueue_custom_admin_scripts_osgs() {
    wp_enqueue_script(
        'google-sheets-api',
        plugin_dir_url(__FILE__) . 'google-sheets-api.js',
        ['jquery'], 
        '1.0',
        true
    );

    wp_localize_script('google-sheets-api', 'apiData', [
        'apiUrl' => 'https://googlesheetsdemolink.wcordersync.com/api/check-the-domain-is-connect-osgs',
        'domain' => home_url(),
        'nonce'  => wp_create_nonce('api_call_nonce')
    ]);
}


function show_current_domain_notice_osgs() {
    $current_domain = home_url();
    $myCurrentDomain = "http://osgs.local";

    if($current_domain !== $myCurrentDomain) {
        $demo_one_time_load_osgs = get_option('demo_one_time_load_osgs');
        if($demo_one_time_load_osgs == false) {
            // Enqueue JavaScript

            if (isset($_COOKIE['spreadsheet_url'])) {
            } else {
                return false;
            }
            if (isset($_COOKIE['spreadsheet_id'])) {
            } else {
                return false;
            }

            update_option('osgsw_spreadsheet_url', $_COOKIE['spreadsheet_url']);
            update_option('osgsw_spreadsheet_id', $_COOKIE['spreadsheet_id']);
            // Trigger the sync action
            do_action('osgsw_updated_save_and_sync');
            update_option('demo_one_time_load_osgs', true);
        }
    }
}

$current_domain = home_url();
$myCurrentDomain = "http://osgs.local";
if($current_domain !== $myCurrentDomain) {
    $demo_one_time_load_osgs = get_option('demo_one_time_load_osgs');
    if($demo_one_time_load_osgs == false) {      
        add_action('admin_enqueue_scripts', 'enqueue_custom_admin_scripts_osgs');
    }
}