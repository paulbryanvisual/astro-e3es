const fs = require('fs');
const http = require('http');

http.get('http://localhost:4008/clients/', (res) => {
  let html = '';
  res.on('data', chunk => html += chunk);
  res.on('end', () => {
    // extract body content
    const bodyMatch = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
    if (bodyMatch) {
      let body = bodyMatch[1];
      // strip script and style tags
      body = body.replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '');
      body = body.replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi, '');
      // print first 1500 characters of <main>
      const mainMatch = body.match(/<main[\s\S]*?<\/main>/i);
      if (mainMatch) {
        console.log(mainMatch[0].substring(0, 1500));
      } else {
        console.log("No <main> tag found. Body sample:\n", body.substring(0, 1500));
      }
    }
  });
});
