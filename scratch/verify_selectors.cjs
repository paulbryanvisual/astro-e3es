const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

// Find faq-section markup
const startIdx = html.indexOf('<section class="wp-block-e3es-faq-section faq-section">');
if (startIdx !== -1) {
  const segment = html.substring(startIdx, startIdx + 1200);
  console.log('=== FAQ SECTION START SEGMENT ===');
  console.log(segment);
} else {
  console.log('FAQ section not found by exact string match!');
}
