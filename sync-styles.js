import * as sass from 'sass';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const sourceFile = path.join(__dirname, 'src', 'styles', 'global.scss');

// The compiled editor stylesheet is written to BOTH destinations so the git
// repo (source of truth for the plugin) never drifts from the live WP install.
// Historically only the WP install was written, which forced manual
// "sync compiled editor-styles.css" commits in the website repo.
const targetFiles = [
  '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/editor-styles.css',
  '/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/wordpress-plugins/e3es-headless-helper/editor-styles.css',
];

console.log(`Compiling ${sourceFile}...`);

try {
  const result = sass.compile(sourceFile, {
    style: 'expanded',
    sourceMap: false,
  });

  // Gutenberg uses .editor-styles-wrapper for scoping enqueued styles automatically if supported,
  // but writing it directly is robust. Our SCSS is BEM-based, so it will match the classes perfectly.
  for (const targetFile of targetFiles) {
    const targetDir = path.dirname(targetFile);
    if (!fs.existsSync(targetDir)) {
      fs.mkdirSync(targetDir, { recursive: true });
    }
    fs.writeFileSync(targetFile, result.css);
    console.log(`Successfully synced compiled CSS to: ${targetFile}`);
  }
} catch (error) {
  console.error('Compilation failed:', error);
  process.exit(1);
}
