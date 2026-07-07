const fetch = require('node-fetch');
const cheerio = require('cheerio');
const fs = require('fs');

async function scrapeFeaturedImages() {
    const res = await fetch('https://www.e3es.com/clients/');
    const html = await res.text();
    const $ = cheerio.load(html);
    
    const mapping = {};
    
    // The grid items have class "portfolio-item"
    $('.portfolio-item').each((i, el) => {
        const link = $(el).find('.project-grid-item-title a').attr('href');
        if (!link) return;
        
        // extract slug from link (e.g. https://www.e3es.com/projects-item/rio-hondo-isd/)
        const slugMatch = link.match(/\/projects-item\/([^\/]+)\/?$/);
        if (!slugMatch) return;
        const slug = slugMatch[1];
        
        // Find the image
        const img = $(el).find('.project-grid-item-img img');
        let imgUrl = img.attr('src');
        
        // Try to get highest resolution from srcset if available
        const srcset = img.attr('srcset');
        if (srcset) {
            const sources = srcset.split(',').map(s => s.trim().split(' '));
            // sources is like [ ["url", "768w"], ["url2", "2048w"] ]
            // sort by width descending
            sources.sort((a, b) => {
                const w1 = parseInt(a[1] || '0', 10);
                const w2 = parseInt(b[1] || '0', 10);
                return w2 - w1;
            });
            imgUrl = sources[0][0];
        } else if (imgUrl) {
            // strip out -768x432.jpg if present
            imgUrl = imgUrl.replace(/-\d+x\d+(\.[a-zA-Z]+)$/, '$1');
        }
        
        if (imgUrl) {
            mapping[slug] = imgUrl;
        }
    });
    
    fs.writeFileSync('featured_image_mapping.json', JSON.stringify(mapping, null, 2));
    console.log(`Found ${Object.keys(mapping).length} client mappings`);
}

scrapeFeaturedImages();
