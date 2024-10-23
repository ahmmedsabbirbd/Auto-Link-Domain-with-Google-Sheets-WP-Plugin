<?php
/**
 * Plugin Name: Auto-Link Domain with Google Sheets
 * Description: A simple plugin to display the current domain.
 * Version: 1.0
 * Author: Your Name
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

    if($current_domain !== "https://complexplough.s4-tastewp.com") {
        $demo_one_time_load = get_option('demo_one_time_load');
        if($demo_one_time_load == false) {
            $url =  home_url();
            $api_url = 'https://ssgs-osgs-demo.calculexapp.com/api/check-the-domain-is-connect?domain='.urlencode($url);

            $response = wp_remote_get($api_url);

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => $response->get_error_message(),
                ];
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            update_option('ssgsw_spreadsheet_url', $data["sheet_url"]);
            // Trigger the sync action
            do_action('ssgsw_updated_save_and_sync');
            update_option('demo_one_time_load', true);
        }
    }


    echo "<div class='notice notice-success is-dismissible'>
           
          </div>";
}

// Shortcode to display domain on any page/post.
add_shortcode('show_domain', 'get_domain_shortcode');

function get_domain_shortcode() {
    return home_url();
}