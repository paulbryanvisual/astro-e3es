async function run() {
  const postTypes = ['pages', 'posts', 'services', 'clients', 'projects', 'project-items', 'case-studies'];
  for (const type of postTypes) {
    const url = `http://e3es2026.local/wp-json/wp/v2/${type}/1641`;
    try {
      const response = await fetch(url);
      if (response.ok) {
        const data = await response.json();
        console.log(`Found under post type: ${type}!`);
        console.log(`Title: ${data.title ? data.title.rendered : 'No Title'}`);
        console.log("--- RAW CONTENT ---");
        console.log(data.content ? (data.content.raw || data.content.rendered) : 'No content');
        return;
      }
    } catch (e) {
      console.error(e);
    }
  }
  console.log("Post 1641 not found under standard post types.");
}
run();
