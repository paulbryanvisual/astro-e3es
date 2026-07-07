const fs = require('fs');
const html = fs.readFileSync('scratch_water_4383.html', 'utf8');

const styleStart = html.indexOf('<style');
const styleEnd = html.indexOf('</style>');

if (styleStart !== -1 && styleEnd !== -1) {
  const styles = html.substring(styleStart, styleEnd);
  
  const searchTerms = ['.faq-section__title', '.faq-section__desc-wrapper', '.faq-section__desc', '.faq-section'];
  for (const term of searchTerms) {
    let pos = 0;
    console.log(`\n================================ SEARCHING FOR: ${term} ================================`);
    while ((pos = styles.indexOf(term, pos)) !== -1) {
      console.log(`--- FOUND MATCH FOR ${term} at position ${pos} ---`);
      console.log(styles.substring(pos - 100, pos + 250));
      pos += term.length;
    }
  }
} else {
  console.log('No style tag found');
}
