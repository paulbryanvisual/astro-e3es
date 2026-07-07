const fs = require('fs');

fetch('http://e3es2026.local/wp-json/wp/v2/services/1636')
  .then(res => res.json())
  .then(data => {
    console.log('=== REST API keys ===');
    console.log(Object.keys(data));
    console.log('=== CONTENT keys ===');
    if (data.content) console.log(Object.keys(data.content));
    console.log('=== CONTENT rendered ===');
    if (data.content) console.log(data.content.rendered);
  })
  .catch(err => console.error(err));
