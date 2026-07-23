const fs = require('fs');

// Fetch http://localhost:4008/funding and print everything from <body to <section
async function run() {
  const res = await fetch('http://localhost:4008/funding');
  const html = await res.text();

  const startIdx = html.indexOf('<body');
  const endIdx = html.indexOf('</section>', startIdx);
  
  console.log(html.substring(startIdx, endIdx + 10));
}

run();
