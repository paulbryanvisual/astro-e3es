import rawTexasMapSvg from '../../public/Texas-Map-Editable.svg?raw';
import { cacheBuster } from './cache.ts';

const texasMapSvg = rawTexasMapSvg
  .replace(/<\?xml[^>]*\?>/i, '')
  .replace(/<svg([^>]*)>/i, (match, attrs) => {
    const viewBoxMatch = attrs.match(/viewBox=["']([^"']+)["']/i);
    const viewBox = viewBoxMatch ? viewBoxMatch[1] : '0 0 941.76 907.17';
    return `<svg id="texas-map-svg" viewBox="${viewBox}" class="db-feature__image texas-svg-map" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">`;
  });

const WP_URL = import.meta.env.PUBLIC_WP_URL || (import.meta.env.PROD 
  ? 'https://descriptive-goldfish.flywheelstaging.com/wp-json/wp/v2'
  : 'http://e3es2026.local/wp-json/wp/v2');

const WP_BASE_URL = WP_URL.replace('/wp-json/wp/v2', '');

const fetchCache = new Map<string, any>();

async function wpFetch(urlPath: string) {
  if (fetchCache.has(urlPath)) {
    return fetchCache.get(urlPath).clone();
  }
  const separator = urlPath.includes('?') ? '&' : '?';
  const url = `${WP_URL}${urlPath}${separator}t=${Date.now()}&cb=${cacheBuster}`;
  const response = await fetch(url, { cache: 'no-store' });
  if (response.ok) {
    fetchCache.set(urlPath, response.clone());
  }
  return response;
}

export async function getPosts() {
  const response = await wpFetch('/posts?_embed');
  if (!response.ok) {
    throw new Error('Failed to fetch posts');
  }
  return response.json();
}

export async function getPostBySlug(slug: string) {
  const response = await wpFetch(`/posts?slug=${slug}&_embed`);
  if (!response.ok) {
    throw new Error(`Failed to fetch post: ${slug}`);
  }
  const posts = await response.json();
  return posts.length > 0 ? posts[0] : null;
}

export async function getPages() {
  let allPages: any[] = [];
  let page = 1;
  let hasMore = true;
  
  while (hasMore) {
    const response = await wpFetch(`/pages?_embed&per_page=100&page=${page}`);
    if (!response.ok) {
      break;
    }
    const pages = await response.json();
    if (pages.length === 0) {
      break;
    }
    allPages = allPages.concat(pages);
    if (pages.length < 100) {
      hasMore = false;
    } else {
      page++;
    }
  }
  return allPages;
}

export async function getServices() {
  let allServices: any[] = [];
  let page = 1;
  let hasMore = true;
  
  while (hasMore) {
    const response = await wpFetch(`/services?_embed&per_page=100&page=${page}`);
    if (!response.ok) {
      break;
    }
    const services = await response.json();
    if (services.length === 0) {
      break;
    }
    allServices = allServices.concat(services);
    if (services.length < 100) {
      hasMore = false;
    } else {
      page++;
    }
  }
  return allServices;
}

export async function getServiceBySlug(slug: string) {
  const response = await wpFetch(`/services?slug=${slug}&_embed`);
  if (!response.ok) {
    throw new Error(`Failed to fetch service: ${slug}`);
  }
  const services = await response.json();
  return services.length > 0 ? services[0] : null;
}

export async function getPageBySlug(slug: string) {
  const response = await wpFetch(`/pages?slug=${slug}&_embed`);
  if (!response.ok) {
    throw new Error(`Failed to fetch page: ${slug}`);
  }
  const pages = await response.json();
  return pages.length > 0 ? pages[0] : null;
}

export async function getPageById(id: number) {
  const response = await wpFetch(`/pages/${id}?_embed`);
  if (!response.ok) {
    throw new Error(`Failed to fetch page: ${id}`);
  }
  return response.json();
}

export async function getClients() {
  const response1 = await wpFetch('/clients?_embed&per_page=100&page=1');
  if (!response1.ok) {
    throw new Error('Failed to fetch clients page 1');
  }
  const page1 = await response1.json();

  let page2: any[] = [];
  try {
    const response2 = await wpFetch('/clients?_embed&per_page=100&page=2');
    if (response2.ok) {
      page2 = await response2.json();
    }
  } catch (e) {
    // Ignore if page 2 fails or is empty
  }

  return [...page1, ...page2];
}

export function getTexasMapSvg() {
  return texasMapSvg;
}

/**
 * Server-side HTML utility to optimize images in Gutenberg block content.
 */



export function processWordPressHtml(html: string, slug?: string): string {
  if (!html) return '';

  // Fix WordPress's wptexturize breaking the HTML comment closing tag
  html = html.replace(/<!-- Interactive Texas Region Map \&\#8211;>/g, '');

  html = html.replace(/<e3-texas-region-selector([^>]*)>/g, (match, p1) => {
    let newAttrs = p1.replace(/data-employees="([^"]*)"/g, (m, val) => {
        const rawJson = decodeHtmlEntities(val);
        const b64 = Buffer.from(rawJson).toString('base64');
        return `data-employees-b64="${b64}"`;
    });
    newAttrs = newAttrs.replace(/data-region-map="([^"]*)"/g, (m, val) => {
        const rawJson = decodeHtmlEntities(val);
        const b64 = Buffer.from(rawJson).toString('base64');
        return `data-region-map-b64="${b64}"`;
    });
    return `<e3-texas-region-selector${newAttrs}>`;
  });

  html = html.replace(/<e3-sales-rep-selector([^>]*)>/g, (match, p1) => {
    let newAttrs = p1.replace(/data-employees="([^"]*)"/g, (m, val) => {
        const rawJson = decodeHtmlEntities(val);
        const b64 = Buffer.from(rawJson).toString('base64');
        return `data-employees-b64="${b64}"`;
    });
    newAttrs = newAttrs.replace(/data-region-map="([^"]*)"/g, (m, val) => {
        const rawJson = decodeHtmlEntities(val);
        const b64 = Buffer.from(rawJson).toString('base64');
        return `data-region-map-b64="${b64}"`;
    });
    return `<e3-sales-rep-selector${newAttrs}>`;
  });


  // Strip encoded HTML comment left behind by wptexturize
  html = html.replace(/&lt;!&#8211; Interactive Texas Region Map &#8211;&gt;/g, '');
  const mapRegex = /<img[^>]*static-map-600x400\.png[^>]*>/gi;
  let processedHtml = html;
  if (mapRegex.test(processedHtml)) {
    processedHtml = processedHtml.replace(mapRegex, getTexasMapSvg());
  }

  // 1. Rewrite relative paths to absolute WordPress server paths
  processedHtml = processedHtml
    .replace(/(url\(["']?)\/images\//gi, `$1${WP_BASE_URL}/images/`)
    .replace(/(url\(["']?)\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`)
    .replace(/(src=["'])\/images\//gi, `$1${WP_BASE_URL}/images/`)
    .replace(/(src=["'])\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`)
    .replace(/(srcset=["'])\/images\//gi, `$1${WP_BASE_URL}/images/`)
    .replace(/(srcset=["'])\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`)
    .replace(/(href=["'])\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`);

  // Rewrite absolute WordPress site links & staging links in content to relative paths
  const absoluteUrlRegex = new RegExp(`href=["']${WP_BASE_URL.replace(/\//g, '\\/')}(\\/[^"']*)?["']`, 'gi');
  processedHtml = processedHtml.replace(absoluteUrlRegex, (match, path) => `href="${path || '/'}"`);
  processedHtml = processedHtml.replace(/href=["']https:\/\/astro-e3es\.paulbryanvisual\.workers\.dev(\/[^"']*)?["']/gi, (match, path) => `href="${path || '/'}"`);

  // Remove trailing arrows from db-feature overlay buttons to avoid doubling with CSS arrows
  processedHtml = processedHtml.replace(
    /(<a\s+[^>]*class=["'][^"']*db-feature__overlay-button[^"']*["'][^>]*>)(.*?)(?:\s*→|\s*&rarr;|\s*&#8594;)?(<\/a>)/gi,
    '$1$2$3'
  );


  // Fix double escaped entities that cause "&amp;" to show on screen
  processedHtml = processedHtml.replace(/&amp;amp;/g, '&amp;')
                               .replace(/&amp;#038;/g, '&#038;');

  // Clean up any wpautop paragraph injection inside script or style tags
  processedHtml = processedHtml.replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gi, (match, scriptContent) => {
    const cleanScript = scriptContent
      .replace(/<\/?p>/gi, '')
      .replace(/<br\s*\/?>/gi, '\n');
    return match.replace(scriptContent, cleanScript);
  });
  processedHtml = processedHtml.replace(/<style\b[^>]*>([\s\S]*?)<\/style>/gi, (match, styleContent) => {
    const cleanStyle = styleContent
      .replace(/<\/?p>/gi, '')
      .replace(/<br\s*\/?>/gi, '');
    return match.replace(styleContent, cleanStyle);
  });

  // Force client-card layout to justify-content: flex-start (align text/tags to top)
  processedHtml = processedHtml.replace(/(class="[^"]*client-card[^"]*"[^>]*style="[^"]*)justify-content:\s*space-between/gi, '$1justify-content: flex-start');

  // Clean up any wpautop paragraph injection inside clients finder section
  processedHtml = processedHtml.replace(/(<section[^>]*class="[^"]*clients-finder-section[^"]*"[^>]*>)([\s\S]*?)(<\/section>)/gi, (match, startTag, sectionContent, endTag) => {
    const cleanContent = sectionContent
      .replace(/<p>Try removing some filters\.<\/p>/gi, '<!--TEMP_FILTER_P-->')
      .replace(/<\/?p>/gi, '')
      .replace(/<br\s*\/?>/gi, '')
      .replace('<!--TEMP_FILTER_P-->', '<p>Try removing some filters.</p>');
    return startTag + cleanContent + endTag;
  });

  // Re-inject Vimeo iframe inside db-video-wrapper from block attributes if comments are present
  const videoBlockRegex = /<!-- wp:e3es\/video-embed (\{.*?\}) -->[\s\S]*?<div class="db-video-wrapper">([\s\S]*?)<\/div>/gi;
  processedHtml = processedHtml.replace(videoBlockRegex, (match, attrsJson, innerContent) => {
    try {
      if (!innerContent.includes('<iframe')) {
        const attrs = JSON.parse(attrsJson);
        const url = attrs.videoUrl;
        const title = attrs.title || 'Video Embed';
        const cleanUrl = url.replace(/&amp;/g, '&');
        return match.replace(
          `<div class="db-video-wrapper">${innerContent}</div>`,
          `<div class="db-video-wrapper"><iframe src="${cleanUrl}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="${title}"></iframe></div>`
        );
      }
    } catch (e) {
      // Ignore JSON parse errors
    }
    return match;
  });

  // Sanitize all Vimeo iframe sources in the content (both block-comment parsed and raw database html)
  const iframeRegex = /(<div class="(?:db-video-wrapper|video-embed__wrapper)"[^>]*>[\s\S]*?<iframe[^>]+src=")([^"]+)("[^>]*>[\s\S]*?<\/iframe>)/gi;
  processedHtml = processedHtml.replace(iframeRegex, (match, prefix, url, suffix) => {
    const cleanUrl = url.replace(/&amp;/g, '&');
    if (!cleanUrl.includes('player.vimeo.com/video/')) {
      const vimeoMatch = cleanUrl.match(/(?:vimeo\.com\/)(?:channels\/[^\/]+\/|groups\/[^\/]+\/videos\/|manage\/videos\/|showcase\/[^\/]+\/video\/|)?([0-9]{8,})/i);
      if (vimeoMatch && vimeoMatch[1]) {
        const videoId = vimeoMatch[1];
        let hash = '';
        
        const matchPos = cleanUrl.indexOf(vimeoMatch[0]);
        const remaining = cleanUrl.substring(matchPos + vimeoMatch[0].length);
        const postMatch = remaining.match(/^\/([a-zA-Z0-9]+)/);
        if (postMatch) {
          const possibleHash = postMatch[1];
          if (possibleHash.toLowerCase() !== 'dnt' && possibleHash.toLowerCase() !== 'badge') {
            hash = possibleHash;
          }
        } else {
          const hMatch = cleanUrl.match(/[?&]h=([a-zA-Z0-9]+)/);
          if (hMatch) {
            hash = hMatch[1];
          }
        }
        
        let embedUrl = `https://player.vimeo.com/video/${videoId}`;
        const params = [];
        if (hash) {
          params.push(`h=${hash}`);
        }
        params.push('badge=0', 'autopause=0', 'player_id=0', 'app_id=58479');
        embedUrl += '?' + params.join('&');
        return prefix + embedUrl + suffix;
      }
    } else {
      return prefix + cleanUrl + suffix;
    }
    return match;
  });

  // Fallback: If comments are stripped (default public REST API behaviour), use the slug to inject expected Vimeo iframes
  if (slug) {
    const expectedVideos: Record<string, { id: string; title: string }> = {
      'granbury-isd': { id: '227283498', title: 'Granbury ISD Case Study Video' },
      'little-elm-isd': { id: '946653874', title: 'Lessons In Learning - Mike Lamb' },
      'keene-isd': { id: '1176712805', title: 'Keene ISD, Sports Lighting' },
      'plano-isd': { id: '1007829512', title: 'Lessons in Learning - Dr. Theresa Williams' },
      'city-of-stockdale': { id: '1171901749', title: 'Lessons in Learning - Stephen Mayfield' },
      'boyd-isd': { id: '1179578579', title: 'Boyd ISD Case Study Video' },
      'royal-isd': { id: '1179578579', title: 'Royal ISD Case Study Video' }
    };

    if (expectedVideos[slug]) {
      const { id, title } = expectedVideos[slug];
      const wrapperRegex = /<div class="db-video-wrapper">([\s\S]*?)<\/div>/i;
      const match = processedHtml.match(wrapperRegex);
      if (match && !match[1].includes('<iframe')) {
        const cleanUrl = `https://player.vimeo.com/video/${id}?badge=0&autopause=0&player_id=0&app_id=58479`;
        processedHtml = processedHtml.replace(
          match[0],
          `<div class="db-video-wrapper"><iframe src="${cleanUrl}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="${title}"></iframe></div>`
        );
      }
    }

    const partnershipParagraphs: Record<string, string> = {
      'bishop-cisd': 'E3 Entegral Solutions partnered with Bishop CISD to implement comprehensive facility improvements including mechanical upgrades and LED lighting retrofits across the district.',
      'city-of-stockdale': 'E3 Entegral Solutions partnered with the City of Stockdale to implement a lagoon restoration and wastewater treatment program, restoring municipal capacity and efficiency.',
      'keene-isd': 'E3 Entegral Solutions partnered with Keene ISD to upgrade their athletic field lighting with a full RGB LED system, delivering dynamic lighting effects alongside improved visibility and safety.'
    };

    if (partnershipParagraphs[slug]) {
      const pText = partnershipParagraphs[slug];
      const signature = 'E3 Entegral Solutions partnered with';
      if (!processedHtml.includes(signature)) {
        processedHtml = `<!-- wp:paragraph -->\n<p>${pText}</p>\n<!-- /wp:paragraph -->\n\n` + processedHtml;
      }
    }
  }

  let isFirstImage = true;

  processedHtml = processedHtml.replace(/<img([^>]+)>/gi, (match, attrs) => {
    let newAttrs = attrs;

    // Force staging URLs to route through relative proxy path (same-origin optimization)
    newAttrs = newAttrs.replace(
      /https:\/\/descriptive-goldfish\.flywheelstaging\.com\/wp-content\/uploads/gi,
      '/wp-content/uploads'
    );

    // 2. Process LCP (First Image) vs Non-LCP images
    if (isFirstImage) {
      // Eager load and set high priority for LCP
      if (/loading="[a-z]+"/gi.test(newAttrs)) {
        newAttrs = newAttrs.replace(/loading="[a-z]+"/gi, 'loading="eager"');
      } else {
        newAttrs += ' loading="eager"';
      }

      if (!/fetchpriority=/gi.test(newAttrs)) {
        newAttrs += ' fetchpriority="high"';
      }
      isFirstImage = false;
    } else {
      // Lazy load and decode asynchronously for all images below the fold
      if (!/loading=/gi.test(newAttrs)) {
        newAttrs += ' loading="lazy"';
      }
      if (!/decoding=/gi.test(newAttrs)) {
        newAttrs += ' decoding="async"';
      }
    }
    return `<img${newAttrs}>`;
  });

  // Force all absolute WordPress resource URLs (local or staging) to route through same-origin relative proxy path
  processedHtml = processedHtml.replace(/https?:\/\/[^\/]+\/wp-(content|includes)/gi, '/wp-$1');
  return processedHtml;
}

/**
 * Utility to decode HTML entities from WordPress titles/content.
 */
export function decodeHtmlEntities(text: string): string {
  if (!text) return text;
  let decoded = text
    .replace(/&amp;amp;/g, 'and')
    .replace(/&amp;/g, 'and')
    .replace(/&#038;/g, 'and')
    .replace(/\\u0026amp;/gi, 'and')
    .replace(/\\u0026/gi, 'and')
    .replace(/u0026amp;/gi, 'and')
    .replace(/u0026/gi, 'and')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#034;/g, '"')
    .replace(/&#34;/g, '"')
    .replace(/&apos;/g, "'")
    .replace(/&#039;/g, "'")
    .replace(/&#39;/g, "'")
    .replace(/&#8211;/g, '–')
    .replace(/&#8212;/g, '—')
    .replace(/&#8216;/g, '‘')
    .replace(/&#8217;/g, '’')
    .replace(/&#8220;/g, '“')
    .replace(/&#8221;/g, '”')
    .replace(/&#8230;/g, '…')
    .replace(/&nbsp;/g, ' ')
    .replace(/&deg;/g, '°')
    .replace(/&trade;/g, '™')
    .replace(/&reg;/g, '®')
    .replace(/&copy;/g, '©')
    .replace(/&(?!(?:[a-zA-Z]+|#\\d+|#x[a-fA-F0-9]+);)/g, 'and');

  // Collapse multiple spaces around 'and' to keep formatting clean
  decoded = decoded.replace(/\s+and\s+/gi, ' and ');
  return decoded.trim();
}

/**
 * Normalize permalinks and URLs to match Astro's clean flat routing
 */
export function getRelativeUrl(url: string) {
  if (!url) return '#';
  try {
    const u = new URL(url);
    let pathname = u.pathname;
    pathname = pathname.replace(/^\/home\/our-approach\//, '/');
    pathname = pathname.replace(/^\/home\/about-us\//, '/');
    pathname = pathname.replace(/^\/home\/industries\//, '/');
    pathname = pathname.replace(/^\/home\//, '/');
    return pathname + u.search + u.hash;
  } catch {
    let pathname = url;
    pathname = pathname.replace(/^\/home\/our-approach\//, '/');
    pathname = pathname.replace(/^\/home\/about-us\//, '/');
    pathname = pathname.replace(/^\/home\/industries\//, '/');
    pathname = pathname.replace(/^\/home\//, '/');
    return pathname;
  }
}

/**
 * Build breadcrumb data for a given item based on parent/cross_post_parent relationships.
 */
export function buildBreadcrumbs(currentItem: any, allItems: any[]) {
  const breadcrumbs = [];
  const itemMap = new Map(allItems.map(i => [i.id, i]));

  // Build hierarchy upwards
  let current = currentItem;
  const path = [];
  while (current) {
    path.push(current);
    // Find parent via native parent OR cross_post_parent
    const parentId = current.parent || (current.meta && parseInt(current.meta.cross_post_parent));
    if (parentId && itemMap.has(parentId)) {
      current = itemMap.get(parentId);
    } else {
      break;
    }
  }

  // Reverse to get root -> child order
  path.reverse();

  // Check if the root of the path is the homepage
  const hasHomepageInPath = path.length > 0 && 
    (path[0].slug === 'home' || 
     path[0].id === 7 || 
     (path[0].title?.rendered || path[0].title || '').toLowerCase() === 'e3 homepage');

  if (!hasHomepageInPath) {
    // Add Home fallback
    breadcrumbs.push({
      label: 'Home',
      href: '/'
    });
  }

const shortServiceNames: Record<string, string> = {
  'roofing': 'Roofing',
  'building-envelope': 'Building Envelope',
  'facility-assessments': 'Facility Assessments',
  'lighting': 'LED Lighting',
  'electrical': 'Electrical',
  'energy-management': 'Energy Management',
  'hvac': 'HVAC & Controls',
  'indoor-air-quality': 'Indoor Air Quality',
  'planning-bond-advisory-services': 'Planning & Bond Services',
  'water': 'Water & Wastewater'
};

function getServiceShortLabel(c: any): string {
  if (c.meta && c.meta._e3_menu_link_text && c.meta._e3_menu_link_text.trim() !== '') {
    return decodeHtmlEntities(c.meta._e3_menu_link_text);
  }
  if (c._e3_menu_link_text && typeof c._e3_menu_link_text === 'string' && c._e3_menu_link_text.trim() !== '') {
    return decodeHtmlEntities(c._e3_menu_link_text);
  }
  if (c.slug && shortServiceNames[c.slug]) {
    return shortServiceNames[c.slug];
  }
  const raw = c.title?.rendered || c.title || 'Untitled';
  return decodeHtmlEntities(raw);
}

  if (path.length > 0 && path[0].type === 'services') {
    const excludedSlugs = ['chiller-plants', 'boiler-plants', 'cooling-towers'];
    const rootServices = allItems.filter(item => {
      const parentId = item.parent || (item.meta && parseInt(item.meta.cross_post_parent));
      return item.type === 'services' && !parentId && !item.slug?.includes('trashed') && !excludedSlugs.includes(item.slug);
    });

    const rootDropdown = rootServices.map(c => {
      return {
        label: getServiceShortLabel(c),
        href: getRelativeUrl(c.link)
      };
    });

    rootDropdown.sort((a, b) => 
      a.label.localeCompare(b.label, 'en', { sensitivity: 'base', numeric: true })
    );

    breadcrumbs.push({
      label: 'Services',
      href: '/services',
      dropdown: rootDropdown
    });
  }

  // Build breadcrumbs with dropdowns
  for (let i = 0; i < path.length; i++) {
    const item = path[i];
    const isLast = i === path.length - 1;
    
    // Find children for dropdown (pages that have this item as their parent)
    let children = [];
    const excludedSlugs = ['chiller-plants', 'boiler-plants', 'cooling-towers'];
    if (item.id === 11 || item.slug === 'services') {
      children = allItems.filter(child => {
        const childParentId = child.parent || (child.meta && parseInt(child.meta.cross_post_parent));
        return child.type === 'services' && !childParentId && !child.slug?.includes('trashed') && !excludedSlugs.includes(child.slug);
      });
    } else {
      children = allItems.filter(child => {
        const childParentId = child.parent || (child.meta && parseInt(child.meta.cross_post_parent));
        return childParentId === item.id && !child.slug?.includes('trashed');
      });
    }

    let label = item.title?.rendered || item.title || 'Untitled';
    label = decodeHtmlEntities(label);
    if (i === 0 && hasHomepageInPath) {
      label = 'Home';
    }

    const childrenDropdown = children.map(c => {
      return {
        label: getServiceShortLabel(c),
        href: getRelativeUrl(c.link)
      };
    });

    childrenDropdown.sort((a, b) => 
      a.label.localeCompare(b.label, 'en', { sensitivity: 'base', numeric: true })
    );

    breadcrumbs.push({
      label: label,
      href: isLast ? undefined : getRelativeUrl(item.link),
      dropdown: childrenDropdown
    });
  }

  return breadcrumbs;
}

