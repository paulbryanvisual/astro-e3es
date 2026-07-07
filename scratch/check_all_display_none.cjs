const fs = require('fs');
const html = fs.readFileSync('scratch_water_4383.html', 'utf8');

// Find all matches of display: none in the whole file
let pos = 0;
while ((pos = html.indexOf('display: none', pos)) !== -1) {
  console.log('--- FOUND display: none ---');
  console.log(html.substring(pos - 150, pos + 150));
  pos += 13;
}

// Find all matches of visibility: hidden
pos = 0;
while ((pos = html.indexOf('visibility: hidden', pos)) !== -1) {
  console.log('--- FOUND visibility: hidden ---');
  console.log(html.substring(pos - 150, pos + 150));
  pos += 18;
}

// Find all matches of opacity: 0
pos = 0;
while ((pos = html.indexOf('opacity: 0', pos)) !== -1) {
  console.log('--- FOUND opacity: 0 ---');
  console.log(html.substring(pos - 150, pos + 150));
  pos += 10;
}
