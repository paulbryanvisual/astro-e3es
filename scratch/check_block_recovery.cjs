const puppeteer = require('puppeteer-core');
const fs = require('fs');

(async () => {
  // Recreate login.php for admin authentication
  console.log("🔑 Creating login.php...");
  fs.writeFileSync('/Users/bryanpaul/Local Sites/e3es2026/app/public/login.php', `<?php
require './wp-load.php';
wp_set_current_user(1);
wp_set_auth_cookie(1, true);
header("Location: http://e3es2026.local/wp-admin/post.php?post=12&action=edit");
exit;
`);

  console.log("🚀 Launching Chrome...");
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true,
    defaultViewport: { width: 1440, height: 1080 }
  });
  
  const page = await browser.newPage();
  
  console.log("🔑 Authenticating as admin via login.php...");
  await page.goto('http://e3es2026.local/login.php', { waitUntil: 'networkidle2' });
  
  console.log("⏳ Waiting for Gutenberg editor to load...");
  let loaded = false;
  try {
    await page.waitForSelector('.interface-interface-skeleton', { timeout: 20000 });
    console.log("✅ Editor loaded!");
    loaded = true;
  } catch (err) {
    console.log("❌ Timeout waiting for editor skeleton. Page title:", await page.title());
  }
  
  let invalidBlocksCount = 0;
  if (loaded) {
    // Wait a few seconds for all blocks to parse and validate
    console.log("⏳ Waiting for block validation...");
    await new Promise(r => setTimeout(r, 6000));
    
    // Check for the "Attempt recovery" button text or invalid block class
    invalidBlocksCount = await page.evaluate(() => {
      // 1. Look for blocks with .is-invalid-block class
      const invalidClasses = document.querySelectorAll('.is-invalid-block');
      if (invalidClasses.length > 0) return invalidClasses.length;
      
      // 2. Look for buttons containing "Attempt recovery" text
      const buttons = Array.from(document.querySelectorAll('button'));
      const recoveryButtons = buttons.filter(b => b.textContent && b.textContent.includes('Attempt recovery'));
      return recoveryButtons.length;
    });
  }
  
  console.log(`📊 Invalid/Recovery blocks found on page: ${invalidBlocksCount}`);
  
  const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/fd3a018d-6d66-4014-a832-26235d4188b8/boyd-isd-editor-screenshot.png';
  console.log(`📸 Taking screenshot...`);
  await page.screenshot({ path: screenshotPath, fullPage: false });
  console.log(`✅ Screenshot saved to: ${screenshotPath}`);
  
  await browser.close();
  
  // Clean up login.php for security
  console.log("🧹 Cleaning up login.php...");
  if (fs.existsSync('/Users/bryanpaul/Local Sites/e3es2026/app/public/login.php')) {
    fs.unlinkSync('/Users/bryanpaul/Local Sites/e3es2026/app/public/login.php');
  }
  
  if (!loaded) {
    console.log("❌ FAILED: The editor page could not be loaded!");
    process.exit(1);
  }
  
  if (invalidBlocksCount > 0) {
    console.log("❌ FAILED: Recovery warnings are still present!");
    process.exit(1);
  } else {
    console.log("🏆 SUCCESS: No block recovery warnings found!");
    process.exit(0);
  }
})();
