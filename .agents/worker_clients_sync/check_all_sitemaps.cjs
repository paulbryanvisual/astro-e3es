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

const PLACEHOLDER_LIST = require('../../still_placeholder.json');

async function main() {
    const sitemaps = [
        'https://www.e3es.com/wp-sitemap-posts-post-1.xml',
        'https://www.e3es.com/wp-sitemap-posts-page-1.xml',
        'https://www.e3es.com/wp-sitemap-posts-services-1.xml',
        'https://www.e3es.com/wp-sitemap-posts-books-1.xml',
        'https://www.e3es.com/wp-sitemap-posts-exihibitions-1.xml'
    ];
    
    const slugMap = {};
    for (const item of PLACEHOLDER_LIST) {
        slugMap[item.slug] = null;
    }
    
    // Also include projects sitemap that we already fetched
    const projectsXml = fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync/sitemap_wp-sitemap-posts-projects-1.xml', 'utf8');
    const projectLocs = [...projectsXml.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1]);
    for (const loc of projectLocs) {
        for (const slug of Object.keys(slugMap)) {
            if (loc.includes(slug)) {
                slugMap[slug] = loc;
            }
        }
    }
    
    for (const url of sitemaps) {
        console.log(`Checking sitemap: ${url}`);
        try {
            const xml = await fetchUrl(url);
            const locs = [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1]);
            for (const loc of locs) {
                for (const slug of Object.keys(slugMap)) {
                    if (loc.includes(slug)) {
                        slugMap[slug] = loc;
                    }
                }
            }
        } catch(e) {
            console.log(`  Failed: ${e.message}`);
        }
    }
    
    const found = Object.entries(slugMap).filter(([s, url]) => url !== null);
    const missing = Object.entries(slugMap).filter(([s, url]) => url === null).map(([s]) => s);
    
    console.log(`\nFound mappings for ${found.length} / ${PLACEHOLDER_LIST.length} slugs:`);
    console.log(found);
    
    console.log(`\nMissing slugs (${missing.length}):`);
    console.log(missing);
}

main().catch(console.error);
