// Using native fetch in Node 26.

async function run() {
  console.log('Querying WordPress database via local REST API...');
  
  let allClients = [];
  let page = 1;
  let hasMore = true;
  
  while (hasMore) {
    const url = `http://e3es2026.local/wp-json/wp/v2/clients?per_page=100&page=${page}&_embed`;
    const response = await fetch(url);
    if (!response.ok) {
      console.log(`Failed to fetch page ${page}: HTTP ${response.status}`);
      break;
    }
    const clients = await response.json();
    if (clients.length === 0) {
      break;
    }
    allClients = allClients.concat(clients);
    console.log(`Page ${page} fetched: ${clients.length} clients`);
    if (clients.length < 100) {
      hasMore = false;
    } else {
      page++;
    }
  }
  
  console.log(`\nTotal client posts found in WP DB: ${allClients.length}`);
  
  const statuses = {};
  const showInIndexCounts = { true: 0, false: 0, missing: 0 };
  const placeholders = [];
  const excludedSlugs = [];
  
  allClients.forEach(c => {
    // Check status
    const status = c.status || 'unknown';
    statuses[status] = (statuses[status] || 0) + 1;
    
    // Check _e3_client_show_in_index
    const show = c.meta ? c.meta._e3_client_show_in_index : undefined;
    if (show === true || show === '1' || show === 1) {
      showInIndexCounts.true++;
    } else if (show === false || show === '0' || show === 0) {
      showInIndexCounts.false++;
      excludedSlugs.push(c.slug);
    } else {
      showInIndexCounts.missing++;
    }
    
    // Check for placeholders in content
    const content = c.content ? c.content.rendered : '';
    if (content.includes('taj-mahal-placeholder') || content.includes('taj-mahal')) {
      placeholders.push(c.slug);
    }
  });
  
  console.log('\nStatus Breakdown:');
  console.log(JSON.stringify(statuses, null, 2));
  
  console.log('\nShow In Index Meta Breakdown:');
  console.log(JSON.stringify(showInIndexCounts, null, 2));
  
  console.log('\nAll Slugs and Show Status:');
  const allSlugsStatus = allClients.map(c => ({
    slug: c.slug,
    show: c.meta ? c.meta._e3_client_show_in_index : null
  }));
  console.log(JSON.stringify(allSlugsStatus, null, 2));
}

run().catch(err => {
  console.error('Error running audit:', err);
});

