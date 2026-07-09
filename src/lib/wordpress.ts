import fs from 'fs';
import path from 'path';
import { cacheBuster } from './cache.ts';

const WP_URL = import.meta.env.PUBLIC_WP_URL || (import.meta.env.PROD 
  ? 'https://descriptive-goldfish.flywheelstaging.com/wp-json/wp/v2'
  : 'http://e3es2026.local/wp-json/wp/v2');

const WP_BASE_URL = WP_URL.replace('/wp-json/wp/v2', '');

async function wpFetch(urlPath: string) {
  const separator = urlPath.includes('?') ? '&' : '?';
  const url = `${WP_URL}${urlPath}${separator}t=${Date.now()}&cb=${cacheBuster}`;
  return fetch(url, { cache: 'no-store' });
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

function getTexasMapSvg() {
  try {
    const svgPath = path.resolve(process.cwd(), 'public/Texas-Map-Editable.svg');
    let svg = fs.readFileSync(svgPath, 'utf8');
    
    // Remove XML declaration
    svg = svg.replace(/<\?xml[^>]*\?>/i, '');
    
    // Ensure the SVG element has the correct class and id for styling
    svg = svg.replace(/<svg([^>]*)>/i, (match, attrs) => {
      const viewBoxMatch = attrs.match(/viewBox=["']([^"']+)["']/i);
      const viewBox = viewBoxMatch ? viewBoxMatch[1] : '0 0 941.76 907.17';
      return `<svg id="texas-map-svg" viewBox="${viewBox}" class="db-feature__image texas-svg-map" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">`;
    });
    
    return svg;
  } catch (e) {
    console.error("Failed to read editable SVG map from public folder:", e);
    return '';
  }
}

/**
 * Server-side HTML utility to optimize images in Gutenberg block content.
 */
export function processWordPressHtml(html: string, slug?: string): string {
  if (!html) return '';

  // Replace blurry map image with inline SVG of Texas
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

  // Remove trailing arrows from db-feature overlay buttons to avoid doubling with CSS arrows
  processedHtml = processedHtml.replace(
    /(<a\s+[^>]*class=["'][^"']*db-feature__overlay-button[^"']*["'][^>]*>)(.*?)(?:\s*→|\s*&rarr;|\s*&#8594;)?(<\/a>)/gi,
    '$1$2$3'
  );


  // Fix double escaped entities that cause "&amp;" to show on screen
  processedHtml = processedHtml.replace(/&amp;amp;/g, '&amp;')
                               .replace(/&amp;#038;/g, '&#038;');

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

  // Fallback: If comments are stripped (default public REST API behaviour), use the slug to inject expected Vimeo iframes
  if (slug) {
    const expectedVideos: Record<string, { id: string; title: string }> = {
      'granbury-isd': { id: '227283498', title: 'Granbury ISD Case Study Video' },
      'little-elm-isd': { id: '946653874', title: 'Lessons In Learning - Mike Lamb' },
      'keene-isd': { id: '1176712805', title: 'Keene ISD, Sports Lighting' },
      'plano-isd': { id: '1007829512', title: 'Lessons in Learning - Dr. Theresa Williams' },
      'city-of-stockdale': { id: '1171901749', title: 'Lessons in Learning - Stephen Mayfield' },
      'boyd-isd': { id: '1179578579', title: 'Boyd ISD Case Study Video' }
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
  }

  let isFirstImage = true;

  return processedHtml.replace(/<img([^>]+)>/gi, (match, attrs) => {
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
}

/**
 * Utility to decode HTML entities from WordPress titles/content.
 */
export function decodeHtmlEntities(text: string): string {
  if (!text) return text;
  return text
    .replace(/&amp;/g, '&')
    .replace(/&#038;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&#8211;/g, '–')
    .replace(/&#8212;/g, '—')
    .replace(/&#8216;/g, '‘')
    .replace(/&#8217;/g, '’')
    .replace(/&#8220;/g, '“')
    .replace(/&#8221;/g, '”')
    .replace(/&nbsp;/g, ' ');
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

  if (path.length > 0 && path[0].type === 'services') {
    const rootServices = allItems.filter(item => {
      const parentId = item.parent || (item.meta && parseInt(item.meta.cross_post_parent));
      return item.type === 'services' && !parentId;
    });

    breadcrumbs.push({
      label: 'Services',
      href: '/services',
      dropdown: rootServices.map(c => ({
        label: c.title?.rendered || c.title,
        href: getRelativeUrl(c.link)
      }))
    });
  }

  // Build breadcrumbs with dropdowns
  for (let i = 0; i < path.length; i++) {
    const item = path[i];
    const isLast = i === path.length - 1;
    
    // Find children for dropdown (pages that have this item as their parent)
    const children = allItems.filter(child => {
      const childParentId = child.parent || (child.meta && parseInt(child.meta.cross_post_parent));
      return childParentId === item.id;
    });

    let label = item.title?.rendered || item.title || 'Untitled';
    label = decodeHtmlEntities(label);
    if (i === 0 && hasHomepageInPath) {
      label = 'Home';
    }

    breadcrumbs.push({
      label: label,
      href: isLast ? undefined : getRelativeUrl(item.link),
      dropdown: children.map(c => ({
        label: c.title?.rendered || c.title,
        href: getRelativeUrl(c.link)
      }))
    });
  }

  return breadcrumbs;
}

