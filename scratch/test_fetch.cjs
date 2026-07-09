async function run() {
  try {
    const res = await fetch('https://www.e3es.com/projects-item/boyd-isd/', {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
      }
    });
    console.log('Status:', res.status);
    const text = await res.text();
    console.log('Length:', text.length);
  } catch (e) {
    console.error('Fetch Error:', e);
  }
}
run();
