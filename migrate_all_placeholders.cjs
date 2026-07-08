/**
 * Comprehensive migration: scrapes ALL client images from the live e3es.com site.
 * Handles pagination on /clients/ and falls back to /projects-item/[slug]/ for each client.
 */
const fs = require('fs');
const path = require('path');
const { execSync, execFileSync } = require('child_process');
const https = require('https');
const http = require('http');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = '/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar';
const PHP_BIN = '/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';
const TMP_DIR = path.join(require('os').tmpdir(), 'e3es-images');
const PLACEHOLDER_LIST = require('./still_placeholder.json');

if (!fs.existsSync(TMP_DIR)) fs.mkdirSync(TMP_DIR, { recursive: true });

function wp(args) {
    return execSync(`"${PHP_BIN}" "${WP_CLI}" ${args}`, { cwd: WP_DIR, encoding: 'utf8' }).trim();
}

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

function downloadFile(url, dest) {
    return new Promise((resolve, reject) => {
        execSync(`curl -s -L -o "${dest}" "${url}"`);
        resolve();
    });
}

// Extract the best large image URL from the live page HTML for a client
function extractHeroImageFromHtml(html, slug) {
    // 1. Try to get the grid featured image (class="s-img-switch wp-post-image")
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

    // 2. Try the main hero/featured large image from individual project page
    const largeImgMatches = [...html.matchAll(/<img[^>]+(?:fetchpriority="high"|class="[^"]*wp-post-image[^"]*")[^>]*>/gi)];
    for (const m of largeImgMatches) {
        const src = m[0].match(/\ssrc="([^"]+)"/);
        if (src && !src[1].includes('logo') && !src[1].includes('Logo') && !src[1].includes('150x150') && !src[1].includes('E3_Web') && !src[1].includes('footer')) {
            return src[1].replace(/-\d+x\d+(\.[a-z]+)$/i, '$1');
        }
    }

    // 3. Find any large image (width > 400) that isn't a logo/icon
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

// Build a master mapping from the grid pages first
async function buildMappingFromGrid() {
    const mapping = {};
    const pages = ['https://www.e3es.com/clients/', 'https://www.e3es.com/clients/page/2/'];
    
    for (const pageUrl of pages) {
        console.log(`Scraping grid: ${pageUrl}`);
        let html;
        try { html = await fetchUrl(pageUrl); } catch(e) { console.log(`  Failed: ${e.message}`); continue; }
        
        const chunks = html.split('<div class="project-grid-wrapper">');
        for (const chunk of chunks) {
            if (!chunk.includes('project-grid-item-title')) continue;
            const slugMatch = chunk.match(/href="[^"]*\/projects-item\/([^\/]+)\/?\"/);
            if (!slugMatch) continue;
            const slug = slugMatch[1];
            
            const imgUrl = extractHeroImageFromHtml(chunk, slug);
            if (imgUrl) mapping[slug] = imgUrl;
        }
    }
    console.log(`Found ${Object.keys(mapping).length} from grid pages.`);
    return mapping;
}

async function main() {
    const gridMapping = await buildMappingFromGrid();

    // Now process each placeholder client
    for (const { id, slug } of PLACEHOLDER_LIST) {
        console.log(`\nProcessing ${slug} (ID: ${id})...`);

        let imgUrl = gridMapping[slug] || null;

        // If not found in grid, try scraping their individual page
        if (!imgUrl) {
            const urls = [
                `https://www.e3es.com/projects-item/${slug}/`,
                `https://www.e3es.com/${slug}/`,
            ];
            for (const url of urls) {
                try {
                    const html = await fetchUrl(url);
                    imgUrl = extractHeroImageFromHtml(html, slug);
                    if (imgUrl) { console.log(`  -> Found image from ${url}`); break; }
                } catch(e) { /* skip */ }
            }
        } else {
            console.log(`  -> Using grid image: ${imgUrl}`);
        }

        if (!imgUrl) {
            console.log(`  -> No image found. Skipping.`);
            continue;
        }

        // Download
        const ext = path.extname(new URL(imgUrl).pathname) || '.jpg';
        const localPath = path.join(TMP_DIR, `${slug}-main${ext}`);
        try {
            console.log(`  -> Downloading ${imgUrl}`);
            await downloadFile(imgUrl, localPath);
        } catch(e) {
            console.log(`  -> Download failed: ${e.message}`);
            continue;
        }

        // Upload to WP
        let attachId, attachUrl;
        try {
            attachId = wp(`media import "${localPath}" --porcelain`);
            attachUrl = wp(`post get ${attachId} --field=guid`);
            console.log(`  -> Uploaded ID: ${attachId}`);
        } catch(e) {
            console.log(`  -> Upload failed: ${e.message}`);
            continue;
        }

        // Set featured image
        try {
            wp(`post meta update ${id} _thumbnail_id ${attachId}`);
        } catch(e) {}

        // Update post_content to replace Taj Mahal placeholder URL with new image
        try {
            let content = wp(`post get ${id} --field=post_content`);
            const escaped = attachUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            
            // Replace all occurrences of any url containing taj-mahal-placeholder.png
            content = content.replace(/https?:\/\/[^\s"')]+taj-mahal-placeholder\.png/gi, attachUrl);
            // Also replace bgImageUrl JSON attribute
            content = content.replace(/"bgImageUrl":"[^"]+"/g, `"bgImageUrl":"${attachUrl}"`);
            // Also update --hero-img css var
            content = content.replace(/--hero-img:url\([^)]+\)/g, `--hero-img:url(${attachUrl})`);
            // Also update style background-image url
            content = content.replace(/url\(http:\/\/e3es2026\.local\/wp-content\/uploads\/2026\/06\/taj-mahal-placeholder\.png\)/gi, `url(${attachUrl})`);

            const tmpContent = path.join(TMP_DIR, `${slug}-final-content.txt`);
            fs.writeFileSync(tmpContent, content);
            wp(`post update ${id} "${tmpContent}"`);
            console.log(`  -> Content updated.`);
        } catch(e) {
            console.log(`  -> Content update failed: ${e.message}`);
        }
    }

    // Flush cache
    try { wp('cache flush'); console.log('\nCache flushed.'); } catch(e) {}
    console.log('\nAll done!');
}

main().catch(console.error);
