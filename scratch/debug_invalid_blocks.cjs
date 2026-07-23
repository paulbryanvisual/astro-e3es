const puppeteer = require('puppeteer-core');
const fs = require('fs');

(async () => {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  
  const page = await browser.newPage();
  
  fs.writeFileSync('/Users/bryanpaul/Local Sites/e3es2026/app/public/login.php', `<?php
require './wp-load.php';
wp_set_current_user(1);
wp_set_auth_cookie(1, true);
header("Location: http://e3es2026.local/wp-admin/post.php?post=12&action=edit");
exit;
`);

  await page.goto('http://e3es2026.local/login.php', { waitUntil: 'networkidle2' });
  await page.waitForSelector('.interface-interface-skeleton', { timeout: 15000 });
  await new Promise(r => setTimeout(r, 5000));
  
  const debugInfo = await page.evaluate(() => {
    const invalidList = [];
    const buttons = Array.from(document.querySelectorAll('button'));
    buttons.forEach((b, idx) => {
      if (b.textContent && b.textContent.includes('Attempt recovery')) {
        // Find nearest block container
        let parent = b.parentElement;
        let blockName = 'unknown';
        let blockLabel = '';
        while (parent) {
          if (parent.dataset && parent.dataset.type) {
            blockName = parent.dataset.type;
            break;
          }
          if (parent.className && parent.className.includes('wp-block')) {
            blockLabel = parent.className;
          }
          parent = parent.parentElement;
        }
        invalidList.push({
          type: 'button_match',
          index: idx,
          blockType: blockName,
          blockClass: blockLabel,
          text: b.textContent,
          html_context: b.outerHTML
        });
      }
    });
    
    return invalidList;
  });
  
  console.log("Debug Info of matches:", JSON.stringify(debugInfo, null, 2));
  
  await browser.close();
  
  if (fs.existsSync('/Users/bryanpaul/Local Sites/e3es2026/app/public/login.php')) {
    fs.unlinkSync('/Users/bryanpaul/Local Sites/e3es2026/app/public/login.php');
  }
})();
