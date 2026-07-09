const fs = require('fs');
const html = fs.readFileSync('/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/.system_generated/steps/1101/content.md', 'utf8');

// Find all src attributes or images
const images = [];
const imgRegex = /<img[^>]+src=["']([^"']+)["']/gi;
let match;
while ((match = imgRegex.exec(html)) !== null) {
  images.push(match[1]);
}

console.log("Images found on live page:", images);

// Also look at the body elements
const bodyStart = html.indexOf('<body');
if (bodyStart !== -1) {
  console.log("\nBody content snippet:");
  console.log(html.substring(bodyStart, bodyStart + 2000));
} else {
  console.log("No <body> tag found.");
}
