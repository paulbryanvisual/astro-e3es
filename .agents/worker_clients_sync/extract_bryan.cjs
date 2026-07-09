const fs = require('fs');

const dataPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
const rawData = fs.readFileSync(dataPath, 'utf8');
const data = JSON.parse(rawData);

const found = data.find(item => item.slug === 'bryan-isd');
if (found) {
    console.log('Found bryan-isd in clients_dump.json!');
    console.log('Content Keys:', Object.keys(found));
    fs.writeFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync/bryan_isd_dumped.json', JSON.stringify(found, null, 2));
} else {
    console.log('bryan-isd NOT found in clients_dump.json');
}
