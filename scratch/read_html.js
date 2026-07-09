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

    // Find the section and map element classes
    const data = await page.evaluate(() => {
      const section = document.querySelector('section.db-feature--green');
      const mapSvg = document.querySelector('#texas-map-svg');
      return {
        sectionClass: section ? section.className : 'not found',
        sectionOuterHtml: section ? section.outerHTML.substring(0, 500) : 'not found',
        mapSvgClass: mapSvg ? mapSvg.className : 'not found',
        mapSvgParentClass: mapSvg && mapSvg.parentElement ? mapSvg.parentElement.className : 'not found'
      };
    });

    console.log('HTML Data:', JSON.stringify(data, null, 2));
    await browser.close();
  } catch (error) {
    console.error('Error:', error);
    process.exit(1);
  }
})();
