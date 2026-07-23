const fs = require('fs');

async function run() {
  const url = 'http://e3es2026.local/wp-json/wp/v2/clients?per_page=100&_embed';
  const res = await fetch(url);
  const json = await res.json();
  
  console.log('Total clients fetched:', json.length);
  const featured = json.filter(c => {
    return c.meta && (c.meta._e3_client_show_in_index === '1' || c.meta._e3_client_show_in_index === 1 || c.meta._e3_client_show_in_index === true);
  });
  console.log('Total featured clients:', featured.length);
  if (featured.length > 0) {
    console.log('Example featured client meta:', featured[0].title.rendered, featured[0].meta);
  }
}

run();
