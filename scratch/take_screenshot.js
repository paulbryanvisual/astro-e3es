import puppeteer from 'puppeteer-core';
import path from 'path';

(async () => {
  try {
    console.log('Launching browser...');
    const browser = await puppeteer.launch({
      executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    console.log('Opening page...');
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 3500 });

    console.log('Navigating to http://localhost:4008/funding...');
    await page.goto('http://localhost:4008/funding', { waitUntil: 'networkidle2', timeout: 30000 });

    console.log('Taking screenshot...');
    const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/55065950-b276-4826-9dd6-d84948ef0582/scratch_original_full.png';
    await page.screenshot({ path: screenshotPath });

    console.log('Screenshot saved to:', screenshotPath);
    await browser.close();
  } catch (error) {
    console.error('Error taking screenshot:', error);
    process.exit(1);
  }
})();
