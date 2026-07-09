import fs from 'fs';

const clients = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/local_wp_details.json', 'utf8'));

const targets = ['gwh', 'goodall-witcher-hospital', 'bryan-isd', 'caldwell-isd', 'carrizo-springs-cisd', 'donna-isd'];

for (const target of targets) {
    const c = clients.find(x => x.slug === target);
    if (c) {
        console.log(`=========================================`);
        console.log(`Slug: ${c.slug} | ID: ${c.id} | Status: ${c.status} | Title: ${c.title}`);
        console.log(`Featured Image URL: ${c.featured_image_url}`);
        console.log(`Content (first 500 chars):`);
        console.log(c.content ? c.content.substring(0, 500) + '...' : '[Empty]');
    } else {
        console.log(`Slug: ${target} not found!`);
    }
}
