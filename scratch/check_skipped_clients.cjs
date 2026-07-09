const fs = require('fs');
const log = fs.readFileSync('/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/.system_generated/tasks/task-1213.log', 'utf8');

console.log("Failed/Skipped items in migration log:");
const lines = log.split('\n');
lines.forEach(line => {
  if (line.includes('Skipping') || line.includes('Failed') || line.includes('Warning') || line.includes('Warning:')) {
    console.log(line);
  }
});
