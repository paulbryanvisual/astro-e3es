const fs = require('fs');
const html = fs.readFileSync('scratch_water_4383.html', 'utf8');

// Find all CSS rules that contain "faq-section"
const styleStart = html.indexOf('<style');
const styleEnd = html.indexOf('</style>');

if (styleStart !== -1 && styleEnd !== -1) {
  const styles = html.substring(styleStart, styleEnd);
  
  // Let's use a regular expression to find CSS blocks containing faq-section
  // A CSS block starts with a selector, has '{', and ends with '}'
  const regex = /([^{}]*faq-section[^{}]*)\{([^{}]*)\}/g;
  let match;
  console.log('--- ALL CSS RULES TARGETING FAQ-SECTION ---');
  while ((match = regex.exec(styles)) !== null) {
    const selector = match[1].trim();
    const body = match[2].trim();
    console.log(`Selector: ${selector}`);
    console.log(`Properties: ${body}`);
    console.log('-------------------------------------------');
  }
} else {
  console.log('No style tag found');
}
