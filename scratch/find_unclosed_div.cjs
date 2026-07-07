const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

const startTag = '<div class="services-page__content"';
const startIdx = html.indexOf(startTag);
const endIdx = html.indexOf('</main>'); // Check up to the end of main

if (startIdx !== -1 && endIdx !== -1) {
  const content = html.substring(startIdx, endIdx);
  
  // Find all tags: <div, </div, <section, </section
  const regex = /<(\/?)(div|section)(?:\s+[^>]*?)?>/gi;
  let match;
  let depth = 0;
  const stack = [];
  
  console.log('--- SCANNING TAGS DEPTH ---');
  while ((match = regex.exec(content)) !== null) {
    const fullTag = match[0];
    const isClose = match[1] === '/';
    const tagName = match[2].toLowerCase();
    const idx = startIdx + match.index;
    
    if (isClose) {
      if (stack.length === 0) {
        console.log(`Error: Closed tag ${fullTag} with empty stack at position ${idx}`);
      } else {
        const popped = stack.pop();
        if (popped.tagName !== tagName) {
          console.log(`Mismatched close tag: Opened ${popped.tag} at ${popped.idx}, closed with ${fullTag} at ${idx}`);
        }
      }
    } else {
      stack.push({ tag: fullTag, tagName, idx });
    }
  }
  
  console.log('\n--- UNCLOSED TAGS LEFT IN STACK ---');
  stack.forEach(item => {
    console.log(`Opened at ${item.idx}:`);
    console.log(html.substring(item.idx, item.idx + 150));
    console.log('-----------------------------------');
  });
}
