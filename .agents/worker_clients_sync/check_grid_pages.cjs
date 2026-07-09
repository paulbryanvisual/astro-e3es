const https = require('https');
const http = require('http');

function checkPage(url) {
    return new Promise((resolve) => {
        const mod = url.startsWith('https') ? https : http;
        mod.get(url, {
            headers: { 'User-Agent': 'Mozilla/5.0 (compatible; E3MigrationBot/1.0)' }
        }, (res) => {
            console.log(`${url} -> HTTP ${res.statusCode}`);
            res.resume();
            resolve(res.statusCode);
        }).on('error', (e) => {
            console.log(`${url} -> Error: ${e.message}`);
            resolve(500);
        });
    });
}

async function main() {
    for (let i = 1; i <= 10; i++) {
        await checkPage(`https://www.e3es.com/clients/page/${i}/`);
    }
}

main().catch(console.error);
