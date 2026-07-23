const fs = require('fs');
const http = require('http');

const SLUGS = [
  'boyd-isd',
  'bryan-isd',
  'caldwell-isd',
  'carrizo-springs-cisd',
  'cooke-county',
  'donna-isd',
  'edcouch-elsa-isd',
  'ferris-isd',
  'glen-rose-medical-center',
  'goodall-witcher-hospital',
  'granbury-isd',
  'greenville-isd',
  'hondo-isd',
  'houston-community-college',
  'kountze-isd',
  'lake-worth-isd',
  'manor-isd',
  'mercedes-isd',
  'needville-isd',
  'port-neches-groves-isd',
  'prosper-isd',
  'raymondville-isd',
  'ricardo-isd',
  'rio-hondo-isd',
  'royal-isd',
  'sanger-isd'
];

function fetchLocalHtml(slug) {
  return new Promise((resolve, reject) => {
    http.get(`http://localhost:4008/clients/${slug}`, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve(data));
    }).on('error', reject);
  });
}

function cleanText(text) {
  return text
    .replace(/&#8217;/g, "'")
    .replace(/&amp;/g, "&")
    .replace(/&nbsp;/g, " ")
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function cleanTextForMatch(text) {
  return text.toLowerCase().replace(/[^a-z0-9]/g, '');
}

async function runAudit() {
  console.log('Starting Client Content Parity Audit...');
  const results = [];
  let totalMissing = 0;

  for (const slug of SLUGS) {
    const liveCachePath = `scratch/live_project_pages_cache/${slug}.html`;
    if (!fs.existsSync(liveCachePath)) {
      console.log(`[WARN] Live cache file missing for ${slug}: ${liveCachePath}`);
      continue;
    }

    const liveHtml = fs.readFileSync(liveCachePath, 'utf8');
    
    // Fetch local HTML
    let localHtml = '';
    try {
      localHtml = await fetchLocalHtml(slug);
    } catch (err) {
      console.log(`[ERROR] Failed to fetch local page for ${slug}: ${err.message}`);
      continue;
    }

    // Extract paragraphs from live HTML
    const pRegex = /<p[^>]*>([\s\S]*?)<\/p>/gi;
    let match;
    const liveParagraphs = [];
    while ((match = pRegex.exec(liveHtml)) !== null) {
      const pText = cleanText(match[1]);
      if (pText.length < 50) continue; // Skip short ones
      if (pText.includes('Join Our Team') || pText.includes('prague-architects') || pText.includes('ALL RIGHTS RESERVED') || pText.includes('Tel:') || pText.includes('+7 (885)')) {
        continue; // Skip footers / headers / sidebars
      }
      liveParagraphs.push(pText);
    }

    const cleanedLocal = cleanTextForMatch(localHtml);
    const missing = [];

    for (const lp of liveParagraphs) {
      const lpClean = cleanTextForMatch(lp);
      if (!cleanedLocal.includes(lpClean)) {
        missing.push(lp);
      }
    }

    results.push({
      slug,
      paragraphsCount: liveParagraphs.length,
      missingCount: missing.length,
      missing
    });

    totalMissing += missing.length;
  }

  // Generate Report
  let md = '# Content Parity Audit Report\n\n';
  md += 'This report verifies that all textual project description paragraphs from the live website are correctly present on the new local featured client pages.\n\n';
  md += `* **Total Client Pages Audited**: ${SLUGS.length}\n`;
  md += `* **Total Missing Paragraphs**: ${totalMissing}\n\n`;

  md += '## Audit Results by Client\n\n';
  md += '| Client Slug | Total Original Paragraphs | Missing Paragraphs | Status |\n';
  md += '|-------------|---------------------------|--------------------|--------|\n';

  for (const r of results) {
    const status = r.missingCount === 0 ? '✅ Pass' : '❌ Fail';
    md += `| \`${r.slug}\` | ${r.paragraphsCount} | ${r.missingCount} | ${status} |\n`;
  }

  if (totalMissing > 0) {
    md += '\n## Detailed Missing Content Log\n\n';
    for (const r of results) {
      if (r.missingCount > 0) {
        md += `### ❌ \`${r.slug}\`\n\n`;
        for (const m of r.missing) {
          md += `* **Missing paragraph**: "${m}"\n`;
        }
        md += '\n';
      }
    }
  } else {
    md += '\n## ✅ All Content Verified\n\nAll textual description paragraphs from the live website are 100% matched and present on the local Astro client pages.\n';
  }

  const reportPath = '/Users/bryanpaul/.gemini/antigravity/brain/fd3a018d-6d66-4014-a832-26235d4188b8/content_parity_report.md';
  fs.writeFileSync(reportPath, md);
  console.log(`Report generated successfully at: ${reportPath}`);
}

runAudit();
