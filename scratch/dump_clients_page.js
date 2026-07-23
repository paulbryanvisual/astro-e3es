async function run() {
  const url = 'http://e3es2026.local/wp-json/wp/v2/pages?slug=clients&t=' + Date.now();
  const res = await fetch(url);
  const json = await res.json();
  const html = json[0].content.rendered;
  console.log('--- CONTENT LENGTH ---', html.length);
  // Find script tags
  const regex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
  let match;
  while ((match = regex.exec(html)) !== null) {
    console.log('Found script tag of length:', match[1].length);
    console.log(match[1].substring(0, 500) + '...');
  }
}
run();
