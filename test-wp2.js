const { getClients } = require('./src/lib/wordpress.js');
async function run() {
  const wpClients = await getClients();
  wpClients.forEach(client => {
    if (client.meta?._e3_client_project_url) {
      console.log(client.slug, client.meta._e3_client_project_url);
    }
  });
}
run();
