const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('./clients_dump.json', 'utf8'));

const client = dump.find(c => c.slug === 'eagle-pass-isd');
if (client) {
  console.log("Eagle Pass ISD content in dump:");
  console.log(JSON.stringify(client.content.rendered, null, 2));
} else {
  console.log("Not found.");
}
