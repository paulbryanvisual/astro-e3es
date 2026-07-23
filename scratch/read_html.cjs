const fs = require('fs');
const html = fs.readFileSync('scratch/live_story.html', 'utf8');
const cleanHtml = html.replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '')
                      .replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '');
const text = cleanHtml.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
console.log(text.substring(0, 5000));
