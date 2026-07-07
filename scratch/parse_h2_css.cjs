const fs = require('fs');
const html = fs.readFileSync('scratch_water_4383.html', 'utf8');

const styleStart = html.indexOf('<style');
const styleEnd = html.indexOf('</style>');

if (styleStart !== -1 && styleEnd !== -1) {
  const styles = html.substring(styleStart, styleEnd);
  
  // Find all selectors containing "h2"
  const regex = /([^{}]*h2[^{}]*)\{([^{}]*)\}/g;
  let match;
  console.log('--- ALL CSS RULES TARGETING h2 ---');
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
