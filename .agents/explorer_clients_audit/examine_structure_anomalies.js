import fs from 'fs';

const data = JSON.parse(fs.readFileSync('client_structure_analysis.json', 'utf8'));

console.log('--- Client Posts with NO Project Block ---');
const noProject = data.filter(c => !c.hasProjectBlock);
noProject.forEach(c => {
    console.log(`  - Slug: ${c.slug}, Title: ${c.title}, Status: ${c.status}`);
});

console.log('\n--- Client Posts with Project Block but NO Description Before It ---');
const noDesc = data.filter(c => c.hasProjectBlock && !c.descriptionBeforeProject);
noDesc.forEach(c => {
    console.log(`  - Slug: ${c.slug}, Title: ${c.title}, Status: ${c.status}`);
});

console.log('\n--- Client Posts with Video ---');
const withVideo = data.filter(c => c.hasVideo);
withVideo.forEach(c => {
    console.log(`  - Slug: ${c.slug}, Title: ${c.title}, Videos:`, c.videoUrls);
});

console.log('\n--- Status of Mapped Images vs Local WP status ---');
const mappedSlugs = data.filter(c => c.inImgMapping);
console.log(`Total mapped: ${mappedSlugs.length}`);
console.log(`Mapped & Publish: ${mappedSlugs.filter(c => c.status === 'publish').length}`);
console.log(`Mapped & Draft: ${mappedSlugs.filter(c => c.status === 'draft').length}`);
const mappedDrafts = mappedSlugs.filter(c => c.status === 'draft');
console.log('Mapped but Draft slugs:', mappedDrafts.map(c => c.slug));
