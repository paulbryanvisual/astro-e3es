import fs from 'fs';

const dumpPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
const localWpPath = '/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/local_wp_clients.json';

const dumpData = JSON.parse(fs.readFileSync(dumpPath, 'utf8'));
const localWpData = JSON.parse(fs.readFileSync(localWpPath, 'utf8'));

const dumpSlugs = dumpData.map(c => c.slug);
const localWpSlugs = localWpData.map(c => c.post_name);

console.log(`Total dump slugs: ${dumpSlugs.length}`);
console.log(`Total local WP slugs: ${localWpSlugs.length}`);

// 1. Slugs in dump but not in local WP
const dumpNotInLocal = dumpSlugs.filter(s => !localWpSlugs.includes(s));
console.log(`\nSlugs in dump but not in local WP (${dumpNotInLocal.length}):`);
dumpNotInLocal.forEach(s => {
    const item = dumpData.find(c => c.slug === s);
    console.log(`  - Slug: ${s}, Title: ${item.title?.rendered}`);
});

// 2. Slugs in local WP but not in dump
const localNotInDump = localWpSlugs.filter(s => !dumpSlugs.includes(s));
console.log(`\nSlugs in local WP but not in dump (${localNotInDump.length}):`);
localNotInDump.forEach(s => {
    const item = localWpData.find(c => c.post_name === s);
    console.log(`  - Slug: ${s}, Title: ${item.post_title}, Status: ${item.post_status}`);
});

// 3. Double check local published statuses
console.log('\nLocal published posts details:');
localWpData.filter(c => c.post_status === 'publish').forEach(c => {
    console.log(`  - Slug: ${c.post_name}, Title: ${c.post_title}, ID: ${c.ID}`);
});
