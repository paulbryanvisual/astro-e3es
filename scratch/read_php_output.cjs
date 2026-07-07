const http = require('http');
const fs = require('fs');

http.get('http://e3es2026.local/dump_post_1636.php', (res) => {
  let data = '';
  res.on('data', (chunk) => {
    data += chunk;
  });
  res.on('end', () => {
    console.log('=== RAW JSON RESPONSE ===');
    console.log(data);
    fs.writeFileSync('scratch/raw_db_json.json', data, 'utf8');
  });
}).on('error', (err) => {
  console.error('Error fetching:', err);
});
