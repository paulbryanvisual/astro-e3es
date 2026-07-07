const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = path.join(WP_DIR, 'wp-cli.phar');
const PHP_BIN = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';
const TAJ = 'taj-mahal-placeholder.png';
const E3_LOGO = 'E3_WebLogo';

function wp(cmd) {
    return execSync(`"${PHP_BIN}" "${WP_CLI}" ${cmd}`, { cwd: WP_DIR, encoding: 'utf8' }).trim();
}

// Get all client IDs
const ids = wp(`post list --post_type=clients --posts_per_page=500 --field=ID --format=ids`).split(' ').filter(Boolean);

const stillPlaceholder = [];

for (const id of ids) {
    const thumbId = wp(`post meta get ${id} _thumbnail_id`).trim();
    if (!thumbId) continue;
    
    let thumbUrl = '';
    try {
        thumbUrl = wp(`post get ${thumbId} --field=guid`);
    } catch(e) { continue; }
    
    if (thumbUrl.includes(TAJ) || thumbUrl.includes(E3_LOGO)) {
        const slug = wp(`post get ${id} --field=post_name`);
        console.log(`PLACEHOLDER: ${slug} (ID: ${id}) -> ${thumbUrl}`);
        stillPlaceholder.push({ id, slug, thumbUrl });
    }
}

console.log(`\n${stillPlaceholder.length} clients still using placeholder.`);
fs.writeFileSync('still_placeholder.json', JSON.stringify(stillPlaceholder, null, 2));
