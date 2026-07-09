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

    const styles = await page.evaluate(() => {
      const section = document.querySelector('section.db-feature--green');
      const wrapper = document.querySelector('.db-feature__image-wrapper');
      const svg = document.querySelector('#texas-map-svg');
      
      const getStyles = (el) => {
        if (!el) return null;
        const comp = window.getComputedStyle(el);
        return {
          display: comp.display,
          position: comp.position,
          overflow: comp.overflow,
          transform: comp.transform,
          width: comp.width,
          height: comp.height,
          zIndex: comp.zIndex,
          clipPath: comp.clipPath,
          margin: comp.margin,
          padding: comp.padding
        };
      };

      return {
        section: getStyles(section),
        wrapper: getStyles(wrapper),
        svg: getStyles(svg)
      };
    });

    console.log('Computed Styles:', JSON.stringify(styles, null, 2));
    await browser.close();
  } catch (error) {
    console.error('Error:', error);
    process.exit(1);
  }
})();
