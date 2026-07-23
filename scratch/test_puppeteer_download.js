const puppeteer = require('puppeteer-core');
const fs = require('fs');

(async () => {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  
  const url = 'https://www.txhslogoproject.com/wp-content/uploads/2019/03/Rio-Hondo-Bobcats-large.png';
  console.log(`Navigating to site root to establish session...`);
  await page.goto('https://www.txhslogoproject.com/', { waitUntil: 'networkidle2' });
  
  console.log(`Fetching image inside browser context...`);
  try {
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
    }, url);
    
    const buffer = Buffer.from(base64.split(',')[1], 'base64');
    fs.writeFileSync('scratch/test_logo.png', buffer);
    console.log(`Success! Saved logo to scratch/test_logo.png. Size: ${buffer.length} bytes`);
  } catch (err) {
    console.error(`Failed to fetch image:`, err);
  }
  
  await browser.close();
})();
