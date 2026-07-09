const https = require('https');

const testUrl = 'https://www.e3es.com/wp-content/uploads/2026/06/Jason%20Flowers%20-%20Donna%20ISD%20for%20TFC.jpg';
https.get(testUrl, (res) => {
    console.log(`HTTP Status for ${testUrl}: ${res.statusCode}`);
    res.resume();
}).on('error', (e) => {
    console.log(`Error: ${e.message}`);
});
