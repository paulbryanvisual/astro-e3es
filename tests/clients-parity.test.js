/**
 * E3 Case Study Parity and Migration E2E Test Suite
 * 
 * Runs against the local Astro dev server to verify:
 * 1. Client count and exclusions on /clients.
 * 2. Status code 200 and BEM layout on individual subpages.
 * 3. Featured image migration (no taj-mahal placeholders).
 * 4. Correct Vimeo video iframes for specific pages.
 * 5. Project details wrapped in wp-block-e3es-project project-section and positioned under relationship paragraph.
 * 
 * Usage: node tests/clients-parity.test.js
 */

import { execSync } from 'child_process';

const BASE_URL = process.env.ASTRO_URL || 'http://localhost:4008';
const CONCURRENCY_LIMIT = 5;

const EXPECTED_VIDEOS = {
  'granbury-isd': '227283498',
  'little-elm-isd': '946653874',
  'keene-isd': '1176712805',
  'plano-isd': '1007829512',
  'city-of-stockdale': '1171901749',
  'boyd-isd': '1179578579'
};

// ANSI Color helper
const colors = {
  reset: '\x1b[0m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  cyan: '\x1b[36m',
  bold: '\x1b[1m'
};

function logInfo(msg) {
  console.log(`${colors.cyan}[INFO]${colors.reset} ${msg}`);
}

function logSuccess(msg) {
  console.log(`${colors.green}[PASS]${colors.reset} ${msg}`);
}

function logWarn(msg) {
  console.log(`${colors.yellow}[WARN]${colors.reset} ${msg}`);
}

function logError(msg) {
  console.error(`${colors.red}[FAIL]${colors.reset} ${msg}`);
}

/**
 * Checks if a class is present in the HTML's class attributes
 */
function hasClass(html, className) {
  const regex = new RegExp(`class=["'][^"']*\\b${className}\\b[^"']*["']`, 'i');
  return regex.test(html);
}

/**
 * Finds all tags and content for a given tag/class combination
 */
function findProjectBlocks(html) {
  const tagRegex = /<([a-z0-9-]+)\s+([^>]*class=["'][^"']*wp-block-e3es-project[^"']*["'][^>]*)>/gi;
  let match;
  const blocks = [];
  while ((match = tagRegex.exec(html)) !== null) {
    const tagName = match[1];
    const attrs = match[2];
    
    // Check if it also contains project-section
    const classMatch = attrs.match(/class=["']([^"']+)["']/i);
    if (!classMatch) continue;
    const classes = classMatch[1].split(/\s+/);
    if (!classes.includes('project-section')) continue;
    
    const startIdx = match.index;
    // Find matching close tag
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

/**
 * Run tests
 */
async function main() {
  console.log(`${colors.bold}${colors.cyan}====================================================${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan} Starting E3 Clients Parity E2E Test Suite           ${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan} Target URL: ${BASE_URL}                            ${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan}====================================================${colors.reset}\n`);

  const failures = [];
  let testCount = 0;
  let passedCount = 0;

  // --- CHECK 1: CLIENTS LISTING PAGE ---
  testCount++;
  logInfo('Verifying /clients listing page...');
  const clientsListUrl = `${BASE_URL}/clients`;
  let clientsPageHtml = '';
  
  try {
    const res = await fetch(clientsListUrl);
    if (!res.ok) {
      throw new Error(`Failed to fetch /clients: HTTP ${res.status} ${res.statusText}`);
    }
    clientsPageHtml = await res.text();
  } catch (err) {
    logError(`Could not access /clients page: ${err.message}`);
    failures.push({ test: 'Clients Listing HTTP Status', error: err.message });
    process.exit(1);
  }

  // Parse client cards
  const cardRegex = /<a\s+[^>]*class=["'][^"']*client-card[^"']*["'][^>]*>/gi;
  let cardMatch;
  const clientCards = [];
  
  while ((cardMatch = cardRegex.exec(clientsPageHtml)) !== null) {
    const cardTag = cardMatch[0];
    const hrefMatch = cardTag.match(/href=["']([^"']+)["']/i);
    const href = hrefMatch ? hrefMatch[1] : '';
    
    const nameMatch = cardTag.match(/data-name=["']([^"']+)["']/i);
    const name = nameMatch ? nameMatch[1] : '';
    
    // Extract slug from href (e.g. /clients/granbury-isd -> granbury-isd)
    const slugMatch = href.match(/\/clients\/([^/?#]+)/);
    const slug = slugMatch ? slugMatch[1] : href.split('/').pop() || '';
    
    clientCards.push({ href, name, slug, tag: cardTag });
  }

  logInfo(`Found ${clientCards.length} client cards on listing page.`);

  // Assertions for Check 1
  let check1Passed = true;
  if (clientCards.length !== 25) {
    const errorMsg = `Expected exactly 25 clients, but found ${clientCards.length}.`;
    logError(errorMsg);
    failures.push({ test: 'Client Count (25)', error: errorMsg });
    check1Passed = false;
  } else {
    logSuccess('Client listing count is exactly 25.');
  }

  // Exclude south-texas
  const southTexasCard = clientCards.find(c => c.slug === 'south-texas' || c.href.includes('south-texas') || c.name.includes('south texas'));
  if (southTexasCard) {
    const errorMsg = `Exclusion failed: 'South Texas & Coast' (south-texas) is present in the list.`;
    logError(errorMsg);
    failures.push({ test: 'Exclude South Texas', error: errorMsg });
    check1Passed = false;
  } else {
    logSuccess('List correctly excludes South Texas & Coast.');
  }

  // Exclude duplicate gwh
  const gwhCard = clientCards.find(c => c.slug === 'gwh' || c.href.endsWith('/gwh') || c.name === 'gwh');
  if (gwhCard) {
    const errorMsg = `Exclusion failed: Duplicate Goodall-Witcher Healthcare (gwh) is present in the list.`;
    logError(errorMsg);
    failures.push({ test: 'Exclude Duplicate GWH', error: errorMsg });
    check1Passed = false;
  } else {
    logSuccess('List correctly excludes duplicate GWH card.');
  }

  if (check1Passed) {
    passedCount++;
  }

  // Collect individual client detail pages under /clients/[slug]
  const clientDetailPages = clientCards.filter(c => {
    // Only test clean /clients/[slug] routes, ignore external links or home industry redirects
    return c.href.startsWith('/clients/') && c.slug !== 'gwh' && c.slug !== 'south-texas';
  });

  logInfo(`Queueing ${clientDetailPages.length} client subpages for E2E audits...`);

  // Concurrency helper
  const queue = [...clientDetailPages];
  const activeWorkers = [];

  const runWorker = async () => {
    while (queue.length > 0) {
      const client = queue.shift();
      await auditClientPage(client, failures);
    }
  };

  for (let i = 0; i < Math.min(CONCURRENCY_LIMIT, queue.length); i++) {
    activeWorkers.push(runWorker());
  }
  await Promise.all(activeWorkers);

  // --- REPORT SUMMARY ---
  console.log(`\n${colors.bold}${colors.cyan}====================================================${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan} E2E Test Suite Execution Complete                   ${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan}====================================================${colors.reset}`);
  console.log(`Passed Suites: ${passedCount}/${testCount}`);
  console.log(`Total Failures Encountered: ${failures.length}\n`);

  if (failures.length > 0) {
    console.log(`${colors.bold}${colors.red}Assertion Failures List:${colors.reset}`);
    failures.forEach((f, idx) => {
      console.log(`\n${idx + 1}. [${f.test}] -> Page: ${f.url || 'Listing'}`);
      console.log(`   ${colors.red}Error:${colors.reset} ${f.error}`);
    });
    console.log(`\n${colors.bold}${colors.red}Test run status: FAIL (Exiting with code 1)${colors.reset}\n`);
    process.exit(1);
  } else {
    console.log(`${colors.bold}${colors.green}Test run status: PASS (Exiting with code 0)${colors.reset}\n`);
    process.exit(0);
  }
}

/**
 * Audits a single client page
 */
async function auditClientPage(client, failures) {
  const url = `${BASE_URL}${client.href}`;
  const slug = client.slug;
  
  try {
    const res = await fetch(url);
    
    // Check 2: Status code 200
    if (res.status !== 200) {
      const errorMsg = `Subpage returned status code ${res.status}`;
      logError(`${slug} -> ${errorMsg}`);
      failures.push({ test: 'Subpage HTTP Status', url, error: errorMsg });
      return; // Stop auditing if page isn't loading
    }

    const html = await res.text();
    const errors = [];

    // Check 2: BEM HTML Layout check
    if (!html.includes('<main')) {
      errors.push('Missing <main> tag');
    }
    if (!hasClass(html, 'breadcrumb-bar')) {
      errors.push('Missing breadcrumb bar class (breadcrumb-bar)');
    }
    if (!hasClass(html, 'db-page-hero') && !hasClass(html, 'wp-block-e3es-intro-banner')) {
      errors.push('Missing hero banner class (db-page-hero or wp-block-e3es-intro-banner)');
    }

    // Check 3: Featured image placeholder check
    if (html.includes('taj-mahal-placeholder') || html.includes('taj-mahal-placeholder.png')) {
      errors.push('Uses unmigrated "taj-mahal-placeholder" featured image in tags or CSS styles');
    }

    // Check 4: Vimeo video iframe check
    if (EXPECTED_VIDEOS[slug]) {
      const expectedVideoId = EXPECTED_VIDEOS[slug];
      const hasVimeoIframe = html.includes('player.vimeo.com/video/');
      const hasCorrectVideoId = html.includes(`player.vimeo.com/video/${expectedVideoId}`);
      
      if (!hasVimeoIframe) {
        errors.push(`Missing Vimeo video iframe`);
      } else if (!hasCorrectVideoId) {
        // Extract whatever vimeo link is there to display in the error
        const vimeoMatch = html.match(/player\.vimeo\.com\/video\/[0-9]+/i);
        const actualLink = vimeoMatch ? vimeoMatch[0] : 'unknown';
        errors.push(`Vimeo iframe is present but renders incorrect URL/ID (Expected: ${expectedVideoId}, Found: ${actualLink})`);
      }
    }

    // Extract main content for relative indices validation
    const mainMatch = html.match(/<main[^>]*>([\s\S]*?)<\/main>/i);
    const mainContent = mainMatch ? mainMatch[1] : html;

    // Check 5: Project details wrapper check
    const hasDetails = hasClass(mainContent, 'project-details');
    const projectIdx = mainContent.search(/wp-block-e3es-project(?!-toc)/) !== -1 ? mainContent.search(/wp-block-e3es-project(?!-toc)/) : mainContent.indexOf('project-section');

    if (hasDetails) {
      // Find all custom e3 project blocks
      const blocks = findProjectBlocks(mainContent);
      
      // Find index of first project details occurrence
      const detailsRegex = /class=["'][^"']*\bproject-details\b[^"']*["']/gi;
      const detailsMatch = detailsRegex.exec(mainContent);
      const detailsIdx = detailsMatch ? detailsMatch.index : -1;
      
      // Verify project details are wrapped
      if (detailsIdx !== -1) {
        const isWrapped = blocks.some(b => detailsIdx >= b.start && detailsIdx <= b.end);
        if (!isWrapped) {
          errors.push('Project details (class="project-details") are not wrapped inside the custom project block structure (wp-block-e3es-project project-section)');
        }
      }
      
      // Find relationship description paragraph
      const paragraphRegex = /<p[^>]*>((?:(?!<\/p>|<p).)*?(?:partnered|partnership|collaborated|cooperated)(?:(?!<\/p>|<p).)*?)<\/p>/gi;
      let paragraphMatch;
      let firstParagraphIdx = -1;
      while ((paragraphMatch = paragraphRegex.exec(mainContent)) !== null) {
        // Skip video intro text blocks
        if (paragraphMatch[0].includes('db-video-section') || paragraphMatch[0].includes('video-embed')) {
          continue;
        }
        firstParagraphIdx = paragraphMatch.index;
        break;
      }
      
      if (firstParagraphIdx === -1) {
        // Fallback: search for first <p> containing client name words
        const nameWords = slug.split('-');
        const clientKeyword = nameWords[0];
        if (clientKeyword && clientKeyword.length > 2) {
          const clientNameRegex = new RegExp(`<p[^>]*>((?:(?!</p>|<p).)*?${clientKeyword}(?:(?!</p>|<p).)*?)</p>`, 'i');
          const clientNameMatch = mainContent.match(clientNameRegex);
          if (clientNameMatch) {
            firstParagraphIdx = clientNameMatch.index;
          }
        }
      }
      
      if (firstParagraphIdx !== -1) {
        // Assert paragraph is BEFORE project section
        if (projectIdx !== -1 && firstParagraphIdx > projectIdx) {
          errors.push(`Project section block is not positioned under the short relationship description paragraph (paragraph found below project block)`);
        }
      } else {
        errors.push('Could not find relationship description paragraph on case study page');
      }
    }

    if (errors.length > 0) {
      logError(`${slug} -> failed ${errors.length} audit checks.`);
      errors.forEach(err => {
        failures.push({ test: 'Subpage Audit', url, error: err });
      });
    } else {
      logSuccess(`${slug} -> passed all audit checks.`);
    }

  } catch (err) {
    const errorMsg = `Audit error: ${err.message}`;
    logError(`${slug} -> ${errorMsg}`);
    failures.push({ test: 'Subpage Audit Error', url, error: errorMsg });
  }
}

main();
