const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Config paths
const phpBinary = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';

async function main() {
  console.log("🔍 Fetching list of clients with external logos...");
  
  let clients = [];
  try {
    const rawResult = execSync(`"${phpBinary}" "${path.join(__dirname, 'query_external_logos.php')}"`);
    clients = JSON.parse(rawResult.toString());
  } catch (err) {
    console.error("Failed to query clients:", err.message);
    process.exit(1);
  }
  
  console.log(`Found ${clients.length} external logo links to process.`);
  if (clients.length === 0) {
    console.log("No external logos to process.");
    process.exit(0);
  }
  
  console.log("🚀 Starting Puppeteer session...");
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: false,
    args: ['--disable-blink-features=AutomationControlled']
  });
  const page = await browser.newPage();
  
  // Establish session
  console.log("Establishing session with txhslogoproject.com...");
  await page.goto('https://www.txhslogoproject.com/', { waitUntil: 'networkidle2' });
  
  if (page.url().includes('sgcaptcha')) {
    console.log("⚠️ SiteGround CAPTCHA detected. Please solve the CAPTCHA in the opened Chrome window...");
    while (page.url().includes('sgcaptcha')) {
      await new Promise(r => setTimeout(r, 1000));
    }
    console.log("✅ CAPTCHA solved! Session established.");
  }
  
  for (let i = 0; i < clients.length; i++) {
    const client = clients[i];
    console.log(`\n[${i+1}/${clients.length}] Processing: ${client.slug}...`);
    console.log(`  External URL: ${client.url}`);
    
    // Create unique name based on client slug to avoid collisons
    const ext = path.extname(client.url.split('?')[0]) || '.png';
    const filename = `${client.slug}-logo${ext}`;
    const tempPath = path.join(__dirname, `temp_${client.slug}_logo${ext}`);
    
    // Check if it already exists in media library and update metadata/content directly
    try {
      const checkCmd = `"${phpBinary}" "${path.join(__dirname, 'sideload_logo_helper.php')}" ${client.id} "${filename}" "check_only" "${client.url}"`;
      const checkOutput = execSync(checkCmd).toString();
      if (checkOutput.includes('SUCCESS_EXISTS')) {
        console.log(`  Already exists in media library. Sideload skipped.`);
        console.log(`  PHP Output: ${checkOutput.trim().replace(/\n/g, ' | ')}`);
        continue; // Skip Puppeteer download!
      }
    } catch (err) {
      // Proceed to download if it does not exist
    }
    
    try {
      // Download file inside browser context
      const base64 = await page.evaluate(async (imgUrl) => {
        const resp = await fetch(imgUrl);
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const blob = await resp.blob();
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onloadend = () => resolve(reader.result);
          reader.onerror = reject;
          reader.readAsDataURL(blob);
        });
      }, client.url);
      
      const buffer = Buffer.from(base64.split(',')[1], 'base64');
      fs.writeFileSync(tempPath, buffer);
      console.log(`  Downloaded successfully (${buffer.length} bytes)`);
      
      // Run PHP helper to sideload and update database post
      console.log(`  Sideloading to WordPress database...`);
      const helperCmd = `"${phpBinary}" "${path.join(__dirname, 'sideload_logo_helper.php')}" ${client.id} "${filename}" "${tempPath}" "${client.url}"`;
      const output = execSync(helperCmd).toString();
      console.log(`  PHP Output: ${output.trim().replace(/\n/g, ' | ')}`);
      
    } catch (err) {
      console.error(`  [ERROR] Failed for ${client.slug}:`, err.message);
    } finally {
      if (fs.existsSync(tempPath)) {
        fs.unlinkSync(tempPath);
      }
    }
  }
  
  await browser.close();
  console.log("\n🏁 Sideloading and database updates complete!");
}

main().catch(err => {
  console.error("Fatal error:", err);
});
