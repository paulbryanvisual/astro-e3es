const fs = require('fs');
const https = require('https');
const http = require('http');

function fetchUrl(url) {
    return new Promise((resolve, reject) => {
        const mod = url.startsWith('https') ? https : http;
        const req = mod.get(url, {
            headers: { 'User-Agent': 'Mozilla/5.0 (compatible; E3MigrationBot/1.0)' }
        }, (res) => {
            if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                return fetchUrl(res.headers.location).then(resolve).catch(reject);
            }
            if (res.statusCode !== 200) {
                res.resume();
                return reject(new Error(`HTTP ${res.statusCode} for ${url}`));
            }
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve(data));
        });
        req.on('error', reject);
        req.setTimeout(15000, () => { req.destroy(); reject(new Error('Timeout')); });
    });
}

async function main() {
    const sitemaps = [
        'https://www.e3es.com/sitemap.xml',
        'https://www.e3es.com/wp-sitemap.xml',
        'https://www.e3es.com/sitemap_index.xml'
    ];
    
    for (const url of sitemaps) {
        console.log(`Checking sitemap: ${url}`);
        try {
            const xml = await fetchUrl(url);
            console.log(`  -> Succeeded! XML snippet:\n${xml.slice(0, 1000)}`);
            fs.writeFileSync(`/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync/sitemap_${url.split('/').pop()}.xml`, xml);
        } catch(e) {
            console.log(`  -> Failed: ${e.message}`);
        }
    }
}

main().catch(console.error);
