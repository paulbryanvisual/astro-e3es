const WP_URL = import.meta.env.PUBLIC_WP_URL || (import.meta.env.PROD 
  ? 'https://descriptive-goldfish.flywheelstaging.com/wp-json/wp/v2'
  : 'http://e3es2026.local/wp-json/wp/v2');

const WP_BASE_URL = WP_URL.replace('/wp-json/wp/v2', '');

async function wpFetch(urlPath: string) {
  const separator = urlPath.includes('?') ? '&' : '?';
  const url = `${WP_URL}${urlPath}${separator}t=${Date.now()}`;
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
  const response = await wpFetch('/clients?_embed&per_page=100');
  if (!response.ok) {
    throw new Error('Failed to fetch clients');
  }
  return response.json();
}

/**
 * Server-side HTML utility to optimize images in Gutenberg block content.
 */
export function optimizeHtmlImages(html: string): string {
  if (!html) return '';

  // 1. Rewrite relative paths to absolute WordPress server paths
  let processedHtml = html
    .replace(/(url\(["']?)\/images\//gi, `$1${WP_BASE_URL}/images/`)
    .replace(/(url\(["']?)\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`)
    .replace(/(src=["'])\/images\//gi, `$1${WP_BASE_URL}/images/`)
    .replace(/(src=["'])\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`)
    .replace(/(srcset=["'])\/images\//gi, `$1${WP_BASE_URL}/images/`)
    .replace(/(srcset=["'])\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`)
    .replace(/(href=["'])\/wp-content\//gi, `$1${WP_BASE_URL}/wp-content/`);

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

