const fs = require('fs');

async function main() {
    const res = await fetch('http://localhost:4008/clients/woodville-isd');
    const text = await res.text();
    fs.writeFileSync('scratch/woodville_fetched.html', text);
    console.log('Fetched woodville-isd. Length:', text.length);
    console.log('Includes taj-mahal:', text.includes('taj-mahal-placeholder'));
    console.log('Includes wp-block-e3es-project:', text.includes('wp-block-e3es-project'));
}

main().catch(console.error);
