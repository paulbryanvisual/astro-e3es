async function run() {
  const url = 'http://e3es2026.local/wp-json/wp/v2/pages?slug=clients&t=' + Date.now();
  const res = await fetch(url);
  const json = await res.json();
  const html = json[0].content.rendered;
  fs.writeFileSync('scratch/clients_content.html', html);
  console.log('Saved to scratch/clients_content.html');
}
import fs from 'fs';
run();
