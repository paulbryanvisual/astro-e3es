const fs = require('fs');

async function check() {
  const scraped = JSON.parse(fs.readFileSync('scratch/extracted_client_cards.json', 'utf8'));
  console.log(`Loaded ${scraped.length} scraped clients.`);
  
  // Fetch local wordpress clients page 1
  const res1 = await fetch(`http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100&page=1&t=${Date.now()}`);
  if (!res1.ok) {
    throw new Error('Failed to fetch local WordPress clients page 1');
  }
  const clients1 = await res1.json();
  
  // Fetch local wordpress clients page 2
  const res2 = await fetch(`http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100&page=2&t=${Date.now()}`);
  let clients2 = [];
  if (res2.ok) {
    clients2 = await res2.json();
  }
  
  const localClients = [...clients1, ...clients2];
  console.log(`Loaded ${localClients.length} local WordPress clients total.`);
  
  const matches = [];
  const missing = [];
  
  scraped.forEach(s => {
    const normalizedScrapedTitle = s.heading.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    const local = localClients.find(lc => {
      const normalizedLocalTitle = lc.title.rendered.toLowerCase().replace(/[^a-z0-9]/g, '');
      const normalizedLocalSlug = lc.slug.toLowerCase().replace(/[^a-z0-9]/g, '');
      return normalizedLocalTitle === normalizedScrapedTitle || normalizedLocalSlug === normalizedScrapedTitle;
    });
    
    if (local) {
      const localFeatured = local._embedded?.['wp:featuredmedia']?.[0]?.source_url || 'none';
      matches.push({
        title: s.heading,
        slug: local.slug,
        scrapedImg: s.imgSrc,
        localImg: localFeatured,
        localId: local.id
      });
    } else {
      missing.push(s.heading);
    }
  });
  
  console.log(`\nMatches found: ${matches.length} / ${scraped.length}`);
  console.log('Matches:', JSON.stringify(matches, null, 2));
  
  if (missing.length > 0) {
    console.log(`\nMissing locally: ${missing.length}`);
    console.log(missing);
  }
}

check().catch(err => console.error(err));
