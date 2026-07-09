import fs from 'fs';

const dumpPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
const data = JSON.parse(fs.readFileSync(dumpPath, 'utf8'));

console.log(`Total clients in clients_dump.json: ${data.length}`);

const statusCounts = {};
const list = [];

for (const client of data) {
    statusCounts[client.status] = (statusCounts[client.status] || 0) + 1;
    list.push({
        id: client.id,
        slug: client.slug,
        title: client.title?.rendered,
        status: client.status,
        region: client.region
    });
}

console.log('Status counts:', statusCounts);
console.log('Client list sample (first 10):');
console.log(list.slice(0, 10));

// Save full list for our analysis
fs.writeFileSync('parsed_dump_clients.json', JSON.stringify(list, null, 2));
