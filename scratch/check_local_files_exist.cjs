const fs = require('fs');
const path = require('path');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const filesToCheck = [
  'Jason Flowers - Brownsville ISD 07142020.png',
  'Jason Flowers - Gainesville ISD.jpg',
  'Jason Flowers - Cooke County.jpg'
];

filesToCheck.forEach(filename => {
  const p = path.join(WP_DIR, 'wp-content/uploads/2026/06', filename);
  console.log(`Checking ${p}: ${fs.existsSync(p)}`);
});
