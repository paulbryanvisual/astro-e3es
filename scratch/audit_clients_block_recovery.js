import puppeteer from 'puppeteer-core';
import fs from 'fs';

const clients = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/scratch/client_ids.json', 'utf8'));

async function run() {
  console.log(`Starting block recovery audit for ${clients.length} clients...`);
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });

  for (let idx = 0; idx < clients.length; idx++) {
    const client = clients[idx];
    const url = `http://e3es2026.local/autologin.php?post=${client.id}`;
    console.log(`[${idx + 1}/${clients.length}] Auditing "${client.slug}" (ID: ${client.id})...`);

    let page = null;
    try {
      page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900 });

      await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 25000 });
      
      // Wait for Gutenberg select API to be ready
      await page.waitForFunction(() => {
        return typeof window.wp !== 'undefined' &&
               typeof window.wp.data !== 'undefined' &&
               typeof window.wp.data.select('core/block-editor') !== 'undefined' &&
               window.wp.data.select('core/block-editor').getBlocks().length > 0;
      }, { timeout: 20000 });

      // Run check & recovery
      const recoveredCount = await page.evaluate(() => {
        const { select, dispatch } = window.wp.data;
        const { getBlocks } = select('core/block-editor');
        const { replaceBlocks } = dispatch('core/block-editor');

        const initialBlocks = getBlocks();
        const invalidBlocks = initialBlocks.filter(b => !b.isValid);

        if (invalidBlocks.length === 0) {
          return 0;
        }

        // Trigger recovery
        invalidBlocks.forEach(block => {
          const recovered = window.wp.blocks.rawHandler({
            HTML: window.wp.blocks.serialize([block])
          });
          replaceBlocks([block.clientId], recovered);
        });

        return invalidBlocks.length;
      });

      if (recoveredCount > 0) {
        console.log(`  -> Recovered ${recoveredCount} block(s). Saving post...`);
        
        // Save post programmatically
        await page.evaluate(() => {
          window.wp.data.dispatch('core/editor').savePost();
        });
        
        // Wait for save to complete
        await page.waitForFunction(() => {
          return !window.wp.data.select('core/editor').isSavingPost();
        }, { timeout: 15000 });
        
        console.log(`  -> Saved successfully.`);
      } else {
        console.log(`  -> OK (No invalid blocks)`);
      }
    } catch (err) {
      console.error(`  -> Error auditing "${client.slug}" (ID: ${client.id}):`, err.message);
    } finally {
      if (page) {
        await page.close().catch(() => {});
      }
    }
  }

  await browser.close();
  console.log("\nBlock recovery audit complete.");
}

run().catch(err => {
  console.error(err);
});
