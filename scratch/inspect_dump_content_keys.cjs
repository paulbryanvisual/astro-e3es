const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('clients_dump.json', 'utf8'));

console.log('Type of content:', typeof dump[0].content);
console.log('Keys of content:', Object.keys(dump[0].content));
console.log('Snippet of content.rendered:\n', dump[0].content.rendered.substring(0, 500));
if (dump[0].content.raw) {
    console.log('Snippet of content.raw:\n', dump[0].content.raw.substring(0, 500));
}
