const puppeteer = require('puppeteer-core');

(async () => {
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: false,
    args: ['--disable-blink-features=AutomationControlled']
  });
  const page = await browser.newPage();
  
  const targets = [
    { slug: 'nocona-isd', url: 'https://txhslogoproject.com/nocona-indians/' },
    { slug: 'banquete-isd', url: 'https://txhslogoproject.com/banquete-bulldogs/' }
  ];
  
  for (const target of targets) {
    console.log(`\nNavigating to: ${target.url}...`);
    await page.goto(target.url, { waitUntil: 'networkidle2' });
    
    if (page.url().includes('sgcaptcha')) {
      console.log("⚠️ SiteGround CAPTCHA detected. Please solve in Chrome...");
      while (page.url().includes('sgcaptcha')) {
        await new Promise(r => setTimeout(r, 1000));
      }
      console.log("✅ CAPTCHA solved!");
    }
    
    // Extract primary logo image url
    const imgUrl = await page.evaluate(() => {
      // Find the image inside the main content area (often a link to the large file or post thumbnail)
      const links = Array.from(document.querySelectorAll('a[href*="/wp-content/uploads/"]'));
      for (const l of links) {
        if (l.href.includes('-large') || l.href.includes('logo')) {
          return l.href;
        }
      }
      const imgs = Array.from(document.querySelectorAll('img[src*="/wp-content/uploads/"]'));
      for (const img of imgs) {
        if (img.src.includes('-large') || img.src.includes('logo') || img.src.includes('Indians') || img.src.includes('Bulldogs')) {
          return img.src;
        }
      }
      return links.length > 0 ? links[0].href : (imgs.length > 0 ? imgs[0].src : null);
    });
    
    console.log(`Result for ${target.slug}: ${imgUrl}`);
  }
  
  await browser.close();
})();
