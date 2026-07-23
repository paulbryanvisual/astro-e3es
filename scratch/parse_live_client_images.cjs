const fs = require('fs');
const html = fs.readFileSync('scratch/live_clients.html', 'utf8');

const regex = /<div class="project-grid-wrapper">([\s\S]*?)<\/div>\s*<\/div>\s*<\/div>/gi;
let match;
const data = [];

const imgRegex = /<img[^>]+src=["']([^"']+)["']/i;
const titleRegex = /<h4[^>]*class=["'][^"']*project-grid-item-title[^"']*["'][^>]*><a[^>]*>([^<]+)<\/a>/i;

while ((match = regex.exec(html)) !== null) {
    const block = match[1];
    const imgMatch = block.match(imgRegex);
    const titleMatch = block.match(titleRegex);
    if (titleMatch) {
        data.push({
            title: titleMatch[1].trim(),
            image: imgMatch ? imgMatch[1].trim() : ''
        });
    }
}

console.log(JSON.stringify(data, null, 2));
