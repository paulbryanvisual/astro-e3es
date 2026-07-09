async function run() {
    const res = await fetch('http://localhost:4008/clients/granbury-isd');
    const html = await res.text();
    const mainMatch = html.match(/<main[^>]*>([\s\S]*?)<\/main>/i);
    if (mainMatch) {
        console.log(mainMatch[1].trim());
    } else {
        console.log('Main element not found');
    }
}
run();
