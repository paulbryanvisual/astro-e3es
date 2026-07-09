const fs = require('fs');

async function run() {
  const scraped = JSON.parse(fs.readFileSync('scratch/extracted_client_cards.json', 'utf8'));
  
  // Fetch local wordpress clients page 1 & 2
  const res1 = await fetch(`http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100&page=1&t=${Date.now()}`);
  const clients1 = await res1.json();
  
  const res2 = await fetch(`http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100&page=2&t=${Date.now()}`);
  let clients2 = [];
  if (res2.ok) {
    clients2 = await res2.json();
  }
  
  const localClients = [...clients1, ...clients2];
  
  console.log('Comparing images for the 25 clients...');
  
  const report = [];
  
  scraped.forEach(s => {
    const normalizedScrapedTitle = s.heading.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    const local = localClients.find(lc => {
      const normalizedLocalTitle = lc.title.rendered.toLowerCase().replace(/[^a-z0-9]/g, '');
      const normalizedLocalSlug = lc.slug.toLowerCase().replace(/[^a-z0-9]/g, '');
      return normalizedLocalTitle === normalizedScrapedTitle || 
             normalizedLocalSlug === normalizedScrapedTitle ||
             (s.heading === 'GOODALL-WITCHER HEALTHCARE' && lc.slug === 'goodall-witcher-hospital');
    });
    
    if (local) {
      const localFeatured = local._embedded?.['wp:featuredmedia']?.[0]?.source_url || 'none';
      
      // Extract file names from scraped and local
      const scrapedFilename = s.imgSrc.substring(s.imgSrc.lastIndexOf('/') + 1);
      const localFilename = localFeatured.substring(localFeatured.lastIndexOf('/') + 1);
      
      // Check if filenames (excluding scale or dimensions) look similar
      // e.g. 55182270675_296ab7a759_k-768x512.jpg vs boyd-isd-hero-55182270675_296ab7a759_k.jpg
      const scrapedClean = scrapedFilename.toLowerCase().replace(/-\d+x\d+/g, '').replace(/\.[a-z]+$/i, '');
      const localClean = localFilename.toLowerCase().replace(/-\d+x\d+/g, '').replace(/_scaled/g, '').replace(/-\d+$/g, '').replace(/\.[a-z]+$/i, '');
      
      let isMatch = false;
      if (scrapedClean === localClean) {
        isMatch = true;
      } else {
        // Check if one contains the other
        const part1 = scrapedClean.replace(/[^a-z0-9]/g, '');
        const part2 = localClean.replace(/[^a-z0-9]/g, '');
        if (part1.includes(part2) || part2.includes(part1)) {
          isMatch = true;
        }
      }
      
      report.push({
        title: s.heading,
        slug: local.slug,
        scrapedUrl: s.imgSrc,
        localUrl: localFeatured,
        scrapedFile: scrapedFilename,
        localFile: localFilename,
        status: isMatch ? 'MATCH' : 'MISMATCH'
      });
    } else {
      report.push({
        title: s.heading,
        slug: 'N/A',
        scrapedUrl: s.imgSrc,
        localUrl: 'N/A',
        scrapedFile: 'N/A',
        localFile: 'N/A',
        status: 'MISSING_LOCAL'
      });
    }
  });
  
  console.table(report.map(r => ({
    Title: r.title,
    Status: r.status,
    Scraped: r.scrapedFile,
    Local: r.localFile
  })));
  
  fs.writeFileSync('scratch/comparison_25.json', JSON.stringify(report, null, 2));
}

run().catch(err => console.error(err));
