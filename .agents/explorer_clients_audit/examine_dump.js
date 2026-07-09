import fs from 'fs';

const dumpData = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json', 'utf8'));
const slugs = dumpData.map(c => c.slug);

const searchTerms = ['elm', 'keene', 'plano', 'stockdale', 'goodall', 'hondo', 'boyd', 'bryan'];

console.log('Searching dump slugs for keywords:');
searchTerms.forEach(term => {
    const matches = slugs.filter(s => s.includes(term));
    console.log(`  - Keyword "${term}" matches:`, matches);
});
