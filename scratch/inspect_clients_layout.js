import puppeteer from 'puppeteer-core';

async function run() {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  
  await page.goto('http://localhost:4008/clients/', { waitUntil: 'networkidle2' });
  
  const styles = await page.evaluate(() => {
    const mainEl = document.querySelector('main.clients-page');
    const contentEl = document.querySelector('.clients-page__content');
    const heroEl = document.querySelector('.db-page-hero');
    
    const getStyleInfo = (el, name) => {
      if (!el) return `${name} not found`;
      const cs = window.getComputedStyle(el);
      return {
        tag: el.tagName,
        classes: el.className,
        maxWidth: cs.maxWidth,
        width: cs.width,
        marginTop: cs.marginTop,
        paddingLeft: cs.paddingLeft,
        paddingRight: cs.paddingRight,
        marginLeft: cs.marginLeft,
        marginRight: cs.marginRight
      };
    };
    
    return {
      main: getStyleInfo(mainEl, 'main'),
      content: getStyleInfo(contentEl, 'content'),
      hero: getStyleInfo(heroEl, 'hero')
    };
  });
  
  console.log(JSON.stringify(styles, null, 2));
  await browser.close();
}

run().catch(console.error);
