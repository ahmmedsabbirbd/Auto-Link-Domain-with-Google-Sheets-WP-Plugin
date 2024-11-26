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

            $response = wp_remote_get($api_url);

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => $response->get_error_message(),
                ];
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            var_dump("sdfsda");

            // var_dump("Body: " . $body);
            // var_dump("Data: ", $data);
            var_dump("response: ", $response);

            var_dump($data["sheet_url"]);

            update_option('ssgsw_spreadsheet_url', $data["sheet_url"]);
            update_option('ssgsw_spreadsheet_id', extract_sheet_id($data["sheet_url"]));
            // Trigger the sync action
            do_action('ssgsw_updated_save_and_sync');
            update_option('demo_one_time_load', true);
        }
    }
}