const fs = require('fs');
const { execSync } = require('child_process');

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

// Helper function to extract content blocks from live site HTML
function extractContentFromHtml(html) {
  let body = html.replace(/<head>[\s\S]*?<\/head>/gi, '')
                 .replace(/<script[\s\S]*?<\/script>/gi, '')
                 .replace(/<style[\s\S]*?<\/style>/gi, '');
  
  const textBlocks = [];
  const textRegex = /<(p|li|h3|h4)[^>]*>([\s\S]*?)<\/\1>/gi;
  let match;
  while ((match = textRegex.exec(body)) !== null) {
    const rawText = match[2].replace(/<[^>]+>/g, '').trim();
    if (rawText.length > 15 && 
        !rawText.includes('Entegral Solutions') && 
        !rawText.includes('All Rights Reserved') &&
        !rawText.includes('Schedule a Consultation') &&
        !rawText.includes('Direct Contact') &&
        !rawText.includes('Texas Office Locations')) {
      textBlocks.push(rawText);
    }
  }

  const images = [];
  const imgRegex = /<img[^>]+src="([^"]*wp-content\/uploads\/[^"]+)"/gi;
  while ((match = imgRegex.exec(body)) !== null) {
    const src = match[1];
    if (!src.includes('E3_WebLogo') && !src.includes('TIPS') && !src.includes('BuyBoard')) {
      images.push(src);
    }
  }

  return {
    textLength: textBlocks.join(' ').length,
    textCount: textBlocks.length,
    texts: textBlocks,
    images: [...new Set(images)]
  };
}

const sleep = ms => new Promise(res => setTimeout(res, ms));

async function run() {
  console.log('Fetching local WordPress clients...');
  const res1 = await fetch(`http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100&page=1&t=${Date.now()}`);
  const clients1 = await res1.json();
  const res2 = await fetch(`http://e3es2026.local/wp-json/wp/v2/clients?_embed=wp:featuredmedia&per_page=100&page=2&t=${Date.now()}`);
  const clients2 = res2.ok ? await res2.json() : [];
  const localClients = [...clients1, ...clients2];

  const auditReport = [];

  for (const [slug, url] of Object.entries(liveUrls)) {
    console.log(`Auditing ${slug}...`);
    try {
      // Use curl command with retries and timeout options for reliability
      const curlCmd = `curl -s -L --connect-timeout 20 --max-time 30 --retry 3 -A "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36" "${url}"`;
      const html = execSync(curlCmd, { encoding: 'utf8', maxBuffer: 10 * 1024 * 1024 });
      if (!html || html.length < 500) {
        throw new Error(`Empty or too short HTML returned by curl`);
      }
      const liveData = extractContentFromHtml(html);

      // Find local client post
      const local = localClients.find(lc => lc.slug === slug);
      if (!local) {
        throw new Error(`Local client not found for slug ${slug}`);
      }

      // Extract local text
      const localContentRaw = local.content.rendered;
      const localTexts = [...localContentRaw.matchAll(/<(p|li|h3|h4)[^>]*>([\s\S]*?)<\/\1>/gi)]
        .map(m => m[2].replace(/<[^>]+>/g, '').trim())
        .filter(t => t.length > 15);
      
      const localImages = [...localContentRaw.matchAll(/<img[^>]+src="([^"]+)"/gi)].map(m => m[1]);

      // Check differences
      const textDiff = liveData.texts.filter(lt => {
        const ltClean = lt.toLowerCase().replace(/[^a-z0-9]/g, '');
        return !localTexts.some(ltx => ltx.toLowerCase().replace(/[^a-z0-9]/g, '').includes(ltClean) || ltClean.includes(ltx.toLowerCase().replace(/[^a-z0-9]/g, '')));
      });

      const imgDiff = liveData.images.filter(limg => {
        const limgName = limg.substring(limg.lastIndexOf('/') + 1).toLowerCase().replace(/-\d+x\d+/g, '').replace(/\.[a-z]+$/i, '');
        return !localImages.some(localImg => {
          const localImgName = localImg.substring(localImg.lastIndexOf('/') + 1).toLowerCase().replace(/-\d+x\d+/g, '').replace(/\.[a-z]+$/i, '');
          return localImgName.includes(limgName) || limgName.includes(localImgName);
        });
      });

      auditReport.push({
        slug,
        title: local.title.rendered,
        liveUrl: url,
        liveTextLength: liveData.textLength,
        localTextLength: localTexts.join(' ').length,
        liveImagesCount: liveData.images.length,
        localImagesCount: localImages.length,
        missingTexts: textDiff,
        missingImages: imgDiff,
        status: (textDiff.length > 0 || imgDiff.length > 0) ? 'INCOMPLETE' : 'PARITY'
      });
    } catch (e) {
      console.error(`Error auditing ${slug}:`, e.message);
      auditReport.push({
        slug,
        title: slug,
        liveUrl: url,
        status: 'ERROR',
        error: e.message
      });
    }
    await sleep(200);
  }

  console.log('\n--- AUDIT SUMMARY ---');
  auditReport.forEach(r => {
    console.log(` - ${r.title} (${r.slug}): ${r.status} (Missing ${r.missingTexts?.length || 0} texts, ${r.missingImages?.length || 0} images)`);
  });

  fs.writeFileSync('scratch/content_audit_report.json', JSON.stringify(auditReport, null, 2));
  console.log('\nSaved complete audit report to scratch/content_audit_report.json');
}

run().catch(err => console.error(err));
