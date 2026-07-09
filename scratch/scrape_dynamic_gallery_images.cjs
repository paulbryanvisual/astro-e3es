const puppeteer = require('puppeteer-core');
const fs = require('fs');

const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

const liveUrls = {
  "boyd-isd": "https://www.e3es.com/projects-item/boyd-isd/",
  "bryan-isd": "https://www.e3es.com/projects-item/bryan-isd/",
  "caldwell-isd": "https://www.e3es.com/projects-item/caldwell-isd-2/",
  "carrizo-springs-cisd": "https://www.e3es.com/projects-item/carrizo-springs-consolidated-isd/",
  "cooke-county": "https://www.e3es.com/projects-item/cooke-county-2/",
  "donna-isd": "https://www.e3es.com/projects-item/donna-isd-2/",
  "edcouch-elsa-isd": "https://www.e3es.com/projects-item/edcouch-elsa-isd/",
  "ferris-isd": "https://www.e3es.com/projects-item/ferris-isd/",
  "glen-rose-medical-center": "https://www.e3es.com/projects-item/glen-rose-medical-center/",
  "goodall-witcher-hospital": "https://www.e3es.com/projects-item/gwh/",
  "granbury-isd": "https://www.e3es.com/projects-item/granbury-isd/",
  "greenville-isd": "https://www.e3es.com/projects-item/greenville-isd/",
  "hondo-isd": "https://www.e3es.com/projects-item/hondo-isd/",
  "houston-community-college": "https://www.e3es.com/projects-item/houston-cc/",
  "kountze-isd": "https://www.e3es.com/projects-item/kountze-isd/",
  "lake-worth-isd": "https://www.e3es.com/projects-item/lake-worth-isd/",
  "manor-isd": "https://www.e3es.com/projects-item/manor-isd-2/",
  "mercedes-isd": "https://www.e3es.com/projects-item/mercedes-isd/",
  "needville-isd": "https://www.e3es.com/projects-item/needville-isd/",
  "port-neches-groves-isd": "https://www.e3es.com/projects-item/port-neches-groves-isd/",
  "prosper-isd": "https://www.e3es.com/projects-item/prosper-isd/",
  "raymondville-isd": "https://www.e3es.com/projects-item/raymondville-isd/",
  "ricardo-isd": "https://www.e3es.com/projects-item/ricardo-isd/",
  "rio-hondo-isd": "https://www.e3es.com/projects-item/rio-hondo-isd/",
  "royal-isd": "https://www.e3es.com/projects-item/royal-isd/"
};

async function run() {
  console.log('Launching headless Chrome...');
  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const results = {};

  try {
    for (const [slug, url] of Object.entries(liveUrls)) {
      console.log(`Loading page for ${slug}: ${url}...`);
      const page = await browser.newPage();
      
      // Set viewport
      await page.setViewport({ width: 1440, height: 900 });
      
      // Navigate to URL
      await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
      
      // Wait another 2.5 seconds to let WPBakery masonry grid finish AJAX loading
      await page.evaluate(() => new Promise(resolve => setTimeout(resolve, 2500)));

      // Extract all image URLs
      const images = await page.evaluate(() => {
        const list = [];
        // Extract from grid containers, galleries, and content images
        const imgs = document.querySelectorAll('img');
        imgs.forEach(img => {
          const src = img.currentSrc || img.src;
          if (src && src.includes('wp-content/uploads') && 
              !src.includes('E3_WebLogo') && 
              !src.includes('TIPS') && 
              !src.includes('BuyBoard')) {
            // Get original/large size instead of thumbnails if possible
            let origSrc = src.replace(/-\d+x\d+(\.[a-z]+)$/i, '$1');
            list.push(origSrc);
          }
        });
        return [...new Set(list)];
      });

      console.log(` -> Found ${images.length} images for ${slug}`);
      results[slug] = images;

      await page.close();
    }
  } catch (e) {
    console.error('Scraping error:', e.message);
  } finally {
    await browser.close();
  }

  fs.writeFileSync('scratch/live_gallery_images.json', JSON.stringify(results, null, 2));
  console.log('Saved scraped images to scratch/live_gallery_images.json');
}

run().catch(err => console.error(err));
