import fs from 'fs';

async function scrapeFeaturedImages() {
    const res = await fetch('https://www.e3es.com/clients/');
    const html = await res.text();
    
    const mapping = {};
    
    // We want to match: <div class="project-grid-wrapper"> ... <img ... src="..." srcset="..." ...> ... <h4 class="project-grid-item-title"><a href=".../projects-item/SLUG/" ...>
    // Since HTML parsing with regex is brittle, let's split by '<div class="project-grid-wrapper">'
    const chunks = html.split('<div class="project-grid-wrapper">');
    
    for (const chunk of chunks) {
        if (!chunk.includes('project-grid-item-title')) continue;
        
        // Extract slug
        const slugMatch = chunk.match(/href="[^"]*\/projects-item\/([^\/]+)\/?\"/);
        if (!slugMatch) continue;
        const slug = slugMatch[1];
        
        // Extract srcset or src
        const imgMatch = chunk.match(/<img[^>]+class="[^"]*s-img-switch[^"]*"[^>]*>/);
        if (!imgMatch) continue;
        const imgTag = imgMatch[0];
        
        let imgUrl = '';
        const srcsetMatch = imgTag.match(/srcset="([^"]+)"/);
        if (srcsetMatch) {
            const srcset = srcsetMatch[1];
            const sources = srcset.split(',').map(s => s.trim().split(' '));
            sources.sort((a, b) => {
                const w1 = parseInt(a[1] || '0', 10);
                const w2 = parseInt(b[1] || '0', 10);
                return w2 - w1;
            });
            imgUrl = sources[0][0];
        } else {
            const srcMatch = imgTag.match(/src="([^"]+)"/);
            if (srcMatch) imgUrl = srcMatch[1];
        }
        
        if (imgUrl) {
            mapping[slug] = imgUrl;
        }
    }
    
    fs.writeFileSync('featured_image_mapping.json', JSON.stringify(mapping, null, 2));
    console.log(`Found ${Object.keys(mapping).length} client mappings`);
}

scrapeFeaturedImages();
