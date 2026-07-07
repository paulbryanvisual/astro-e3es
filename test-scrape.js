async function test() {
  const url = 'https://www.e3es.com/projects-item/boyd-isd/';
  const res = await fetch(url);
  const html = await res.text();
  
  const imgRegex = /<img[^>]+src="([^">]+)"/g;
  let match;
  const images = [];
  while ((match = imgRegex.exec(html)) !== null) {
      const src = match[1];
      if (src.includes('uploads') && !src.includes('E3_WebLogo')) {
          images.push(src);
      }
  }
  console.log(images);
}
test();
