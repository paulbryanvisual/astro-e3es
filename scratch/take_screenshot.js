import puppeteer from 'puppeteer-core';

async function run() {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 2000 });
  
  console.log('Navigating to http://localhost:4008/team...');
  await page.goto('http://localhost:4008/team', { waitUntil: 'networkidle2' });
  
  const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/0117dea9-e7ad-4194-afd1-ffe2f765a84f/team_page_screenshot.png';
  console.log(`Saving screenshot to ${screenshotPath}...`);
  await page.screenshot({ path: screenshotPath });
  
  await browser.close();
  console.log('Done!');
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
