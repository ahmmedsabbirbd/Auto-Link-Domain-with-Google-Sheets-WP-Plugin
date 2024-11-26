<?php
/**
 * Plugin Name: Auto-Link Domain with Google Sheets
 * Description: Create a new domain and automatically connect it with Google Sheets.
 * Version: 1.0
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

function extract_sheet_id($url) {
    // PHP version of extracting Google Sheets ID
    if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
        return $matches[1];
    }
    return "Invalid Google Sheets link";
}

function show_current_domain_notice() {
    $current_domain = home_url();
    $myCurrentDomain = "http://ssgs.local";

    if($current_domain !== $myCurrentDomain) {
        $demo_one_time_load = get_option('demo_one_time_load');
        if($demo_one_time_load == false) {
            $url =  home_url();
            $api_url = 'https://googlesheetsdemolink.wcordersync.com/api/check-the-domain-is-connect?domain='.urlencode($url);

            $response = wp_remote_get($api_url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                ],
            ]);

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => $response->get_error_message(),
                ];
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if (!isset($data["sheet_url"])) {
                error_log('Missing "sheet_url" in API response: ' . $body);
                return;
            }
            update_option('ssgsw_spreadsheet_url', $data["sheet_url"]);
            update_option('ssgsw_spreadsheet_id', extract_sheet_id($data["sheet_url"]));
            // Trigger the sync action
            do_action('ssgsw_updated_save_and_sync');
            update_option('demo_one_time_load', true);
        }
    }
}