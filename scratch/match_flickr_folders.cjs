const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = `${WP_DIR}/wp-cli.phar`;
const PHP_BIN = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';
const FLICKR_DIR = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads';

// Get local posts
const localOutput = execSync(`"${PHP_BIN}" "${WP_CLI}" post list --post_type=clients --post_status=any --fields=ID,post_name --posts_per_page=200 --format=json`, { cwd: WP_DIR, encoding: 'utf8' });
const localClients = JSON.parse(localOutput);

// Read flickr folders
const folders = fs.readdirSync(FLICKR_DIR).filter(f => fs.statSync(path.join(FLICKR_DIR, f)).isDirectory());

// Stop-words to ignore in matching
const stopWords = new Set(['isd', 'cisd', 'consolidated', 'county', 'medical', 'center', 'hospital', 'public', 'schools', 'community', 'college', 'facilities', 'commission', 'district', 'of', 'the', 'and', 'city', 'state']);

function getKeywords(slug) {
    return slug.split('-').filter(w => w.length > 2 && !stopWords.has(w));
}

const matches = {};

localClients.forEach(c => {
    const slug = c.post_name;
    const keywords = getKeywords(slug);
    
    if (keywords.length === 0) {
        // Fallback to slug words
        keywords.push(...slug.split('-').filter(w => !stopWords.has(w)));
    }
    
    // Find matching folders
    const matched = folders.filter(f => {
        const folderLower = f.toLowerCase();
        // The folder must contain ALL core keywords
        return keywords.every(kw => folderLower.includes(kw));
    });
    
    matches[slug] = {
        id: c.ID,
        keywords,
        folders: matched
    };
});

fs.writeFileSync('scratch/matched_flickr_folders.json', JSON.stringify(matches, null, 2));

// Print summary
let matchCount = 0;
for (const [slug, info] of Object.entries(matches)) {
    if (info.folders.length > 0) {
        matchCount++;
        console.log(`${slug} -> ${info.folders.join(', ')}`);
    }
}
console.log(`\nMatched ${matchCount} out of ${localClients.length} clients.`);
