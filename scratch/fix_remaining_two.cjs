const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = '/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar';
const PHP_BIN = '/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';

function wp(args) {
  return execSync(`"${PHP_BIN}" "${WP_CLI}" ${args}`, { cwd: WP_DIR, encoding: 'utf8' }).trim();
}

const targets = [
  {
    slug: 'eagle-pass-isd',
    id: 1450,
    file: 'Jason Flowers - Eagle Pass ISD.png'
  },
  {
    slug: 'edgewood-isd',
    id: 1451,
    file: 'Jason Flowers - eisd.jpg'
  }
];

targets.forEach(t => {
  const localPath = path.join(WP_DIR, 'wp-content/uploads/2026/06', t.file);
  console.log(`Processing ${t.slug} (ID: ${t.id}) with file: ${t.file}`);
  
  if (!fs.existsSync(localPath)) {
    console.error(`Error: File does not exist at ${localPath}`);
    return;
  }
  
  try {
    console.log(`Importing media...`);
    // Escape shell arguments using string template or direct shell escaping if necessary, but here we can pass it clean
    const attachId = wp(`media import "${localPath}" --porcelain`);
    const attachUrl = wp(`post get ${attachId} --field=guid`).trim();
    console.log(`Imported successfully. ID: ${attachId}, URL: ${attachUrl}`);
    
    console.log(`Setting featured image...`);
    wp(`post meta update ${t.id} _thumbnail_id ${attachId}`);
    
    console.log(`Updating post content...`);
    let postContent = wp(`post get ${t.id} --field=post_content`);
    
    postContent = postContent.replace(/"bgImageUrl"\s*:\s*"[^"]+"/g, `"bgImageUrl":"${attachUrl}"`);
    postContent = postContent.replace(/background-image:linear-gradient\([^)]+\),\s*url\([^)]+\)/g, `background-image:linear-gradient(rgba(33, 87, 52,0.95), rgba(33, 87, 52,0.95)), url(${attachUrl})`);
    postContent = postContent.replace(/--hero-img:url\([^)]+\)/g, `--hero-img:url(${attachUrl})`);
    
    const tmpFile = path.join(WP_DIR, 'wp-content/uploads/2026/06', `temp_${t.slug}_content.txt`);
    fs.writeFileSync(tmpFile, postContent);
    wp(`post update ${t.id} "${tmpFile}"`);
    fs.unlinkSync(tmpFile);
    console.log(`Successfully updated ${t.slug}!\n`);
  } catch (e) {
    console.error(`Failed to process ${t.slug}: ${e.message}\n`);
  }
});

try {
  wp('cache flush');
  console.log('Cache flushed.');
} catch (e) {}
