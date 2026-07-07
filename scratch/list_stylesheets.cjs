const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

let pos = 0;
console.log('--- LINK TAGS ---');
while ((pos = html.indexOf('<link', pos)) !== -1) {
  const end = html.indexOf('>', pos);
  console.log(html.substring(pos, end + 1));
  pos = end + 1;
}

pos = 0;
console.log('--- STYLE TAGS ---');
while ((pos = html.indexOf('<style', pos)) !== -1) {
  const end = html.indexOf('>', pos);
  console.log(html.substring(pos, end + 1));
  pos = end + 1;
}
