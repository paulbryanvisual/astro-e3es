const fs = require('fs');
const html = fs.readFileSync('scratch/woodville_fetched.html', 'utf8');

const imgMatches = [...html.matchAll(/<img[^>]+src="([^"]+)"/gi)];
console.log('Image tags src attributes:');
imgMatches.forEach(m => console.log(' -', m[1]));

const styleMatches = [...html.matchAll(/url\(([^)]+)\)/gi)];
console.log('\nStyle background urls:');
styleMatches.forEach(m => console.log(' -', m[1]));
