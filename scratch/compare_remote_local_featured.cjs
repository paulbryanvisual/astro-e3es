// Node script to compare client featured images from the staging API with the local API.
const fs = require('fs');

async function compareFeatured() {
  console.log('Fetching from staging API (current site)...');
  const stageRes = await fetch('https://descriptive-goldfish.flywheelstaging.com/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100');
  if (!stageRes.ok) {
    throw new Error(`Failed to fetch from staging: ${stageRes.status}`);
  }
  const stageClients = await stageRes.json();
  console.log(`Fetched ${stageClients.length} clients from staging.`);

  console.log('Fetching from local API (new Astro site)...');
  const localRes = await fetch('http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100');
  if (!localRes.ok) {
    throw new Error(`Failed to fetch from local: ${localRes.status}`);
  }
  const localClients = await localRes.json();
  console.log(`Fetched ${localClients.length} clients from local.`);

  const localMap = new Map(localClients.map(c => [c.slug, c]));
  const discrepancies = [];
  const matches = [];

  stageClients.forEach(stageClient => {
    const slug = stageClient.slug;
    const title = stageClient.title.rendered;
    
    let stageImg = null;
    if (stageClient._embedded && stageClient._embedded['wp:featuredmedia'] && stageClient._embedded['wp:featuredmedia'][0]) {
      stageImg = stageClient._embedded['wp:featuredmedia'][0].source_url;
    }

    const localClient = localMap.get(slug);
    if (!localClient) {
      discrepancies.push({
        slug,
        title,
        status: 'Missing in Local WordPress',
        stageImg,
        localImg: null
      });
      return;
    }

    let localImg = null;
    if (localClient._embedded && localClient._embedded['wp:featuredmedia'] && localClient._embedded['wp:featuredmedia'][0]) {
      localImg = localClient._embedded['wp:featuredmedia'][0].source_url;
    }

    const stageFilename = stageImg ? decodeURIComponent(stageImg.split('/').pop().toLowerCase()) : null;
    const localFilename = localImg ? decodeURIComponent(localImg.split('/').pop().toLowerCase()) : null;

    // Remove WordPress width/height suffixes (e.g. -1024x683) for a robust match
    const cleanStage = stageFilename ? stageFilename.replace(/\.[^/.]+$/, '').replace(/-\d+x\d+$/, '') : null;
    const cleanLocal = localFilename ? localFilename.replace(/\.[^/.]+$/, '').replace(/-\d+x\d+$/, '') : null;

    if (cleanStage === cleanLocal) {
      matches.push({ slug, title, stageImg, localImg });
    } else {
      discrepancies.push({
        slug,
        title,
        status: 'Mismatch',
        stageImg: stageFilename,
        localImg: localFilename,
        stageId: stageClient.featured_media,
        localId: localClient.featured_media
      });
    }
  });

  console.log(`\nComparison Summary:`);
  console.log(`- Identical Featured Photos: ${matches.length}`);
  console.log(`- Mismatched Featured Photos: ${discrepancies.length}`);

  if (discrepancies.length > 0) {
    console.log('\nMismatched Client Photos List:');
    discrepancies.forEach(d => {
      console.log(`- ${d.title} (${d.slug}):`);
      console.log(`  -> Staging (Current): ${d.stageImg}`);
      console.log(`  -> Local (New):     ${d.localImg}`);
    });

    fs.writeFileSync('scratch/remote_local_discrepancies.json', JSON.stringify(discrepancies, null, 2));
    console.log('\nSaved discrepancies to scratch/remote_local_discrepancies.json');
  } else {
    console.log('\nAll client featured photos are perfectly identical on staging and local Astro!');
  }
}

compareFeatured().catch(err => console.error(err));
