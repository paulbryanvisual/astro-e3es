const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('clients_dump.json', 'utf8'));

const donna = dump.find(item => (item.slug || item.post_name) === 'donna-isd');
if (donna) {
    const content = donna.content.rendered || '';
    const idx = content.indexOf('wp-block-e3es-project project-section');
    if (idx !== -1) {
        console.log(content.substring(idx - 50, idx + 2000));
    }
}
