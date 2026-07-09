import puppeteer from 'puppeteer-core';

(async () => {
  try {
    const browser = await puppeteer.launch({
      executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.goto('http://localhost:4008/funding', { waitUntil: 'networkidle2' });

    const html = await page.evaluate(() => {
      const section = document.querySelector('section.db-feature--green');
      return section ? section.outerHTML : 'not found';
    });

    console.log('Outer HTML:', html);
    await browser.close();
  } catch (error) {
    console.error('Error:', error);
    process.exit(1);
  }
})();
