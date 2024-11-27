document.addEventListener('DOMContentLoaded', function () {
    if (typeof apiData === 'undefined') {
        console.error('API data not loaded');
        return;
    }

    const { apiUrl, domain, nonce } = apiData;

    async function checkDomainConnection() {
        try {
            const response = await fetch(`${apiUrl}?domain=${encodeURIComponent(domain)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
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

                setCookie('spreadsheet_url', data.sheet_url, 7);
                setCookie('spreadsheet_id', sheetId, 7);
            } else {
                console.warn('No sheet_url in response');
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    function extractSheetId(url) {
        const match = url.match(/\/d\/([a-zA-Z0-9-_]+)/);
        return match ? match[1] : "Invalid Google Sheets link";
    }
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires.toUTCString()};path=/`;
    }

    checkDomainConnection();
});