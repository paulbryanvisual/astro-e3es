const fs = require('fs');
const html = fs.readFileSync('scratch_water_4383.html', 'utf8');

const styleStart = html.indexOf('<style');
const styleEnd = html.indexOf('</style>');

if (styleStart !== -1 && styleEnd !== -1) {
  const styles = html.substring(styleStart, styleEnd);
  
  // Find all selectors that match faq-section__title or faq-section__desc
  // Let's do a search for any occurrence of 'title' or 'desc' or 'faq-section' in selectors
  const regex = /([^{}]+)\{([^{}]+)\}/g;
  let match;
  console.log('--- ALL CSS RULES MATCHING SELECTORS ---');
  while ((match = regex.exec(styles)) !== null) {
    const selector = match[1].trim();
    const body = match[2].trim();
    if (selector.includes('title') || selector.includes('desc') || selector.includes('faq-section')) {
      console.log(`Selector: ${selector}`);
      console.log(`Body: ${body}`);
      console.log('----------------------------------------');
    }
  }
} else {
  console.log('No style tag found');
}
