const fs = require('fs');

const dumpPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
if (fs.existsSync(dumpPath)) {
    const dump = JSON.parse(fs.readFileSync(dumpPath, 'utf8'));
    console.log(`Total items: ${dump.length}`);
    const results = [];
    for (const item of dump) {
        const id = item.id || item.ID;
        const slug = item.slug || item.post_name;
        const content = item.content ? item.content.rendered : '';
        
        // Find all wp-content/uploads image links in the content
        const matches = [...content.matchAll(/https?:\/\/[^\s"')]+wp-content\/uploads\/[^\s"')]+/g)].map(m => m[0]);
        if (matches.length > 0) {
            results.push({ id, slug, images: matches });
        }
    }
    console.log(`Found ${results.length} items with uploads images in dump.`);
    console.log('Sample items:', results.slice(0, 5));
    fs.writeFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync/dump_images.json', JSON.stringify(results, null, 2));
} else {
    console.log('File not found');
}
