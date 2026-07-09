const fs = require('fs');
const path = require('path');

const targets = [
  {
    slug: 'cooke-county',
    url: 'https://www.e3es.com/wp-content/uploads/2021/12/Cooke-edit.jpg',
    fallback: 'https://www.e3es.com/wp-content/uploads/2021/12/Cooke-edit-768x545.jpg'
  },
  {
    slug: 'goodall-witcher-hospital',
    url: 'https://www.e3es.com/wp-content/uploads/2022/06/ghw-crane.jpg',
    fallback: 'https://www.e3es.com/wp-content/uploads/2022/06/ghw-crane-768x432.jpg'
  },
  {
    slug: 'houston-community-college',
    url: 'https://www.e3es.com/wp-content/uploads/2021/12/51586541380_b5836397d2_k.jpg',
    fallback: 'https://www.e3es.com/wp-content/uploads/2021/12/51586541380_b5836397d2_k-768x512.jpg'
  }
];

const destDir = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/uploads/2026/06';

async function download(url, destPath) {
  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`Status ${response.status}`);
  }
  const arrayBuffer = await response.arrayBuffer();
  const buffer = Buffer.from(arrayBuffer);
  fs.writeFileSync(destPath, buffer);
  console.log(`Successfully downloaded ${url} to ${destPath}`);
}

async function run() {
  for (const t of targets) {
    const destPath = path.join(destDir, path.basename(t.url));
    console.log(`Downloading for ${t.slug}...`);
    try {
      await download(t.url, destPath);
    } catch (e) {
      console.log(`Failed to download original: ${e.message}. Trying fallback...`);
      try {
        const fallbackPath = path.join(destDir, path.basename(t.fallback));
        await download(t.fallback, fallbackPath);
      } catch (err) {
        console.error(`Failed fallback: ${err.message}`);
      }
    }
  }
}

run().catch(err => console.error(err));
