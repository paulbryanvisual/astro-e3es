const fs = require('fs');
const html = fs.readFileSync('scratch/live_clients_page.html', 'utf8');

console.log(`HTML Length: ${html.length}`);

// Find all image URLs containing "wp-content/uploads"
const matches = [];
const regex = /https:\/\/www\.e3es\.com\/wp-content\/uploads\/[^\s"'\)]+(\.png|\.jpg|\.jpeg|\.webp)/gi;
let m;
while ((m = regex.exec(html)) !== null) {
  matches.push(m[0]);
}

console.log(`Total upload images found: ${matches.length}`);
console.log("\nFirst 20 images:");
console.log([...new Set(matches)].slice(0, 20));

// Also let's search for "Banquete" or "Woodville" to see how they are rendered
const indexBanquete = html.toLowerCase().indexOf('banquete');
if (indexBanquete !== -1) {
  console.log("\nBanquete context:");
  console.log(html.substring(indexBanquete - 200, indexBanquete + 500));
} else {
  console.log("\nCould not find 'Banquete' on the live page.");
}
