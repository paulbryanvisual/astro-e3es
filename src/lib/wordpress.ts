const WP_URL = 'https://descriptive-goldfish.flywheelstaging.com/wp-json/wp/v2';

export async function getPosts() {
  const response = await fetch(`${WP_URL}/posts?_embed`);
  if (!response.ok) {
    throw new Error('Failed to fetch posts');
  }
  return response.json();
}

export async function getPostBySlug(slug: string) {
  const response = await fetch(`${WP_URL}/posts?slug=${slug}&_embed`);
  if (!response.ok) {
    throw new Error(`Failed to fetch post: ${slug}`);
  }
  const posts = await response.json();
  return posts.length > 0 ? posts[0] : null;
}

export async function getPages() {
  const response = await fetch(`${WP_URL}/pages?_embed`);
  if (!response.ok) {
    throw new Error('Failed to fetch pages');
  }
  return response.json();
}

export async function getPageBySlug(slug: string) {
  const response = await fetch(`${WP_URL}/pages?slug=${slug}&_embed`);
  if (!response.ok) {
    throw new Error(`Failed to fetch page: ${slug}`);
  }
  const pages = await response.json();
  return pages.length > 0 ? pages[0] : null;
}

export async function getClients() {
  const response = await fetch(`${WP_URL}/clients?_embed&per_page=100`);
  if (!response.ok) {
    throw new Error('Failed to fetch clients');
  }
  return response.json();
}
