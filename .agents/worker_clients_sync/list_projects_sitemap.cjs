const fs = require('fs');
const path = require('path');

const xmlPath = '/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync/sitemap_wp-sitemap-posts-projects-1.xml';
if (fs.existsSync(xmlPath)) {
    const xml = fs.readFileSync(xmlPath, 'utf8');
    const locs = [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1]);
    console.log(`Total projects in sitemap: ${locs.length}`);
    console.log(locs);
} else {
    console.log('File not found');
}
