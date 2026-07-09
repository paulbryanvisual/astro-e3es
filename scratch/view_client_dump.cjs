const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('./clients_dump.json', 'utf8'));

const slugs = ['brownsville-isd', 'gainesville-isd', 'cooke-county'];

slugs.forEach(slug => {
  const client = dump.find(c => c.slug === slug);
  if (client) {
    console.log(`\n=================== ${slug} ===================`);
    console.log(`Title: ${client.title.rendered}`);
    console.log(`Featured Media: ${client.featured_media}`);
    console.log(`Content excerpt: ${client.content.rendered.substring(0, 1000)}`);
  } else {
    console.log(`\nClient ${slug} not found in dump.`);
  }
});
