async function run() {
  const res = await fetch('http://e3es2026.local/wp-json/wp/v2/clients?per_page=100&_fields=id,slug,title');
  const clients = await res.json();
  console.log(`Loaded ${clients.length} clients:`);
  clients.forEach(c => {
    console.log(` - ${c.title.rendered} (${c.slug})`);
  });
}
run().catch(err => console.error(err));
