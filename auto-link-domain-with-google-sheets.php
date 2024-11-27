<?php
/**
 * Plugin Name: Auto-Link Domain with Google Sheets
 * Description: Automatically connect your domain with Google Sheets via API using JavaScript.
 * Version: 1.1
 * Author: WPPOOL
 */

// Activation Hook
function demo_with_google_sheets_activate() {
    if (get_option('demo_one_time_load') === false) {
        add_option('demo_one_time_load', false);
    }
    update_option('demo_one_time_load', false);
}
register_activation_hook(__FILE__, 'demo_with_google_sheets_activate');

// Enqueue JavaScript
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_scripts');
function enqueue_custom_admin_scripts() {
    wp_enqueue_script(
        'google-sheets-api',
        plugin_dir_url(__FILE__) . 'google-sheets-api.js',
        ['jquery'], // Dependencies (optional)
        '1.0',
        true // Load in footer
    );

    // Pass data to JavaScript
    wp_localize_script('google-sheets-api', 'apiData', [
        'apiUrl' => 'https://googlesheetsdemolink.wcordersync.com/api/check-the-domain-is-connect',
        'domain' => home_url(),
        'nonce'  => wp_create_nonce('api_call_nonce') // Security nonce
    ]);
}

// Helper Function to Extract Google Sheet ID
function extract_sheet_id($url) {
    if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
        return $matches[1];
    }
    return "Invalid Google Sheets link";
}

// Admin Notice Example
add_action('admin_notices', 'show_current_domain_notice');
function show_current_domain_notice() {
    // Check if the cookie exists
    if (isset($_COOKIE['spreadsheet_url'])) {
        // Access the cookie value
        $spreadsheet_url = $_COOKIE['spreadsheet_url'];
        echo "Spreadsheet URL: " . $spreadsheet_url;
    } else {
        echo "Cookie 'spreadsheet_url' not set.";
    }

    // Similarly, you can access the 'spreadsheet_id' cookie
    if (isset($_COOKIE['spreadsheet_id'])) {
        $spreadsheet_id = $_COOKIE['spreadsheet_id'];
        echo "Spreadsheet ID: " . $spreadsheet_id;
    } else {
        echo "Cookie 'spreadsheet_id' not set.";
    }

    $current_domain = home_url();
    $myCurrentDomain = "http://ssgs.local";

    if ($current_domain !== $myCurrentDomain) {
        $demo_one_time_load = get_option('demo_one_time_load');
        if ($demo_one_time_load == false) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p>Domain check in progress. Please wait...</p>';
            echo '</div>';
        }
    }
}

?>
