import fs from 'fs';

const htmlPath = '/Users/bryanpaul/Local Sites/astro-e3es/dist/clients/index.html';
if (!fs.existsSync(htmlPath)) {
    console.log('dist/clients/index.html does not exist.');
    process.exit(1);
}

const html = fs.readFileSync(htmlPath, 'utf8');

// Regex to capture links like href="/clients/slug/" or href="/clients/slug"
const linkRegex = /href="\/clients\/([^/"]+)\/?"[^>]*>([\s\S]*?)<\/a>/g;
const links = [];
let match;

while ((match = linkRegex.exec(html)) !== null) {
    const slug = match[1];
    const text = match[2].replace(/<[^>]+>/g, '').trim();
    if (slug && slug !== 'index.html' && !slug.startsWith('page')) {
        links.push({ slug, text });
    }
}

// Remove duplicates
const uniqueLinks = [];
const seenSlugs = new Set();
for (const link of links) {
    if (!seenSlugs.has(link.slug)) {
        seenSlugs.add(link.slug);
        uniqueLinks.push(link);
    }
}

console.log(`Total unique client links found in compiled HTML: ${uniqueLinks.length}`);
console.log('Unique Client links details:');
console.log(uniqueLinks);
