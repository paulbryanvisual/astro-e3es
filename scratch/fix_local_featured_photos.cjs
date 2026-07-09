// Node script to fix local client featured photos by correctly parsing filenames with spaces from the dump content.
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = '/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar';
const PHP_BIN = '/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';

const placeholders = JSON.parse(fs.readFileSync('./still_placeholder.json', 'utf8'));
const dump = JSON.parse(fs.readFileSync('./clients_dump.json', 'utf8'));

function wp(args) {
  return execSync(`"${PHP_BIN}" "${WP_CLI}" ${args}`, { cwd: WP_DIR, encoding: 'utf8' }).trim();
}

async function fixPhotos() {
  console.log(`Starting featured photo alignment for ${placeholders.length} clients...`);
  
  let fixedCount = 0;
  let skippedCount = 0;
  let fileNotFoundCount = 0;
  
  for (const client of placeholders) {
    const slug = client.slug;
    const postId = client.id;
    
    // Find client in dump
    const dumpClient = dump.find(c => c.slug === slug);
    if (!dumpClient) {
      console.log(`- ${slug} (${postId}): Not found in dump. Skipping.`);
      skippedCount++;
      continue;
    }
    
    const content = dumpClient.content.rendered || '';
    
    // 1. Try to find the correct image URL from content
    let imgUrl = null;
    
    // Check bgImageUrl JSON attribute
    const bgImageMatch = content.match(/"bgImageUrl"\s*:\s*"([^"]+)"/i);
    if (bgImageMatch) {
      imgUrl = bgImageMatch[1];
    }
    
    // Check background-image url(...)
    if (!imgUrl) {
      const urlMatch = content.match(/url\(['"]?(http:\/\/e3es2026\.local\/wp-content\/uploads\/[^'"\)]+)['"]?\)/i);
      if (urlMatch) {
        imgUrl = urlMatch[1];
      }
    }
    
    // Check img src (not client_logo, not taj-mahal)
    if (!imgUrl) {
      const srcMatches = [];
      const srcRegex = /src=["'](http:\/\/e3es2026\.local\/wp-content\/uploads\/[^"']+)["']/gi;
      let m;
      while ((m = srcRegex.exec(content)) !== null) {
        srcMatches.push(m[1]);
      }
      
      const firstValidImg = srcMatches.find(url => 
        !url.includes('client_logo.png') && 
        !url.includes('taj-mahal-placeholder.png')
      );
      if (firstValidImg) {
        imgUrl = firstValidImg;
      }
    }
    
    if (!imgUrl) {
      console.log(`- ${slug} (${postId}): No project image found in dump content.`);
      skippedCount++;
      continue;
    }
    
    // Standardize URL and extract filename (handle spaces and URL encoding)
    const relativePath = imgUrl.replace('http://e3es2026.local/wp-content/uploads/', '');
    const decodedRelativePath = decodeURIComponent(relativePath);
    let localPath = path.join(WP_DIR, 'wp-content/uploads', decodedRelativePath);
    
    // Check if file exists
    if (!fs.existsSync(localPath)) {
      // Fallback: search for file containing slug words in uploads folder
      const uploadsDir = path.join(WP_DIR, 'wp-content/uploads/2026/06');
      let foundAlt = false;
      if (fs.existsSync(uploadsDir)) {
        const files = fs.readdirSync(uploadsDir);
        const nameWords = slug.split('-');
        const altFile = files.find(f => 
          nameWords.every(word => f.toLowerCase().includes(word.toLowerCase()))
        );
        if (altFile) {
          localPath = path.join(uploadsDir, altFile);
          foundAlt = true;
          console.log(`  -> File not found at original path. Using alternative: ${altFile}`);
        }
      }
      
      if (!foundAlt) {
        console.log(`- ${slug} (${postId}): File not found on disk at ${localPath}`);
        fileNotFoundCount++;
        continue;
      }
    }
    
    console.log(`- Aligning ${slug} (${postId}) to image: ${path.basename(localPath)}`);
    
    // Import into media library and get ID/URL
    let attachId, attachUrl;
    try {
      attachId = wp(`media import "${localPath}" --porcelain`);
      attachUrl = wp(`post get ${attachId} --field=guid`).trim();
    } catch (e) {
      console.error(`  -> Failed to import media: ${e.message}`);
      continue;
    }
    
    // Set featured image
    try {
      wp(`post meta update ${postId} _thumbnail_id ${attachId}`);
    } catch (e) {
      console.error(`  -> Failed to set featured image: ${e.message}`);
      continue;
    }
    
    // Update post content intro-banner background and images
    try {
      let postContent = wp(`post get ${postId} --field=post_content`);
      
      // Update bgImageUrl attribute
      postContent = postContent.replace(/"bgImageUrl"\s*:\s*"[^"]+"/g, `"bgImageUrl":"${attachUrl}"`);
      
      // Update background-image style url
      postContent = postContent.replace(/background-image:linear-gradient\([^)]+\),\s*url\([^)]+\)/g, `background-image:linear-gradient(rgba(33, 87, 52,0.95), rgba(33, 87, 52,0.95)), url(${attachUrl})`);
      
      // Update --hero-img custom style properties
      postContent = postContent.replace(/--hero-img:url\([^)]+\)/g, `--hero-img:url(${attachUrl})`);
      
      // Update any inline styles using old url
      const escapedAttachUrl = attachUrl.replace(/\//g, '\\/');
      
      // Write to temp file and update content
      const tmpFile = path.join(WP_DIR, 'wp-content/uploads/2026/06', `temp_${slug}_content.txt`);
      fs.writeFileSync(tmpFile, postContent);
      wp(`post update ${postId} "${tmpFile}"`);
      fs.unlinkSync(tmpFile);
      
      console.log(`  -> Successfully updated post content & featured image.`);
      fixedCount++;
    } catch (e) {
      console.error(`  -> Failed to update post content: ${e.message}`);
    }
  }
  
  // Flush local WP cache
  try {
    wp('cache flush');
    console.log('\nCache flushed.');
  } catch (e) {}
  
  console.log(`\nAlignment Summary:`);
  console.log(`- Successfully Updated: ${fixedCount}`);
  console.log(`- Skipped: ${skippedCount}`);
  console.log(`- Files Not Found: ${fileNotFoundCount}`);
}

fixPhotos().catch(err => console.error(err));
