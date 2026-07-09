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
    const url = 'https://www.e3es.com/wp-sitemap-posts-media-1.xml';
    console.log(`Fetching: ${url}`);
    try {
        const data = await fetchUrl(url);
        console.log(`  -> Succeeded! Length: ${data.length}`);
        fs.writeFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync/sitemap_media.xml', data);
        
        const locs = [...data.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1]);
        console.log(`Total media URLs: ${locs.length}`);
        console.log('Sample media URLs:', locs.slice(0, 15));
    } catch(e) {
        console.log(`  -> Failed: ${e.message}`);
    }
}

main().catch(console.error);
