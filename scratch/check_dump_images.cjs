// Node script to parse clients_dump.json and extract the image URLs referenced in each client post.
const fs = require('fs');

const data = JSON.parse(fs.readFileSync('./clients_dump.json', 'utf8'));

console.log(`Total clients in dump: ${data.length}`);

const clientImages = {};

data.forEach(client => {
  const slug = client.slug;
  const content = client.content.rendered || '';
  
  // Try to find image URLs in the content
  // Look for uploads/2026/06/ or similar patterns
  const imageRegex = /http:\/\/e3es2026\.local\/wp-content\/uploads\/[^\s"'\)]+(\.png|\.jpg|\.jpeg|\.webp)/gi;
  const matches = content.match(imageRegex);
  
  // Also check if there's any featured_media or ACF client_logo/image
  const featuredMedia = client.featured_media;
  
  clientImages[slug] = {
    title: client.title.rendered,
    featured_media: featuredMedia,
    matches: matches ? [...new Set(matches)] : []
  };
});

// Output the first 10 clients to see what we got
const slugs = Object.keys(clientImages);
console.log("\nFirst 10 clients extraction:");
slugs.slice(0, 10).forEach(slug => {
  console.log(`- ${slug}: mediaId=${clientImages[slug].featured_media}, matches=${JSON.stringify(clientImages[slug].matches)}`);
});

fs.writeFileSync('scratch/dump_images_extracted.json', JSON.stringify(clientImages, null, 2));
console.log("\nSaved all to scratch/dump_images_extracted.json");
