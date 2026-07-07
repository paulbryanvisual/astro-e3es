const fs = require('fs');
const html = fs.readFileSync('/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/legacy-html/project-history.html', 'utf8');
const matches = html.match(/href="([^"]+)"/g);
const set = new Set();
if(matches) {
  matches.forEach(m => set.add(m.replace('href="', '').replace('"', '')));
}
console.log(Array.from(set).filter(l => l.includes('-isd') || l.includes('county')));
