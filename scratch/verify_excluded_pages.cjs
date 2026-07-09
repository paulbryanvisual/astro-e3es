// Using native fetch in Node.

const BASE_URL = 'http://localhost:4008';
const EXCLUDED_SLUGS = ['plano-isd', 'keene-isd', 'little-elm-isd', 'city-of-stockdale', 'boyd-isd'];

const EXPECTED_VIDEOS = {
  'granbury-isd': '227283498',
  'little-elm-isd': '946653874',
  'keene-isd': '1176712805',
  'plano-isd': '1007829512',
  'city-of-stockdale': '1171901749',
  'boyd-isd': '1179578579'
};

function hasClass(html, className) {
  const regex = new RegExp(`class=["'][^"']*\\b${className}\\b[^"']*["']`, 'i');
  return regex.test(html);
}

function findProjectBlocks(html) {
  const tagRegex = /<([a-z0-9-]+)\s+([^>]*class=["'][^"']*wp-block-e3es-project[^"']*["'][^>]*)>/gi;
  let match;
  const blocks = [];
  while ((match = tagRegex.exec(html)) !== null) {
    const tagName = match[1];
    const attrs = match[2];
    
    const classMatch = attrs.match(/class=["']([^"']+)["']/i);
    if (!classMatch) continue;
    const classes = classMatch[1].split(/\s+/);
    if (!classes.includes('project-section')) continue;
    
    const startIdx = match.index;
    let depth = 1;
    let currentIdx = startIdx + match[0].length;
    const openTagStr = `<${tagName}`;
    const closeTagStr = `</${tagName}`;
    
    while (depth > 0 && currentIdx < html.length) {
      const nextOpen = html.toLowerCase().indexOf(openTagStr, currentIdx);
      const nextClose = html.toLowerCase().indexOf(closeTagStr, currentIdx);
      if (nextClose === -1) break;
      if (nextOpen !== -1 && nextOpen < nextClose) {
        depth++;
        currentIdx = nextOpen + openTagStr.length;
      } else {
        depth--;
        currentIdx = nextClose + closeTagStr.length;
      }
    }
    blocks.push({ start: startIdx, end: currentIdx });
  }
  return blocks;
}

async function auditPage(slug) {
  const url = `${BASE_URL}/clients/${slug}`;
  console.log(`Auditing: ${url}`);
  
  try {
    const res = await fetch(url);
    if (res.status !== 200) {
      console.error(`FAIL: ${slug} returned HTTP ${res.status}`);
      return;
    }
    
    const html = await res.text();
    const errors = [];
    
    if (!html.includes('<main')) {
      errors.push('Missing <main> tag');
    }
    if (!hasClass(html, 'breadcrumb-bar')) {
      errors.push('Missing breadcrumb bar class (breadcrumb-bar)');
    }
    if (!hasClass(html, 'db-page-hero') && !hasClass(html, 'wp-block-e3es-intro-banner')) {
      errors.push('Missing hero banner class (db-page-hero or wp-block-e3es-intro-banner)');
    }
    if (html.includes('taj-mahal-placeholder') || html.includes('taj-mahal-placeholder.png')) {
      errors.push('Uses unmigrated "taj-mahal-placeholder" featured image');
    }
    
    if (EXPECTED_VIDEOS[slug]) {
      const expectedVideoId = EXPECTED_VIDEOS[slug];
      const hasVimeoIframe = html.includes('player.vimeo.com/video/');
      const hasCorrectVideoId = html.includes(`player.vimeo.com/video/${expectedVideoId}`);
      if (!hasVimeoIframe) {
        errors.push(`Missing Vimeo video iframe`);
      } else if (!hasCorrectVideoId) {
        errors.push(`Vimeo iframe renders incorrect URL/ID (Expected: ${expectedVideoId})`);
      }
    }
    
    const mainMatch = html.match(/<main[^>]*>([\s\S]*?)<\/main>/i);
    const mainContent = mainMatch ? mainMatch[1] : html;
    
    const hasDetails = hasClass(mainContent, 'project-details');
    const projectIdx = mainContent.indexOf('wp-block-e3es-project') !== -1 ? mainContent.indexOf('wp-block-e3es-project') : mainContent.indexOf('project-section');
    
    if (hasDetails) {
      const blocks = findProjectBlocks(mainContent);
      const detailsRegex = /class=["'][^"']*\bproject-details\b[^"']*["']/gi;
      const detailsMatch = detailsRegex.exec(mainContent);
      const detailsIdx = detailsMatch ? detailsMatch.index : -1;
      
      if (detailsIdx !== -1) {
        const isWrapped = blocks.some(b => detailsIdx >= b.start && detailsIdx <= b.end);
        if (!isWrapped) {
          errors.push('Project details not wrapped inside wp-block-e3es-project project-section');
        }
      }
      
      const paragraphRegex = /<p[^>]*>([\s\S]*?(?:partnered|partnership|collaborated|cooperated)[\s\S]*?)<\/p>/gi;
      let paragraphMatch;
      let firstParagraphIdx = -1;
      while ((paragraphMatch = paragraphRegex.exec(mainContent)) !== null) {
        if (paragraphMatch[0].includes('db-video-section') || paragraphMatch[0].includes('video-embed')) {
          continue;
        }
        firstParagraphIdx = paragraphMatch.index;
        break;
      }
      
      if (firstParagraphIdx === -1) {
        const nameWords = slug.split('-');
        const clientKeyword = nameWords[0];
        if (clientKeyword && clientKeyword.length > 2) {
          const clientNameRegex = new RegExp(`<p[^>]*>([\\s\\S]*?${clientKeyword}[\\s\\S]*?)<\/p>`, 'i');
          const clientNameMatch = mainContent.match(clientNameRegex);
          if (clientNameMatch) {
            firstParagraphIdx = clientNameMatch.index;
          }
        }
      }
      
      if (firstParagraphIdx !== -1) {
        if (projectIdx !== -1 && firstParagraphIdx > projectIdx) {
          errors.push(`Project section block is not positioned under relationship description paragraph`);
        }
      } else {
        errors.push('Could not find relationship description paragraph');
      }
    }
    
    if (errors.length > 0) {
      console.error(`FAIL: ${slug} had errors:\n - ${errors.join('\n - ')}`);
    } else {
      console.log(`PASS: ${slug} passed all E2E assertions.`);
    }
    
  } catch (err) {
    console.error(`FAIL: Exception auditing ${slug}: ${err.message}`);
  }
}

async function run() {
  for (const slug of EXCLUDED_SLUGS) {
    await auditPage(slug);
  }
}

run();
