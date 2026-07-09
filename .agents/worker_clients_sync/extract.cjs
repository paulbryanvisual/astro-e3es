const fs = require('fs');
const path = require('path');

const dataPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
const rawData = fs.readFileSync(dataPath, 'utf8');
const data = JSON.parse(rawData);

console.log('Total items in dump:', Array.isArray(data) ? data.length : typeof data);
if (Array.isArray(data)) {
    const targets = ['caldwell-isd', 'carrizo-springs-cisd', 'donna-isd'];
    const found = data.filter(item => targets.includes(item.post_name) || targets.includes(item.slug));
    console.log('Found targets:', found.map(item => ({ id: item.ID || item.id, slug: item.post_name || item.slug, keys: Object.keys(item) })));
    fs.writeFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync/extracted_targets.json', JSON.stringify(found, null, 2));
} else {
    console.log('Data keys:', Object.keys(data).slice(0, 10));
}
