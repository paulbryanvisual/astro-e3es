import puppeteer from 'puppeteer-core';
import path from 'path';

const slugs = ['mercedes-isd', 'needville-isd'];
const outputDir = '/Users/bryanpaul/.gemini/antigravity/brain/fd3a018d-6d66-4014-a832-26235d4188b8/visual_verification';

async function run() {
  console.log('Launching headless Chrome to capture remaining...');
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 1600 });

  for (const slug of slugs) {
    const url = `http://localhost:4008/clients/${slug}`;
    console.log(`Navigating to ${url}...`);
    try {
      await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
      await new Promise(resolve => setTimeout(resolve, 500));
      
      const screenshotPath = path.join(outputDir, `${slug}.png`);
      console.log(`Saving screenshot to ${screenshotPath}...`);
      await page.screenshot({ path: screenshotPath });
    } catch (err) {
      console.error(`Failed to capture ${slug}:`, err.message);
    }
  }

  await browser.close();
  console.log('Done capturing remaining!');
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
