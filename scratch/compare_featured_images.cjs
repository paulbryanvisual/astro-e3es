// Node script to compare original featured photos from dump with local WordPress featured photos.
const fs = require('fs');

async function compare() {
  const dump = JSON.parse(fs.readFileSync('./clients_dump.json', 'utf8'));
  const originalImages = {};

  dump.forEach(client => {
    const slug = client.slug;
    const content = client.content.rendered || '';
    
    // Find all image URLs in the content (allowing spaces and parentheses)
    // Matches src="http://..." or url(http://...) or url('http://...')
    const urls = [];
    const srcRegex = /src=["'](http:\/\/e3es2026\.local\/wp-content\/uploads\/[^"']+)["']/gi;
    let match;
    while ((match = srcRegex.exec(content)) !== null) {
      urls.push(match[1]);
    }
    
    const urlRegex = /url\(['"]?(http:\/\/e3es2026\.local\/wp-content\/uploads\/[^'"\)]+)['"]?\)/gi;
    while ((match = urlRegex.exec(content)) !== null) {
      urls.push(match[1]);
    }

    // Filter out client_logo, taj-mahal, and duplicates
    const clientImages = [...new Set(urls)].filter(u => 
      !u.includes('client_logo.png') && 
      !u.includes('taj-mahal-placeholder')
    );

    // The first matching image is generally the original featured hero image!
    originalImages[slug] = clientImages.length > 0 ? clientImages[0] : null;
  });

  console.log('Fetching local WordPress client cards...');
  const res = await fetch(`http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100&t=${Date.now()}`);
  if (!res.ok) {
    throw new Error(`Failed to fetch from WP: ${res.status}`);
  }
  const wpClients = await res.json();
  console.log(`Fetched ${wpClients.length} clients from local WP.`);

  const discrepancies = [];
  const matches = [];

  wpClients.forEach(client => {
    const slug = client.slug;
    const title = client.title.rendered;
    const origUrl = originalImages[slug];
    
    let localUrl = null;
    if (client._embedded && client._embedded['wp:featuredmedia'] && client._embedded['wp:featuredmedia'][0]) {
      localUrl = client._embedded['wp:featuredmedia'][0].source_url;
    }

    // Clean up urls for clean comparison (just compare filenames)
    const origFilename = origUrl ? decodeURIComponent(origUrl.split('/').pop().toLowerCase()) : null;
    const localFilename = localUrl ? decodeURIComponent(localUrl.split('/').pop().toLowerCase()) : null;

    if (!origFilename) {
      // No original image in dump content
      if (localFilename) {
        discrepancies.push({
          slug,
          title,
          status: 'Extra Local Image',
          orig: null,
          local: localFilename
        });
      }
    } else if (!localFilename) {
      discrepancies.push({
        slug,
        title,
        status: 'Missing Local Image',
        orig: origFilename,
        local: null
      });
    } else {
      // Check if local filename contains the original filename (allowing for wordpress resized suffixes or minor name tweaks)
      // e.g. "jason flowers - woodville isd-1024x683.png" vs "jason flowers - woodville isd.png"
      const cleanOrig = origFilename.replace(/\.[^/.]+$/, '').replace(/-\d+x\d+$/, '');
      const cleanLocal = localFilename.replace(/\.[^/.]+$/, '').replace(/-\d+x\d+$/, '');
      
      if (cleanLocal.includes(cleanOrig) || cleanOrig.includes(cleanLocal)) {
        matches.push({ slug, orig: origFilename, local: localFilename });
      } else {
        discrepancies.push({
          slug,
          title,
          status: 'Mismatch',
          orig: origFilename,
          local: localFilename
        });
      }
    }
  });

  console.log(`\nParity comparison complete:`);
  console.log(`- Exact/Similar Matches: ${matches.length}`);
  console.log(`- Discrepancies Found: ${discrepancies.length}`);

  if (discrepancies.length > 0) {
    console.log('\nList of Discrepancies:');
    discrepancies.forEach(d => {
      console.log(`- ${d.title} (${d.slug}): ${d.status}`);
      console.log(`  -> Dump:  ${d.orig}`);
      console.log(`  -> Local: ${d.local}`);
    });
    
    // Save discrepancies to a JSON file for processing
    fs.writeFileSync('scratch/image_discrepancies.json', JSON.stringify(discrepancies, null, 2));
    console.log('\nSaved discrepancies list to scratch/image_discrepancies.json');
  } else {
    console.log('\nAll client featured photos are perfectly synchronized between current site and Astro!');
  }
}

compare().catch(err => console.error(err));
