const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = '/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar';
const PHP_BIN = '/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';

const PLACEHOLDER_LIST = require('../../still_placeholder.json');
const DUMP_LIST = require('../../clients_dump.json');

function wp(args) {
    return execSync(`"${PHP_BIN}" "${WP_CLI}" ${args}`, { cwd: WP_DIR, encoding: 'utf8' }).trim();
}

async function main() {
    console.log(`Processing ${PLACEHOLDER_LIST.length} placeholders...`);
    
    let processedCount = 0;
    let skippedCount = 0;
    let missingFileCount = 0;
    
    for (const { id, slug } of PLACEHOLDER_LIST) {
        console.log(`\n--------------------------------------------`);
        console.log(`Processing ${slug} (ID: ${id})...`);
        
        // Find matching client in dump
        const dumpItem = DUMP_LIST.find(item => item.slug === slug || (item.post_name && item.post_name === slug));
        if (!dumpItem) {
            console.log(`  -> Not found in clients_dump.json. Skipping.`);
            skippedCount++;
            continue;
        }
        
        const contentHtml = dumpItem.content.rendered;
        
        // Extract all e3es2026.local uploads URLs from the HTML content
        // Match both encoded and unencoded URLs
        const regex = /http:\/\/e3es2026\.local\/wp-content\/uploads\/([^\s"'`<>]+)/gi;
        const matches = [...contentHtml.matchAll(regex)].map(m => m[0]);
        
        if (matches.length === 0) {
            console.log(`  -> No uploads URLs found in dump content for ${slug}. Skipping.`);
            skippedCount++;
            continue;
        }
        
        // Find the first image that is not client_logo.png
        let imgUrl = matches.find(url => !url.includes('client_logo.png') && (url.endsWith('.jpg') || url.endsWith('.jpeg') || url.endsWith('.png') || url.endsWith('.webp')));
        
        if (!imgUrl) {
            // Fallback to client_logo.png or any url
            imgUrl = matches[0];
        }
        
        // Clean up url (e.g. remove trailing tags or closing parens from regex)
        imgUrl = imgUrl.split(/[)'"`\s<>]/)[0];
        
        console.log(`  -> Target image URL from dump: ${imgUrl}`);
        
        // Map to local file path
        const relativePath = imgUrl.replace('http://e3es2026.local/wp-content/uploads/', '');
        // Decode URI component (handle %20, etc.)
        const decodedRelativePath = decodeURIComponent(relativePath);
        let localPath = path.join(WP_DIR, 'wp-content/uploads', decodedRelativePath);
        
        console.log(`  -> Checking local file path: ${localPath}`);
        
        if (!fs.existsSync(localPath)) {
            console.log(`  -> WARNING: Local file does not exist! Let's check if there is an alternative file in the uploads folder.`);
            // Try to find any file in uploads/2026/06 that contains the slug
            const uploads202606 = path.join(WP_DIR, 'wp-content/uploads/2026/06');
            let foundAlternative = false;
            if (fs.existsSync(uploads202606)) {
                const files = fs.readdirSync(uploads202606);
                // Look for files matching the slug or name
                const nameWords = slug.split('-');
                const matchingFile = files.find(f => {
                    return nameWords.every(word => f.toLowerCase().includes(word.toLowerCase()));
                });
                if (matchingFile) {
                    const alternativePath = path.join(uploads202606, matchingFile);
                    console.log(`  -> Found alternative matching file: ${alternativePath}`);
                    localPath = alternativePath;
                    foundAlternative = true;
                }
            }
            if (!foundAlternative) {
                console.log(`  -> No local file found for ${slug}. Skipping.`);
                missingFileCount++;
                continue;
            }
        }
        
        // Import the local file into WordPress media library
        let attachId, attachUrl;
        try {
            console.log(`  -> Importing local file to WP...`);
            attachId = wp(`media import "${localPath}" --porcelain`);
            attachUrl = wp(`post get ${attachId} --field=guid`);
            console.log(`  -> Successfully imported. Attachment ID: ${attachId}, URL: ${attachUrl}`);
        } catch(e) {
            console.log(`  -> Import failed: ${e.message}`);
            continue;
        }
        
        // Set featured image
        try {
            wp(`post meta update ${id} _thumbnail_id ${attachId}`);
            console.log(`  -> Set featured image thumbnail_id.`);
        } catch(e) {
            console.log(`  -> Failed to set featured image: ${e.message}`);
        }
        
        // Update post content to replace Taj Mahal placeholder URL with new image
        try {
            let content = wp(`post get ${id} --field=post_content`);
            
            // Replace all occurrences of any url containing taj-mahal-placeholder.png
            content = content.replace(/https?:\/\/[^\s"')]+taj-mahal-placeholder\.png/gi, attachUrl);
            // Also replace bgImageUrl JSON attribute
            content = content.replace(/"bgImageUrl":"[^"]+"/g, `"bgImageUrl":"${attachUrl}"`);
            // Also update --hero-img css var
            content = content.replace(/--hero-img:url\([^)]+\)/g, `--hero-img:url(${attachUrl})`);
            // Also update style background-image url
            content = content.replace(/url\(http:\/\/e3es2026\.local\/wp-content\/uploads\/2026\/06\/taj-mahal-placeholder\.png\)/gi, `url(${attachUrl})`);
            
            // Write updated content to a temp file and update post
            const tmpContentPath = path.join(WP_DIR, 'wp-content/uploads/2026/06', `${slug}-updated-content.txt`);
            fs.writeFileSync(tmpContentPath, content);
            wp(`post update ${id} "${tmpContentPath}"`);
            fs.unlinkSync(tmpContentPath);
            console.log(`  -> Content updated successfully.`);
            processedCount++;
        } catch(e) {
            console.log(`  -> Failed to update post content: ${e.message}`);
        }
    }
    
    // Flush cache
    try {
        wp('cache flush');
        console.log('\nCache flushed.');
    } catch(e) {}
    
    console.log(`\n============================================`);
    console.log(`Migration Summary:`);
    console.log(`- Processed: ${processedCount}`);
    console.log(`- Skipped: ${skippedCount}`);
    console.log(`- Missing Files: ${missingFileCount}`);
    console.log(`============================================`);
}

main().catch(console.error);
