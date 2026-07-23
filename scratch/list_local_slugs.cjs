const { execSync } = require('child_process');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = `${WP_DIR}/wp-cli.phar`;
const PHP_BIN = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';

const output = execSync(`"${PHP_BIN}" "${WP_CLI}" post list --post_type=clients --post_status=any --fields=ID,post_name,post_status --posts_per_page=200 --format=json`, { cwd: WP_DIR, encoding: 'utf8' });
const clients = JSON.parse(output);

console.log('Count:', clients.length);
clients.forEach(c => {
    console.log(`${c.ID} - ${c.post_name} (${c.post_status})`);
});
