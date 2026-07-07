const fs = require('fs');
const css = fs.readFileSync('/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/wordpress-plugins/e3es-headless-helper/editor-styles.css', 'utf8');

// Find all matches of display: none in editor-styles.css
let pos = 0;
while ((pos = css.indexOf('display: none', pos)) !== -1) {
  console.log('--- FOUND display: none in editor-styles.css ---');
  console.log(css.substring(pos - 150, pos + 150));
  pos += 13;
}
