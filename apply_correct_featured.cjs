const fs = require('fs');
const { execSync } = require('child_process');
const path = require('path');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = path.join(WP_DIR, 'wp-cli.phar');
const PHP_BIN = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';
const TMP_DIR = '/var/folders/mr/9ghm5gjx17ldyw50wg45cck80000gn/T/e3es-images';

if (!fs.existsSync(TMP_DIR)) {
    fs.mkdirSync(TMP_DIR, { recursive: true });
}

const mapping = JSON.parse(fs.readFileSync('featured_image_mapping.json', 'utf8'));

for (const [slug, imgUrl] of Object.entries(mapping)) {
    console.log(`Processing ${slug}...`);
    
    // Find local client post ID
    let postId;
    try {
        const idOutput = execSync(`"${PHP_BIN}" "${WP_CLI}" post list --name=${slug} --post_type=clients --field=ID --format=json`, { cwd: WP_DIR, encoding: 'utf8' });
        const ids = JSON.parse(idOutput);
        if (!ids || ids.length === 0) {
            console.log(`  -> Client ${slug} not found locally. Skipping.`);
            continue;
        }
        postId = ids[0];
    } catch (e) {
        console.log(`  -> Error finding ${slug}: ${e.message}`);
        continue;
    }
    
    // Download image
    const fileName = path.basename(new URL(imgUrl).pathname);
    const localPath = path.join(TMP_DIR, `${slug}-hero-${fileName}`);
    try {
        console.log(`  -> Downloading ${imgUrl}`);
        execSync(`curl -s -L -o "${localPath}" "${imgUrl}"`);
    } catch (e) {
        console.log(`  -> Failed to download image. Skipping.`);
        continue;
    }
    
    // Upload to WordPress
    let attachId;
    let attachUrl;
    try {
        const uploadOutput = execSync(`"${PHP_BIN}" "${WP_CLI}" media import "${localPath}" --porcelain`, { cwd: WP_DIR, encoding: 'utf8' });
        attachId = uploadOutput.trim();
        const urlOutput = execSync(`"${PHP_BIN}" "${WP_CLI}" post get ${attachId} --field=guid`, { cwd: WP_DIR, encoding: 'utf8' });
        attachUrl = urlOutput.trim();
        console.log(`  -> Uploaded as ID: ${attachId} (${attachUrl})`);
    } catch (e) {
        console.log(`  -> Failed to upload image. Skipping.`);
        continue;
    }
    
    // Set featured image
    try {
        execSync(`"${PHP_BIN}" "${WP_CLI}" post meta update ${postId} _thumbnail_id ${attachId}`, { cwd: WP_DIR });
        console.log(`  -> Updated _thumbnail_id`);
    } catch (e) {}
    
    // Update post_content to replace any old hero image with new hero image
    try {
        const contentOutput = execSync(`"${PHP_BIN}" "${WP_CLI}" post get ${postId} --field=post_content`, { cwd: WP_DIR, encoding: 'utf8' });
        let content = contentOutput;
        
        // Find the background image currently in the intro-banner block
        // It could be the taj mahal, or for Rio Hondo it could be rh-before-1.jpg
        // We'll replace the regex matching background-image inside the intro-banner block
        
        // 1. Replace the JSON attribute "bgImageUrl":"..."
        content = content.replace(/"bgImageUrl":"[^"]+"/, `"bgImageUrl":"${attachUrl}"`);
        
        // 2. Replace the style attribute background-image: ... url(...)
        content = content.replace(/background-image:linear-gradient\([^)]+\),\s*url\([^)]+\)/, `background-image:linear-gradient(rgba(33, 87, 52,0.85), rgba(33, 87, 52,0.85)), url(${attachUrl})`);
        
        // 3. Just in case, replace the img inside project-section__hero for custom blocks
        content = content.replace(/<img[^>]+class="project-section__hero-img"[^>]+src="([^"]+)"/, function(match, p1) {
            return match.replace(p1, attachUrl);
        });
        
        // 4. Update style="--hero-img:url(...)"
        content = content.replace(/--hero-img:url\([^)]+\)/, `--hero-img:url(${attachUrl})`);

        const contentPath = path.join(TMP_DIR, `${slug}-content-update.txt`);
        fs.writeFileSync(contentPath, content);
        
        execSync(`"${PHP_BIN}" "${WP_CLI}" post update ${postId} "${contentPath}"`, { cwd: WP_DIR });
        console.log(`  -> Updated post_content with new banner image`);
    } catch (e) {
        console.log(`  -> Failed to update post content: ${e.message}`);
    }
}

// flush cache
try {
    execSync(`"${PHP_BIN}" "${WP_CLI}" cache flush`, { cwd: WP_DIR });
    console.log(`Cache flushed.`);
} catch (e) {}

console.log('All done!');
