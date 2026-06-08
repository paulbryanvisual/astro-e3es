import * as sass from 'sass';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const sourceFile = path.join(__dirname, 'src', 'styles', 'global.scss');
const targetFile = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/editor-styles.css';

console.log(`Compiling ${sourceFile}...`);

try {
  const result = sass.compile(sourceFile, {
    style: 'expanded',
    sourceMap: false,
  });

  // Ensure the target directory exists
  const targetDir = path.dirname(targetFile);
  if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
  }

  // Prepend block editor wrapper scoping if desired or write as is
  // Gutenberg uses .editor-styles-wrapper for scoping enqueued styles automatically if supported, 
  // but writing it directly is robust. Our SCSS is BEM-based, so it will match the classes perfectly.
  fs.writeFileSync(targetFile, result.css);
  console.log(`Successfully synced compiled CSS to: ${targetFile}`);
} catch (error) {
  console.error('Compilation failed:', error);
  process.exit(1);
}
