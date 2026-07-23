const fs = require('fs');
const { execSync } = require('child_process');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = `${WP_DIR}/wp-cli.phar`;
const PHP_BIN = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';

// Get local posts
const localOutput = execSync(`"${PHP_BIN}" "${WP_CLI}" post list --post_type=clients --post_status=any --fields=ID,post_name,post_status --posts_per_page=200 --format=json`, { cwd: WP_DIR, encoding: 'utf8' });
const localClients = JSON.parse(localOutput);

// Load dump
const dumpClients = JSON.parse(fs.readFileSync('clients_dump.json', 'utf8'));

const localSlugs = localClients.map(c => c.post_name);
const dumpSlugs = dumpClients.map(c => c.slug);

console.log('Local count:', localSlugs.length);
console.log('Dump count:', dumpSlugs.length);

const inLocalNotDump = localSlugs.filter(s => !dumpSlugs.includes(s));
const inDumpNotLocal = dumpSlugs.filter(s => !localSlugs.includes(s));

console.log('In Local but NOT in Dump:', inLocalNotDump);
console.log('In Dump but NOT in Local:', inDumpNotLocal);
