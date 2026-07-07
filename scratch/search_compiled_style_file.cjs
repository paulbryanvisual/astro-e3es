const fs = require('fs');
const html = fs.readFileSync('scratch/current_water_page.html', 'utf8');

const idx = html.indexOf('#ff0000');
if (idx !== -1) {
  console.log('FOUND #ff0000 in current_water_page.html!');
  console.log(html.substring(idx - 100, idx + 100));
} else {
  console.log('NOT FOUND #ff0000 in current_water_page.html!');
}
