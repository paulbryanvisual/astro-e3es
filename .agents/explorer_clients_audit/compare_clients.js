import fs from 'fs';

const dumpPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
const localWpPath = '/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/local_wp_clients.json';
const featImgPath = '/Users/bryanpaul/Local Sites/astro-e3es/featured_image_mapping.json';
const placeholderPath = '/Users/bryanpaul/Local Sites/astro-e3es/still_placeholder.json';

const dumpData = JSON.parse(fs.readFileSync(dumpPath, 'utf8'));
const localWpData = JSON.parse(fs.readFileSync(localWpPath, 'utf8'));
const featImgData = JSON.parse(fs.readFileSync(featImgPath, 'utf8'));
const placeholderData = JSON.parse(fs.readFileSync(placeholderPath, 'utf8'));

// 1. Get lists of slugs
const dumpSlugs = dumpData.map(c => c.slug);
const localWpPublishSlugs = localWpData.filter(c => c.post_status === 'publish').map(c => c.post_name);
const localWpDraftSlugs = localWpData.filter(c => c.post_status === 'draft').map(c => c.post_name);
const featImgSlugs = Object.keys(featImgData);
const placeholderSlugs = placeholderData.map(c => c.slug);

console.log(`JSON dump: ${dumpSlugs.length} clients`);
console.log(`Local WP Publish: ${localWpPublishSlugs.length} clients`);
console.log(`Local WP Draft: ${localWpDraftSlugs.length} clients`);
console.log(`Featured Image Mapped: ${featImgSlugs.length} clients`);
console.log(`Placeholder Slugs: ${placeholderSlugs.length} clients`);

console.log('\n--- Live Clients vs Local WP Publish ---');
// Let's identify the 100 dump clients.
// Are they the live clients?
// Let's check which dump slugs are NOT in local WP publish:
const dumpNotInLocalPublish = dumpSlugs.filter(s => !localWpPublishSlugs.includes(s));
console.log(`Dump slugs not in local WP Publish (${dumpNotInLocalPublish.length}):`, dumpNotInLocalPublish);

// Which local WP publish are NOT in dump:
const localPublishNotInDump = localWpPublishSlugs.filter(s => !dumpSlugs.includes(s));
console.log(`Local WP Publish slugs not in dump (${localPublishNotInDump.length}):`, localPublishNotInDump);

// Let's check if the dump slugs are drafts locally:
const dumpInLocalDraft = dumpSlugs.filter(s => localWpDraftSlugs.includes(s));
console.log(`Dump slugs that are drafts locally (${dumpInLocalDraft.length}):`, dumpInLocalDraft);

// Let's check if there are dump slugs not in local WP at all:
const dumpNotInLocalAtAll = dumpSlugs.filter(s => !localWpPublishSlugs.includes(s) && !localWpDraftSlugs.includes(s));
console.log(`Dump slugs not in local WP at all (${dumpNotInLocalAtAll.length}):`, dumpNotInLocalAtAll);

// Let's write the detailed lists to files
const comparison = {
    dumpSlugs,
    localWpPublishSlugs,
    localWpDraftSlugs,
    featImgSlugs,
    placeholderSlugs,
    dumpNotInLocalPublish,
    localPublishNotInDump,
    dumpInLocalDraft,
    dumpNotInLocalAtAll
};
fs.writeFileSync('comparison_results.json', JSON.stringify(comparison, null, 2));
