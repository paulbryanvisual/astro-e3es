import puppeteer from 'puppeteer-core';

async function test(url) {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });
  const page = await browser.newPage();
  
  let status = null;
  page.on('response', response => {
    if (response.url() === url) {
      status = response.status();
    }
  });

  try {
    await page.goto(url, { waitUntil: 'networkidle2' });
  } catch (e) {
    // Ignore navigation errors
  }
  
  await browser.close();
  return status;
}

async function run() {
  const urls = [
    'https://player.vimeo.com/video/1171901749?badge=0&autopause=0&player_id=0&app_id=58479',
    'https://player.vimeo.com/video/1171901749',
    'https://player.vimeo.com/video/1171901749?dnt=1',
    'https://player.vimeo.com/video/740399213',
    'https://player.vimeo.com/video/740399213?dnt=1'
  ];
  
  for (const url of urls) {
    const status = await test(url);
    console.log(`URL: ${url} -> Status: ${status}`);
  }
}

run().catch(console.error);
