<?php
/**
 * Plugin Name: Auto-Link Domain with Google Sheets
 * Description: Create a new domain and automatically connect it with Google Sheets.
 * Version: 2.1
 * Author: WPPOOL
 */

function demo_with_google_sheets_activate() {
    if (get_option('demo_one_time_load') === false) {
        add_option('demo_one_time_load', false);
    }
    update_option('demo_one_time_load', false);
}
register_activation_hook(__FILE__, 'demo_with_google_sheets_activate');

add_action('admin_notices', 'show_current_domain_notice');


function show_current_domain_notice() {
    $current_domain = home_url();
    $myCurrentDomain = "http://ssgs.local";

    if($current_domain !== $myCurrentDomain) {
        $demo_one_time_load = get_option('demo_one_time_load');
        update_option('demo_one_time_load', false);
        if($demo_one_time_load == false) {
            // Enqueue JavaScript
            add_action('admin_enqueue_scripts', 'enqueue_custom_admin_scripts');
            function enqueue_custom_admin_scripts() {
                wp_enqueue_script(
                    'google-sheets-api',
                    plugin_dir_url(__FILE__) . 'google-sheets-api.js',
                    ['jquery'], 
                    '1.0',
                    true
                );

                wp_localize_script('google-sheets-api', 'apiData', [
                    'apiUrl' => 'https://googlesheetsdemolink.wcordersync.com/api/check-the-domain-is-connect',
                    'domain' => home_url(),
                    'nonce'  => wp_create_nonce('api_call_nonce')
                ]);
            }

            if (isset($_COOKIE['spreadsheet_url'])) {
                $spreadsheet_url = $_COOKIE['spreadsheet_url'];
                echo "Spreadsheet URL: " . $spreadsheet_url;
            } else {
                echo "Cookie 'spreadsheet_url' not set.";
                return false;
            }
            if (isset($_COOKIE['spreadsheet_id'])) {
                $spreadsheet_id = $_COOKIE['spreadsheet_id'];
                echo "Spreadsheet ID: " . $spreadsheet_id;
            } else {
                echo "Cookie 'spreadsheet_id' not set.";
                return false;
            }

            update_option('ssgsw_spreadsheet_url', $_COOKIE['spreadsheet_url']);
            update_option('ssgsw_spreadsheet_id', $_COOKIE['spreadsheet_id']);
            // Trigger the sync action
            do_action('ssgsw_updated_save_and_sync');
            update_option('demo_one_time_load', true);
        }
    }
}