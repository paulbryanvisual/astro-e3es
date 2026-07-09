const fs = require('fs');

const html = fs.readFileSync('scratch/live_clients_raw.html', 'utf8');

// Let's find all blocks containing project-grid-item or similar
// Let's search for classes inside the HTML
const regex = /<div[^>]*class="[^"]*project-grid-item[^"]*"[^>]*>([\s\S]*?)<\/div>\s*<\/div>/gi;
const matches = [...html.matchAll(regex)];
console.log(`Found ${matches.length} project grid item blocks:`);

const results = [];

// If no project-grid-item blocks found, let's dump anything with class containing grid or item
matches.forEach((m, idx) => {
  const block = m[1];
  
  // Find title/name (usually in h3 or h4 or h2 or a tag)
  const headingMatch = block.match(/<(h[1-6]|div|span|a)[^>]*>(.*?)<\/\1>/i);
  const heading = headingMatch ? headingMatch[2].replace(/<[^>]+>/g, '').trim() : '';
  
  // Find image src
  const imgMatch = block.match(/<img[^>]+src="([^"]+)"/i);
  const imgSrc = imgMatch ? imgMatch[1] : '';
  
  // Find any text/metadata
  const metaMatches = [...block.matchAll(/<span[^>]*>(.*?)<\/span>/gi)].map(mm => mm[1].replace(/<[^>]+>/g, '').trim());
  
  results.push({
    index: idx + 1,
    heading,
    imgSrc,
    meta: metaMatches,
    rawLength: block.length
  });
});

console.log(JSON.stringify(results, null, 2));
fs.writeFileSync('scratch/extracted_client_cards.json', JSON.stringify(results, null, 2));

// If results are empty, let's search for all img tags and their immediate surroundings
if (results.length === 0) {
  console.log('No matches found. Let\'s output lines containing img tags to investigate.');
  const lines = html.split('\n');
  lines.forEach((line, idx) => {
    if (line.includes('wp-post-image') || line.includes('project-grid')) {
      console.log(`Line ${idx + 1}: ${line.trim().substring(0, 300)}`);
    }
  });
}
