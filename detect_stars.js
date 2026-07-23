const sharp = require('sharp');
const fs = require('fs');

async function run() {
  const imgPath = '/Users/bryanpaul/Local Sites/astro-e3es/public/images/Texas-Map---green-with-dark-stars.jpg';
  const { data, info } = await sharp(imgPath)
    .raw()
    .toBuffer({ resolveWithObject: true });

  const width = info.width;
  const height = info.height;
  const channels = info.channels;

  const grid = Array.from({ length: height }, () => new Uint8Array(width));
  const blackPixels = [];

  for (let y = 0; y < height; y++) {
    for (let x = 0; x < width; x++) {
      const idx = (y * width + x) * channels;
      const r = data[idx];
      const g = data[idx+1];
      const b = data[idx+2];

      // Detect dark star pixels
      if (r < 60 && g < 60 && b < 60) {
        grid[y][x] = 1;
        blackPixels.push({ x, y });
      }
    }
  }

  const visited = Array.from({ length: height }, () => new Uint8Array(width));
  const stars = [];

  for (const pixel of blackPixels) {
    if (visited[pixel.y][pixel.x]) continue;

    const cluster = [];
    const queue = [pixel];
    visited[pixel.y][pixel.x] = 1;

    while (queue.length > 0) {
      const curr = queue.shift();
      cluster.push(curr);

      for (let dy = -1; dy <= 1; dy++) {
        for (let dx = -1; dx <= 1; dx++) {
          if (dx === 0 && dy === 0) continue;
          const nx = curr.x + dx;
          const ny = curr.y + dy;
          if (nx >= 0 && nx < width && ny >= 0 && ny < height) {
            if (grid[ny][nx] && !visited[ny][nx]) {
              visited[ny][nx] = 1;
              queue.push({ x: nx, y: ny });
            }
          }
        }
      }
    }

    // Ignore noise clusters
    if (cluster.length >= 4) {
      let sumX = 0;
      let sumY = 0;
      for (const p of cluster) {
        sumX += p.x;
        sumY += p.y;
      }
      const cx = sumX / cluster.length;
      const cy = sumY / cluster.length;
      stars.push({ x: Math.round(cx), y: Math.round(cy) });
    }
  }

  console.log(`Detected ${stars.length} stars.`);
  fs.writeFileSync('/Users/bryanpaul/.gemini/antigravity/brain/b9c8b880-8835-4792-8e98-4e16468a4b3a/scratch/stars.json', JSON.stringify(stars, null, 2));
}

run().catch(console.error);
