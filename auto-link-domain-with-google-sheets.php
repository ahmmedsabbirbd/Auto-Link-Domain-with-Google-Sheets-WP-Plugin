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

<!-- External JavaScript File: google-sheets-api.js -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ensure apiData object exists
    if (typeof apiData === 'undefined') {
        console.error('API data not loaded');
        return;
    }

    const { apiUrl, domain, nonce } = apiData;

    // Function to make API call
    async function checkDomainConnection() {
        try {
            const response = await fetch(`${apiUrl}?domain=${encodeURIComponent(domain)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce, // Optional for secured WordPress AJAX
                }
            });

            if (!response.ok) {
                console.error(`API error: ${response.status}`);
                return;
            }

            const data = await response.json();

            if (data.sheet_url) {
                console.log(`Spreadsheet URL: ${data.sheet_url}`);
                const sheetId = extractSheetId(data.sheet_url);
                console.log(`Extracted Sheet ID: ${sheetId}`);

                // Store data in localStorage or send to WordPress
                localStorage.setItem('spreadsheet_url', data.sheet_url);
                localStorage.setItem('spreadsheet_id', sheetId);

                // Update admin notice (Optional)
                const adminNotice = document.querySelector('.notice.notice-info');
                if (adminNotice) {
                    adminNotice.innerHTML = `<p>Successfully connected to Google Sheet: ${data.sheet_url}</p>`;
                }
            } else {
                console.warn('No sheet_url in response');
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    // Function to extract Google Sheet ID
    function extractSheetId(url) {
        const match = url.match(/\/d\/([a-zA-Z0-9-_]+)/);
        return match ? match[1] : "Invalid Google Sheets link";
    }

    // Trigger the API call
    checkDomainConnection();
});
</script>
