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
    await page.setViewport({ width: 1440, height: 2000 });

    console.log('Navigating to http://localhost:4008/clients...');
    await page.goto('http://localhost:4008/clients', { waitUntil: 'networkidle2', timeout: 30000 });

    // Wait 2 seconds for any animations or client-side renders to stabilize
    await new Promise(resolve => setTimeout(resolve, 2000));

    console.log('Taking screenshot...');
    const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/be7206aa-ed54-4a04-b6ec-162ead04af7a/clients_page_screenshot.png';
    await page.screenshot({ path: screenshotPath });

    console.log('Screenshot saved to:', screenshotPath);
    await browser.close();
  } catch (error) {
    console.error('Error taking screenshot:', error);
    process.exit(1);
  }
})();
