const fs = require('fs');
const legacy = JSON.parse(fs.readFileSync('src/data/legacy_clients.json', 'utf8'));
console.log('Legacy count:', legacy.length);
const legacySlugs = legacy.map(c => {
    const parts = c.url.split('/');
    return parts[parts.length - 2];
});
console.log('Legacy slugs:', legacySlugs.sort());
