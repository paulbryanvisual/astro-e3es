import puppeteer from 'puppeteer-core';

async function run() {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  
  const consoleMessages = [];
  page.on('console', msg => consoleMessages.push(`[CONSOLE] ${msg.type()}: ${msg.text()}`));
  
  const failedRequests = [];
  page.on('requestfailed', req => {
    failedRequests.push(`[FAILED REQUEST] ${req.url()} - ${req.failure().errorText}`);
  });
  
  page.on('response', response => {
    const status = response.status();
    if (response.url().includes('vimeo') && status >= 400) {
      failedRequests.push(`[VIMEO HTTP ERROR] ${response.url()} - Status: ${status}`);
    }
  });

  await page.goto('http://localhost:4008/clients/city-of-stockdale/', { waitUntil: 'networkidle2' });
  
  const iframeSrcs = await page.evaluate(() => {
    return Array.from(document.querySelectorAll('iframe')).map(iframe => ({
      src: iframe.src,
      outerHTML: iframe.outerHTML,
      offsetWidth: iframe.offsetWidth,
      offsetHeight: iframe.offsetHeight
    }));
  });

  console.log('--- IFRAME INFO ---');
  console.log(JSON.stringify(iframeSrcs, null, 2));
  
  console.log('\n--- CONSOLE MESSAGES ---');
  console.log(consoleMessages.join('\n'));
  
  console.log('\n--- FAILED/ERROR REQUESTS ---');
  console.log(failedRequests.join('\n'));

  await browser.close();
}

run().catch(console.error);
