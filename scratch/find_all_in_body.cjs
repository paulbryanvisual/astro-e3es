const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

// Find all matches of faq-section in the HTML body (excluding styles/head)
const bodyStart = html.indexOf('<body');
const bodyContent = bodyStart !== -1 ? html.substring(bodyStart) : html;

let pos = 0;
console.log('--- SEARCHING IN BODY ---');
while ((pos = bodyContent.indexOf('faq-section', pos)) !== -1) {
  console.log(`\nFound 'faq-section' in body at offset ${pos}:`);
  console.log(bodyContent.substring(pos - 150, pos + 250));
  pos += 11;
}
