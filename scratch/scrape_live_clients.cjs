const fs = require('fs');

async function scrape() {
  console.log('Fetching live clients page...');
  const res = await fetch('https://www.e3es.com/clients/');
  const html = await res.text();
  
  fs.writeFileSync('scratch/live_clients_raw.html', html);
  console.log('Saved raw HTML to scratch/live_clients_raw.html');
  
  // Let's find all grid items. Visual Composer grids typically have:
  // <div class="vc_grid-item vc_clearfix...
  // Let's scan for vc_grid-item or similar blocks
  const items = html.split('vc_grid-item');
  console.log(`Found ${items.length - 1} grid item segments.`);
  
  const extracted = [];
  
  for (let i = 1; i < items.length; i++) {
    const segment = items[i];
    // Find title/name
    const titleMatch = segment.match(/title="([^"]+)"/);
    const title = titleMatch ? titleMatch[1] : null;
    
    // Find background image URL
    const bgMatch = segment.match(/background-image:\s*url\(([^)]+)\)/i);
    const bgUrl = bgMatch ? bgMatch[1].replace(/&amp;/g, '&') : null;
    
    if (title || bgUrl) {
      extracted.push({ title, bgUrl });
    }
  }
  
  console.log('Extracted items:', JSON.stringify(extracted, null, 2));
  fs.writeFileSync('scratch/scraped_clients.json', JSON.stringify(extracted, null, 2));
}

scrape().catch(err => console.error(err));
