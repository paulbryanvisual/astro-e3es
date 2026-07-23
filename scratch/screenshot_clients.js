import puppeteer from 'puppeteer-core';

async function run() {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto('http://localhost:4008/clients', { waitUntil: 'networkidle2' });
  
  // Wait 2 seconds for rendering
  await new Promise(resolve => setTimeout(resolve, 2000));
  
  // Scroll down a bit to see the next row of cards
  await page.evaluate(() => {
    window.scrollBy(0, 450);
  });
  await new Promise(resolve => setTimeout(resolve, 1000));
  
  const screenshotPath = '/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/clients-list-fix.png';
  await page.screenshot({ path: screenshotPath });
  console.log(`Saved screenshot to ${screenshotPath}`);
  await browser.close();
}

run();
