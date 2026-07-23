import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const posts = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/scratch/block_posts.json', 'utf8'));
const resultsFile = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/recovery_audit_results.json';

async function run() {
  console.log(`Starting robust sequential block validation audit for ${posts.length} posts...`);
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });

  const auditResults = [];

  for (let idx = 0; idx < posts.length; idx++) {
    const post = posts[idx];
    const url = `http://e3es2026.local/autologin.php?post=${post.id}`;
    console.log(`[${idx + 1}/${posts.length}] Checking ${post.post_type} "${post.title}" (ID: ${post.id})...`);

    const startTime = Date.now();
    let page = null;
    try {
      page = await browser.newPage();
      await page.setViewport({ width: 1440, height: 900 });

      // Use domcontentloaded for faster loads and to prevent getting stuck on websockets
      await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 25000 });
      
      // Wait for Gutenberg select API to be ready
      await page.waitForFunction(() => {
        return typeof window.wp !== 'undefined' &&
               typeof window.wp.data !== 'undefined' &&
               typeof window.wp.data.select('core/block-editor') !== 'undefined' &&
               window.wp.data.select('core/block-editor').getBlocks().length > 0;
      }, { timeout: 20000 });

      const postResult = await page.evaluate((post) => {
        const { select, dispatch } = window.wp.data;
        const { getBlocks } = select('core/block-editor');
        const { replaceBlocks } = dispatch('core/block-editor');
        const { getEditedPostContent } = select('core/editor');

        const beforeContent = getEditedPostContent();
        const initialBlocks = getBlocks();
        const invalidBlocks = initialBlocks.filter(b => !b.isValid);

        if (invalidBlocks.length === 0) {
          return null;
        }

        const beforeBlocksHtml = invalidBlocks.map(block => ({
          name: block.name,
          clientId: block.clientId,
          html: window.wp.blocks.serialize([block])
        }));

        // Trigger block recovery for each invalid block
        invalidBlocks.forEach(block => {
          const recovered = window.wp.blocks.rawHandler({
            HTML: window.wp.blocks.serialize([block])
          });
          replaceBlocks([block.clientId], recovered);
        });

        const afterContent = getEditedPostContent();

        // Get updated blocks after recovery
        const updatedBlocks = getBlocks();
        const afterBlocksHtml = beforeBlocksHtml.map(before => {
          const blockIndex = initialBlocks.findIndex(b => b.clientId === before.clientId);
          const newBlock = updatedBlocks[blockIndex];
          return {
            name: before.name,
            clientId: before.clientId,
            isValidNow: newBlock ? newBlock.isValid : null,
            html: newBlock ? window.wp.blocks.serialize([newBlock]) : ''
          };
        });

        return {
          id: post.id,
          title: post.title,
          slug: post.slug,
          post_type: post.post_type,
          invalidCount: invalidBlocks.length,
          beforeContent,
          afterContent,
          before: beforeBlocksHtml,
          after: afterBlocksHtml
        };
      }, post);

      const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
      if (postResult) {
        console.log(`  -> Found ${postResult.invalidCount} invalid block(s) in ${elapsed}s!`);
        auditResults.push(postResult);
        // Save incremental results
        fs.writeFileSync(resultsFile, JSON.stringify(auditResults, null, 2));
      } else {
        console.log(`  -> OK (${elapsed}s)`);
      }
    } catch (err) {
      const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
      console.error(`  -> Error checking ID ${post.id} after ${elapsed}s:`, err.message);
    } finally {
      if (page) {
        await page.close().catch(() => {});
      }
    }
  }

  await browser.close();
  console.log(`\nAudit complete! Found ${auditResults.length} posts with invalid blocks.`);
  console.log(`Results saved to ${resultsFile}`);
}

run().catch(err => {
  console.error(err);
});
