const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('clients_dump.json', 'utf8'));

dump.forEach(item => {
    let imageUrl = '';
    if (item._embedded && item._embedded['wp:featuredmedia'] && item._embedded['wp:featuredmedia'][0]) {
        imageUrl = item._embedded['wp:featuredmedia'][0].source_url;
    }
    console.log(`${item.slug || item.post_name}: ${imageUrl || 'NO FEATURED IMAGE'}`);
});
