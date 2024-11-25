<?php
/**
 * Plugin Name: Auto-Link Domain with Google Sheets
 * Description: Create a new domain and automatically connect it with Google Sheets.
 * Version: 1.0
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

function extract_sheet_id($url) {
    // PHP version of extracting Google Sheets ID
    if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
        return $matches[1];
    }
    return "Invalid Google Sheets link";
}

function show_current_domain_notice_osgs() {
    $current_domain = home_url();
    $myCurrentDomain = "http://osgs.local";

    if($current_domain !== $myCurrentDomain) {
        $demo_one_time_load_osgs = get_option('demo_one_time_load_osgs');
        if($demo_one_time_load_osgs == false) {
            $url =  home_url();
            $api_url = 'http://ssgs-osgs.sportsontheweb.net/api/check-the-domain-is-connect-osgs?domain='.urlencode($url);

            $response = wp_remote_get($api_url);

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => $response->get_error_message(),
                ];
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            update_option('osgsw_spreadsheet_url', $data["sheet_url"]);
            update_option('osgsw_spreadsheet_id', extract_sheet_id($data["sheet_url"]));
            // Trigger the sync action
            do_action('osgsw_updated_save_and_sync');
            update_option('demo_one_time_load_osgs', true);
        }
    }
}