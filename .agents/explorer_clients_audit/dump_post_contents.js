import fs from 'fs';
import path from 'path';

const clients = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/local_wp_details.json', 'utf8'));

const targets = ['gwh', 'goodall-witcher-hospital', 'bryan-isd', 'caldwell-isd', 'carrizo-springs-cisd', 'donna-isd'];

for (const target of targets) {
    const c = clients.find(x => x.slug === target);
    if (c) {
        fs.writeFileSync(`${target}_content.html`, c.content || '');
        console.log(`Saved content for ${target} to ${target}_content.html`);
    } else {
        console.log(`Slug ${target} not found!`);
    }
}
