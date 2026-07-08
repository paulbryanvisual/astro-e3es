import fs from 'fs';
fetch('http://e3es2026.local/wp-json/wp/v2/pages?per_page=100').then(res => res.json()).then(pages => {
  const paths = pages.map(page => {
    try {
      const url = new URL(page.link);
      return url.pathname.replace(/^\/|\/$/g, '');
    } catch (e) { return page.slug; }
  });
  console.log(paths.filter(p => p.includes('texas')));
});
