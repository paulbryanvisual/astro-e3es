const fs = require('fs');
const html = fs.readFileSync('scratch_water_4383.html', 'utf8');

const faqIdx = html.indexOf('<section class="wp-block-e3es-faq-section');
if (faqIdx !== -1) {
  console.log('=== HTML OF FAQ SECTION IN scratch_water_4383.html ===');
  console.log(html.substring(faqIdx, faqIdx + 1500));
} else {
  console.log('FAQ section not found in scratch_water_4383.html');
}
