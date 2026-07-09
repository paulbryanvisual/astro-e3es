const fs = require('fs');

const dumpPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
if (fs.existsSync(dumpPath)) {
    const dump = JSON.parse(fs.readFileSync(dumpPath, 'utf8'));
    const slugs = dump.map(item => item.slug || item.post_name);
    console.log(`Total slugs in clients_dump.json: ${slugs.length}`);
    console.log(slugs.sort());
} else {
    console.log('File not found');
}
