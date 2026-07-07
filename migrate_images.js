import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import os from 'os';

const WP_URL = 'http://e3es2026.local/wp-json/wp/v2/clients';
const PHP_BIN = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';
const WP_CLI = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-cli.phar';
const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const TMP_DIR = path.join(os.tmpdir(), 'e3es-images');

const nonLegacyClients = [
    'big-sandy-isd', 'carrizo-springs-cisd', 'ennis-isd', 'gainesville-isd', 'granbury-isd',
    'hondo-isd', 'idea-public-schools', 'ingram-isd', 'keene-isd', 'little-elm-isd',
    'lyford-isd', 'mercedes-isd', 'pilot-point-isd', 'plano-isd', 'poolville-isd',
    'robstown-isd', 'sanger-isd', 'santa-fe-isd', 'waxahachie-isd', 'weslaco-isd',
    'city-of-stockdale', 'cooke-county', 'hardin-county', 'texas-facilities-commission',
    'glen-rose-medical-center', 'goodall-witcher-hospital', 'gwh', 'north-texas-medical-center',
    'houston-community-college', 'san-jacinto-community-college'
];

if (!fs.existsSync(TMP_DIR)) fs.mkdirSync(TMP_DIR);

async function run() {
    console.log("Fetching local clients...");
    const res = await fetch(`${WP_URL}?_embed=1&per_page=100`);
    const clients = await res.json();
    
    for (const client of clients) {
        if (nonLegacyClients.includes(client.slug)) continue;
        
        let hasTajMahal = false;
        if (client._embedded && client._embedded['wp:featuredmedia'] && client._embedded['wp:featuredmedia'][0]) {
            const featuredUrl = client._embedded['wp:featuredmedia'][0].source_url;
            if (featuredUrl.includes('taj-mahal-placeholder') || featuredUrl.includes('E3_WebLogo')) {
                hasTajMahal = true;
            }
        } else {
            hasTajMahal = true; // no featured media
        }
        
        if (!hasTajMahal) continue;
        
        console.log(`Processing client: ${client.slug}`);
        
        let liveUrl = `https://www.e3es.com/projects-item/${client.slug}/`;
        let html = '';
        try {
            const pageRes = await fetch(liveUrl, { redirect: 'follow' });
            if (!pageRes.ok) {
                // Try fallback
                const fbRes = await fetch(`https://www.e3es.com/${client.slug}/`, { redirect: 'follow' });
                if (!fbRes.ok) {
                    console.log(`  -> Failed to fetch live page for ${client.slug}. Skipping.`);
                    continue;
                }
                html = await fbRes.text();
            } else {
                html = await pageRes.text();
            }
        } catch (e) {
            console.log(`  -> Error fetching ${liveUrl}: ${e.message}`);
            continue;
        }

        const imgRegex = /<img[^>]+src="([^">]+)"/g;
        let match;
        const images = new Set();
        while ((match = imgRegex.exec(html)) !== null) {
            const src = match[1];
            if (src.includes('uploads') && !src.includes('E3_WebLogo') && !src.includes('logo')) {
                // Ignore small thumbnails
                if (!src.match(/-\d+x\d+\.(png|jpg|jpeg|gif|webp)$/)) {
                    images.add(src);
                }
            }
        }
        
        const imgArray = Array.from(images).slice(0, 3); 
        if (imgArray.length === 0) {
            console.log(`  -> No usable images found for ${client.slug}.`);
            continue;
        }
        
        let featuredImageId = null;
        let galleryIds = [];
        let first = true;
        
        for (const imgUrl of imgArray) {
            const filename = path.basename(imgUrl).replace(/\?.*/, '');
            const localPath = path.join(TMP_DIR, `${client.slug}-${filename}`);
            
            console.log(`  -> Downloading ${imgUrl}`);
            try {
                const imgRes = await fetch(imgUrl);
                const buffer = await imgRes.arrayBuffer();
                fs.writeFileSync(localPath, Buffer.from(buffer));
                
                // Upload via WP-CLI
                const cmd = `"${PHP_BIN}" "${WP_CLI}" media import "${localPath}" --porcelain`;
                const attachmentId = execSync(cmd, { cwd: WP_DIR, encoding: 'utf8' }).trim();
                console.log(`  -> Uploaded as ID: ${attachmentId}`);
                
                if (first) {
                    featuredImageId = attachmentId;
                    first = false;
                } else {
                    galleryIds.push(attachmentId);
                }
            } catch (err) {
                console.log(`  -> Failed to download/upload ${imgUrl}: ${err.message}`);
            }
        }
        
        if (featuredImageId) {
            console.log(`  -> Setting featured image...`);
            // Use post meta update for thumbnail
            execSync(`"${PHP_BIN}" "${WP_CLI}" post meta update ${client.id} _thumbnail_id ${featuredImageId}`, { cwd: WP_DIR });
            
            if (galleryIds.length > 0) {
                const allIds = [featuredImageId, ...galleryIds];
                let blockHtml = `<!-- wp:gallery {"linkTo":"none"} -->\n<figure class="wp-block-gallery has-nested-images columns-default is-cropped">`;
                for (const id of allIds) {
                    const getUrlCmd = `"${PHP_BIN}" "${WP_CLI}" post get ${id} --field=guid`;
                    const src = execSync(getUrlCmd, { cwd: WP_DIR, encoding: 'utf8' }).trim();
                    blockHtml += `\n<!-- wp:image {"id":${id},"sizeSlug":"large","linkDestination":"none"} -->\n<figure class="wp-block-image size-large"><img src="${src}" alt="" class="wp-image-${id}"/></figure>\n<!-- /wp:image -->`;
                }
                blockHtml += `\n</figure>\n<!-- /wp:gallery -->\n\n`;
                
                const contentPath = path.join(TMP_DIR, `${client.slug}-content.txt`);
                const getCmd = `"${PHP_BIN}" "${WP_CLI}" post get ${client.id} --field=post_content`;
                const rawContent = execSync(getCmd, { cwd: WP_DIR, encoding: 'utf8' });
                
                // Check if we already appended a gallery so we don't duplicate
                if (!rawContent.includes('wp-block-gallery')) {
                    fs.writeFileSync(contentPath, rawContent + "\n" + blockHtml);
                    const updateCmd = `"${PHP_BIN}" "${WP_CLI}" post update ${client.id} "${contentPath}"`;
                    execSync(updateCmd, { cwd: WP_DIR });
                    console.log(`  -> Updated content with gallery.`);
                }
            }
        }
        console.log(`  -> Done with ${client.slug}`);
    }
    console.log("Migration complete!");
}

run();
