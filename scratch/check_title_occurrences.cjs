const fs = require('fs');
const html = fs.readFileSync('scratch_water_4383.html', 'utf8');

// Find all occurrences of "faq-section__title" in the whole HTML file (both CSS and HTML)
let pos = 0;
while ((pos = html.indexOf('faq-section__title', pos)) !== -1) {
  console.log(`\n--- FOUND 'faq-section__title' at position ${pos} ---`);
  console.log(html.substring(pos - 150, pos + 250));
  pos += 18;
}
