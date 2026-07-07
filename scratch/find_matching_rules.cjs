const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

// Extract all style tag contents
const styleTags = [];
let pos = 0;
while ((pos = html.indexOf('<style', pos)) !== -1) {
  const end = html.indexOf('</style>', pos);
  if (end !== -1) {
    styleTags.push(html.substring(html.indexOf('>', pos) + 1, end));
    pos = end + 8;
  } else {
    break;
  }
}

const allCss = styleTags.join('\n');

// Parse rules using a simple regex: selector { body }
const rules = [];
const regex = /([^{}]+)\{([^{}]+)\}/g;
let match;
while ((match = regex.exec(allCss)) !== null) {
  rules.push({
    selector: match[1].trim(),
    body: match[2].trim()
  });
}

// Find all rules that could apply to:
// 1. .faq-section__title (tag: h2, class: faq-section__title, parents: div.faq-section__container, section.faq-section, div.services-page__content, div.services-page__container, main.services-page, body, html)
// 2. .faq-section__desc (tag: p, class: faq-section__desc, parents: div.faq-section__desc-wrapper, div.faq-section__container, section.faq-section, ...)

const titleTargets = [
  'faq-section__title',
  'faq-section',
  'services-page',
  'h2'
];

console.log('=== RULES THAT MIGHT APPLY TO TITLE OR DESC ===');
rules.forEach(rule => {
  const sel = rule.selector;
  const isMatch = titleTargets.some(target => sel.includes(target));
  if (isMatch) {
    console.log(`Selector: ${sel}`);
    console.log(`Body: ${rule.body}`);
    console.log('----------------------------------------');
  }
});
