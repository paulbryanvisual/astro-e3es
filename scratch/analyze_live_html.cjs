const fs = require('fs');

const html = fs.readFileSync('scratch/live_clients_raw.html', 'utf8');

// Find all image tags
const images = [...html.matchAll(/<img[^>]+src="([^"]+)"/gi)].map(m => m[1]);
console.log(`Found ${images.length} image tags:`);
images.slice(0, 15).forEach(img => console.log(' - Image src:', img));

// Find any links containing "/clients/"
const clientLinks = [...html.matchAll(/<a[^>]+href="([^"]*clients\/[^"]*)"/gi)].map(m => m[1]);
console.log(`Found ${clientLinks.length} client links:`);
clientLinks.slice(0, 15).forEach(link => console.log(' - Client link:', link));

// Find all occurrences of the word "Boyd" or "Donna" or "Ricardo" to see how they are structured
const searchTerms = ['Boyd', 'Donna', 'Ricardo', 'Hondo'];
searchTerms.forEach(term => {
  const idx = html.indexOf(term);
  if (idx !== -1) {
    console.log(`Found "${term}" at index ${idx}. Snippet around it:`);
    console.log(html.substring(Math.max(0, idx - 150), Math.min(html.length, idx + 150)));
  } else {
    console.log(`"${term}" not found.`);
  }
});
