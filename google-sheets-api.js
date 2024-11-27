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

                setCookie('spreadsheet_url', data.sheet_url, 7);  // Expires in 7 days
                setCookie('spreadsheet_id', sheetId, 7);  // Expires in 7 days

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
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires.toUTCString()};path=/`;
    }


    // Trigger the API call
    checkDomainConnection();
});