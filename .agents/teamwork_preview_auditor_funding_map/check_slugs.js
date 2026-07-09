const WP_URL = 'http://e3es2026.local/wp-json/wp/v2';

async function main() {
  console.log('Fetching pages from WordPress...');
  try {
    const res = await fetch(`${WP_URL}/pages?per_page=100`);
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }
    const pages = await res.json();
    console.log(`Found ${pages.length} pages:`);
    pages.forEach(p => {
      const hasMapImg = p.content.rendered.includes('static-map-600x400.png');
      const hasSvg = p.content.rendered.includes('texas-map-svg');
      console.log(`- Slug: ${p.slug} | ID: ${p.id} | Link: ${p.link} | hasMapImg: ${hasMapImg} | hasSvg: ${hasSvg}`);
    });
  } catch (err) {
    console.error('Error fetching pages:', err);
  }
}

main();
