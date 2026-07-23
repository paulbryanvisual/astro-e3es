const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('clients_dump.json', 'utf8'));
console.log('Total items in dump:', dump.length);
if (Array.isArray(dump)) {
    const slugs = dump.map(item => item.slug || item.post_name);
    console.log('Slugs (first 10):', slugs.slice(0, 10));
    fs.writeFileSync('dump_slugs.json', JSON.stringify(slugs.sort(), null, 2));
} else {
    console.log('Dump is not an array. Keys:', Object.keys(dump));
}
