const fs = require('fs');

const html = fs.readFileSync('scratch/live_clients_raw.html', 'utf8');

// Find all hrefs inside the project grid items.
// Visual Composer grid items usually have links like:
// <a href="https://www.e3es.com/projects-item/..." class="vc_gitem-link"...
const matches = [...html.matchAll(/<a[^>]+href="([^"]*projects-item\/[^"]*)"[^>]*>/gi)].map(m => m[1]);
console.log(`Found ${matches.length} projects-item links:`);
const uniqueMatches = [...new Set(matches)];
console.log(JSON.stringify(uniqueMatches, null, 2));

// Let's print out specifically the links matching our 25 clients
const scraped = JSON.parse(fs.readFileSync('scratch/extracted_client_cards.json', 'utf8'));
const map = {};

scraped.forEach(s => {
  // Let's find any match in the html that contains the slug of this client
  const nameNorm = s.heading.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-');
  
  // Try to find a link that contains a part of the name
  const matchedLink = uniqueMatches.find(link => {
    const slug = link.replace(/\/$/, '').split('/').pop();
    return slug.includes(nameNorm) || nameNorm.includes(slug) || 
           (s.heading === 'GOODALL-WITCHER HEALTHCARE' && slug.includes('goodall-witcher'));
  });
  
  map[s.heading] = matchedLink || 'NOT_FOUND';
});

console.log('Client name to project page link mapping:');
console.log(JSON.stringify(map, null, 2));
fs.writeFileSync('scratch/live_project_links.json', JSON.stringify(map, null, 2));
