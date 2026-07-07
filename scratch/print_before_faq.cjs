const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

const faqIdx = html.indexOf('<section class="wp-block-e3es-faq-section');
if (faqIdx !== -1) {
  console.log('=== HTML BEFORE FAQ SECTION ===');
  console.log(html.substring(faqIdx - 1500, faqIdx));
} else {
  console.log('FAQ section not found');
}
