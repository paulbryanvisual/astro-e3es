const fs = require('fs');

const dump = JSON.parse(fs.readFileSync('./clients_dump.json', 'utf8'));
const clientImages = {};

dump.forEach(client => {
  const slug = client.slug;
  const content = client.content.rendered || '';
  
  let imgUrl = null;
  
  // 1. Try to find bgImageUrl in JSON string
  const jsonMatch = content.match(/"bgImageUrl"\s*:\s*"([^"]+)"/i);
  if (jsonMatch) {
    imgUrl = jsonMatch[1];
  }
  
  // 2. Try to find url(http...) in style attribute
  if (!imgUrl) {
    const urlMatch = content.match(/url\(['"]?(http:\/\/e3es2026\.local\/wp-content\/uploads\/[^'"\)]+)['"]?\)/i);
    if (urlMatch) {
      imgUrl = urlMatch[1];
    }
  }
  
  // 3. Try to find any img src that is not client_logo or taj-mahal
  if (!imgUrl) {
    const srcMatch = content.match(/src=["'](http:\/\/e3es2026\.local\/wp-content\/uploads\/[^"']+)["']/i);
    if (srcMatch && !srcMatch[1].includes('client_logo') && !srcMatch[1].includes('taj-mahal')) {
      imgUrl = srcMatch[1];
    }
  }
  
  clientImages[slug] = {
    title: client.title.rendered,
    bgImageUrl: imgUrl
  };
});

fs.writeFileSync('scratch/dump_bg_images.json', JSON.stringify(clientImages, null, 2));

// Print clients where bgImageUrl is null or placeholder
console.log("Clients with missing or placeholder bgImageUrl in dump:");
Object.entries(clientImages).forEach(([slug, info]) => {
  if (!info.bgImageUrl || info.bgImageUrl.includes('client_logo') || info.bgImageUrl.includes('taj-mahal')) {
    console.log(`- ${slug}: ${info.bgImageUrl}`);
  }
});
