const fs = require('fs');
const https = require('https');
const http = require('http');

function fetchUrl(url) {
    return new Promise((resolve, reject) => {
        const mod = url.startsWith('https') ? https : http;
        const req = mod.get(url, {
            headers: { 'User-Agent': 'Mozilla/5.0 (compatible; E3MigrationBot/1.0)' }
        }, (res) => {
            if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                return fetchUrl(res.headers.location).then(resolve).catch(reject);
            }
            if (res.statusCode !== 200) {
                res.resume();
                return reject(new Error(`HTTP ${res.statusCode} for ${url}`));
            }
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve(data));
        });
        req.on('error', reject);
        req.setTimeout(15000, () => { req.destroy(); reject(new Error('Timeout')); });
    });
}

function extractHeroImageFromHtml(html, slug) {
    const gridImgMatch = html.match(/<img[^>]+class="[^"]*s-img-switch[^"]*wp-post-image[^"]*"[^>]*>/);
    if (gridImgMatch) {
        const tag = gridImgMatch[0];
        const srcset = tag.match(/srcset="([^"]+)"/);
        if (srcset) {
            const sources = srcset[1].split(',').map(s => s.trim().split(' '));
            sources.sort((a, b) => parseInt(b[1] || '0') - parseInt(a[1] || '0'));
            if (sources[0][0]) return sources[0][0];
        }
        const src = tag.match(/\ssrc="([^"]+)"/);
        if (src) return src[1].replace(/-\d+x\d+(\.[a-z]+)$/i, '$1');
    }

    const largeImgMatches = [...html.matchAll(/<img[^>]+(?:fetchpriority="high"|class="[^"]*wp-post-image[^"]*")[^>]*>/gi)];
    for (const m of largeImgMatches) {
        const src = m[0].match(/\ssrc="([^"]+)"/);
        if (src && !src[1].includes('logo') && !src[1].includes('Logo') && !src[1].includes('150x150') && !src[1].includes('E3_Web') && !src[1].includes('footer')) {
            return src[1].replace(/-\d+x\d+(\.[a-z]+)$/i, '$1');
        }
    }

    const allImgs = [...html.matchAll(/<img[^>]+width="(\d+)"[^>]+src="([^"]+)"|<img[^>]+src="([^"]+)"[^>]+width="(\d+)"/gi)];
    for (const m of allImgs) {
        const width = parseInt(m[1] || m[4] || '0');
        const src = m[2] || m[3];
        if (width >= 600 && src && src.includes('wp-content/uploads') && !src.includes('logo') && !src.includes('Logo') && !src.includes('E3_Web') && !src.includes('footer') && !src.includes('150x150')) {
            return src.replace(/-\d+x\d+(\.[a-z]+)$/i, '$1');
        }
    }

    return null;
}

async function main() {
    const mapping = {};
    const pages = ['https://www.e3es.com/clients/', 'https://www.e3es.com/clients/page/2/'];
    
    for (const pageUrl of pages) {
        console.log(`Scraping grid: ${pageUrl}`);
        let html;
        try { html = await fetchUrl(pageUrl); } catch(e) { console.log(`  Failed: ${e.message}`); continue; }
        
        const chunks = html.split('<div class="project-grid-wrapper">');
        for (const chunk of chunks) {
            if (!chunk.includes('project-grid-item-title')) continue;
            // Let's print out what links/slugs are in the chunk
            const matches = [...chunk.matchAll(/href="[^"]*\/projects-item\/([^\/]+)\/?\"/g)];
            console.log(`Found hrefs in chunk:`, matches.map(m => m[1]));
            
            const slugMatch = chunk.match(/href="[^"]*\/projects-item\/([^\/]+)\/?\"/);
            if (!slugMatch) continue;
            const slug = slugMatch[1];
            
            const imgUrl = extractHeroImageFromHtml(chunk, slug);
            if (imgUrl) mapping[slug] = imgUrl;
        }
    }
    console.log('Mapping keys:', Object.keys(mapping));
}

main().catch(console.error);
