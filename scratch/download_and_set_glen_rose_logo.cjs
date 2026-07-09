const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = '/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar';
const PHP_BIN = '/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';

function wp(args) {
  return execSync(`"${PHP_BIN}" "${WP_CLI}" ${args}`, { cwd: WP_DIR, encoding: 'utf8' }).trim();
}

async function run() {
  const logoUrl = 'https://www.e3es.com/wp-content/uploads/2025/07/grh_logo_sq-300x300.png';
  const destDir = path.join(WP_DIR, 'wp-content/uploads/2026/06');
  const destPath = path.join(destDir, 'grh_logo_sq-300x300.png');
  
  console.log(`Downloading Glen Rose logo from ${logoUrl}...`);
  const res = await fetch(logoUrl);
  if (!res.ok) {
    throw new Error(`Failed to download logo: ${res.status}`);
  }
  const arrayBuffer = await res.arrayBuffer();
  fs.writeFileSync(destPath, Buffer.from(arrayBuffer));
  console.log(`Downloaded logo to ${destPath}`);
  
  console.log(`Importing logo to media library...`);
  const attachId = wp(`media import "${destPath}" --porcelain`);
  const attachUrl = wp(`post get ${attachId} --field=guid`).trim();
  console.log(`Imported successfully. ID: ${attachId}, URL: ${attachUrl}`);
  
  console.log(`Setting logo meta for Glen Rose Medical Center (ID: 1458)...`);
  wp(`post meta update 1458 _e3_client_logo ${attachUrl}`);
  console.log('Successfully updated logo meta!');
  
  try {
    wp('cache flush');
    console.log('Cache flushed.');
  } catch (e) {}
}

run().catch(err => console.error(err));
