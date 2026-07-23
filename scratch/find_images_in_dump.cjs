const fs = require('fs');
const dump = JSON.parse(fs.readFileSync('clients_dump.json', 'utf8'));

const clientImages = {};

dump.forEach(item => {
    const slug = item.slug || item.post_name;
    const content = item.content.rendered || '';
    
    // Find all images in content
    const imgMatches = [...content.matchAll(/<img[^>]+src="([^"]+)"/gi)].map(m => m[1]);
    const bgMatches = [...content.matchAll(/background-image:[^;]*url\(([^)]+)\)/gi)].map(m => m[1]);
    const styleBgMatches = [...content.matchAll(/--hero-img:url\(([^)]+)\)/gi)].map(m => m[1]);
    const metaImg = item.meta?._e3_client_logo || '';
    
    const allImages = [...imgMatches, ...bgMatches, ...styleBgMatches];
    if (metaImg) allImages.push(metaImg);
    
    // Filter out E3 logos, templates, placeholders, and 150x150 icons
    const filtered = allImages.filter(img => {
        const lower = img.toLowerCase();
        return !lower.includes('logo') && 
               !lower.includes('taj-mahal') && 
               !lower.includes('150x150') && 
               !lower.includes('cropped-') &&
               !lower.includes('e3-background') &&
               !lower.includes('e3_web');
    });
    
    if (filtered.length > 0) {
        clientImages[slug] = filtered;
    }
});

fs.writeFileSync('scratch/dump_images_by_client.json', JSON.stringify(clientImages, null, 2));
console.log(`Found images for ${Object.keys(clientImages).length} clients in content.`);
console.log('Sample for woodville-isd:', clientImages['woodville-isd']);
