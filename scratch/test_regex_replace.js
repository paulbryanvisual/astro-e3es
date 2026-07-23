import fs from 'fs';

async function run() {
  const url = 'http://e3es2026.local/wp-json/wp/v2/pages?slug=clients&t=' + Date.now();
  const res = await fetch(url);
  const json = await res.json();
  let html = json[0].content.rendered;
  
  console.log('Original check:', html.includes('justify-content: space-between'));
  
  // Test regex replacement
  const regex = /(class="[^"]*client-card[^"]*"[^>]*style="[^"]*)justify-content:\s*space-between/gi;
  console.log('Regex match:', regex.test(html));
  
  // Reset regex index for subsequent test
  regex.lastIndex = 0;
  
  const replaced = html.replace(regex, '$1justify-content: flex-start');
  console.log('Replaced check:', replaced.includes('justify-content: space-between'));
}

run();
