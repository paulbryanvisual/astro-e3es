const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

const startTag = '<div class="services-page__content"';
const faqTag = '<section class="wp-block-e3es-faq-section';

const startIdx = html.indexOf(startTag);
const faqIdx = html.indexOf(faqTag);

console.log('startIdx:', startIdx);
console.log('faqIdx:', faqIdx);

if (startIdx !== -1 && faqIdx !== -1) {
  const sub = html.substring(startIdx, faqIdx);
  
  // Let's count divs and sections
  let openDivs = 0;
  let closeDivs = 0;
  let pos = 0;
  while ((pos = sub.indexOf('<div', pos)) !== -1) {
    openDivs++;
    pos += 4;
  }
  pos = 0;
  while ((pos = sub.indexOf('</div', pos)) !== -1) {
    closeDivs++;
    pos += 5;
  }
  
  let openSections = 0;
  let closeSections = 0;
  pos = 0;
  while ((pos = sub.indexOf('<section', pos)) !== -1) {
    openSections++;
    pos += 8;
  }
  pos = 0;
  while ((pos = sub.indexOf('</section', pos)) !== -1) {
    closeSections++;
    pos += 10;
  }
  
  console.log('Open divs:', openDivs);
  console.log('Close divs:', closeDivs);
  console.log('Open sections:', openSections);
  console.log('Close sections:', closeSections);
}
