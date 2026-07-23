const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('clients_dump.json', 'utf8'));
console.log(JSON.stringify(dump[0], null, 2));
