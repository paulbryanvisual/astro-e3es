const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const phpBinary = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';
const outputDir = path.join(__dirname, 'logos');
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

async function main() {
  console.log("🔍 Fetching list of all client posts from WordPress...");
  
  let clients = [];
  try {
    const rawResult = execSync(`"${phpBinary}" "${path.join(__dirname, 'query_external_logos.php')}"`);
    clients = JSON.parse(rawResult.toString());
  } catch (err) {
    console.error("Failed to query clients:", err.message);
    process.exit(1);
  }
  
  console.log(`Found ${clients.length} clients in database. Starting crawler...`);
  
  const results = [];
  
  for (let i = 0; i < clients.length; i++) {
    const client = clients[i];
    console.log(`\n[${i+1}/${clients.length}] Crawling live logo for: ${client.slug}...`);
    
    // Construct possible live URLs to fetch
    const urls = [
      `https://www.e3es.com/projects-item/${client.slug}/`,
      `https://www.e3es.com/projects-item/${client.slug}-2/`, // alternative duplicate suffix
    ];
    
    let logoUrl = null;
    let html = '';
    
    for (const url of urls) {
      try {
        console.log(`  Fetching: ${url}`);
        const response = await fetch(url);
        if (!response.ok) {
          console.log(`    Status: ${response.status}`);
          continue;
        }
        html = await response.text();
        
        // Try to match logo inside the vc_col-sm-2 column
        const match = html.match(/vc_col-sm-2[\s\S]*?<img[^>]*src="([^"]+)"/i);
        if (match && match[1]) {
          logoUrl = match[1];
          // Strip any width/height thumbnail suffix like -150x150 to get the original full-size logo!
          logoUrl = logoUrl.replace(/-\d+x\d+(\.[a-z0-9]+)$/i, '$1');
          console.log(`    Matched Logo URL: ${logoUrl}`);
          break;
        }
      } catch (err) {
        console.log(`    Error fetching: ${err.message}`);
      }
    }
    
    if (logoUrl) {
      try {
        console.log(`  Downloading logo...`);
        const imgResponse = await fetch(logoUrl);
        if (imgResponse.ok) {
          const buffer = Buffer.from(await imgResponse.arrayBuffer());
          const ext = path.extname(logoUrl.split('?')[0]) || '.png';
          const destFile = path.join(outputDir, `${client.slug}-logo${ext}`);
          fs.writeFileSync(destFile, buffer);
          console.log(`  [SUCCESS] Saved logo to: ${destFile} (${buffer.length} bytes)`);
          results.push({ id: client.id, slug: client.slug, localFile: destFile, originalUrl: logoUrl });
        } else {
          console.log(`  [ERROR] Failed to download image: ${imgResponse.status}`);
        }
      } catch (err) {
        console.log(`  [ERROR] Failed to download: ${err.message}`);
      }
    } else {
      console.log(`  [WARNING] No logo found for ${client.slug}`);
    }
  }
  
  // Write download manifest
  fs.writeFileSync(path.join(__dirname, 'logo_manifest.json'), JSON.stringify(results, null, 2));
  console.log(`\n🏁 Crawling complete! Saved manifest to scratch/logo_manifest.json.`);
}

main().catch(err => {
  console.error("Fatal error:", err);
});
