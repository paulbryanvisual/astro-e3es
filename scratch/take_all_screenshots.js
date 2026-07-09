import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const slugs = [
  'boyd-isd', 'bryan-isd', 'caldwell-isd', 'carrizo-springs-cisd', 'cooke-county',
  'donna-isd', 'edcouch-elsa-isd', 'ferris-isd', 'glen-rose-medical-center', 'granbury-isd',
  'greenville-isd', 'goodall-witcher-hospital', 'hondo-isd', 'houston-community-college', 'kountze-isd',
  'lake-worth-isd', 'manor-isd', 'mercedes-isd', 'needville-isd', 'port-neches-groves-isd',
  'prosper-isd', 'raymondville-isd', 'ricardo-isd', 'rio-hondo-isd', 'royal-isd', 'sanger-isd'
];

const outputDir = '/Users/bryanpaul/.gemini/antigravity/brain/fd3a018d-6d66-4014-a832-26235d4188b8/visual_verification';
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

async function run() {
  console.log('Launching headless Chrome...');
  const browser = await puppeteer.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 1600 });

  for (const slug of slugs) {
    const url = `http://localhost:4008/clients/${slug}`;
    console.log(`Navigating to ${url}...`);
    try {
      await page.goto(url, { waitUntil: 'networkidle2', timeout: 15000 });
      // Wait extra 500ms to ensure animations complete
      await new Promise(resolve => setTimeout(resolve, 500));
      
      const screenshotPath = path.join(outputDir, `${slug}.png`);
      console.log(`Saving screenshot to ${screenshotPath}...`);
      await page.screenshot({ path: screenshotPath });
    } catch (err) {
      console.error(`Failed to capture ${slug}:`, err.message);
    }
  }

  await browser.close();
  console.log('Visual verification capture complete!');
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
