import puppeteer from 'puppeteer-core';

async function run() {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  await page.goto('http://localhost:4008/clients/raymondville-isd/', { waitUntil: 'networkidle2' });

  const breadcrumbs = await page.evaluate(() => {
    // Select elements within breadcrumb container
    const items = Array.from(document.querySelectorAll('.breadcrumbs__item, .breadcrumb-item, [class*="breadcrumb"]'));
    return items.map(el => {
      // Find direct links and dropdown list items inside this item
      const link = el.querySelector('a');
      const dropdownLinks = Array.from(el.querySelectorAll('[class*="dropdown"] a, ul a'));
      return {
        text: el.textContent.split('\n')[0].trim(),
        class: el.className,
        link: link ? { text: link.textContent.trim(), href: link.getAttribute('href') } : null,
        dropdown: dropdownLinks.map(a => ({ text: a.textContent.trim(), href: a.getAttribute('href') }))
      };
    });
  });

  console.log('--- BREADCRUMBS DROPDOWNS AUDIT ---');
  console.log(JSON.stringify(breadcrumbs, null, 2));
  await browser.close();
}

run();
