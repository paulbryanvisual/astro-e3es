import fs from 'fs';
const html = fs.readFileSync('scratch/clients_content.html', 'utf8');
console.log('Includes space-between:', html.includes('space-between'));
console.log('Includes justify-content:', html.includes('justify-content'));

// Find lines containing justify-content
const lines = html.split('\n');
lines.forEach((line, idx) => {
  if (line.includes('justify-content')) {
    console.log(`Line ${idx + 1}: ${line.trim()}`);
  }
});
