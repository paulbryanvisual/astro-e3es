import fs from 'node:fs';
import path from 'node:path';

const wranglerJsonPath = path.join(process.cwd(), 'dist', 'server', 'wrangler.json');

if (fs.existsSync(wranglerJsonPath)) {
  try {
    const data = fs.readFileSync(wranglerJsonPath, 'utf8');
    const config = JSON.parse(data);

    // Remove the ASSETS binding which causes errors in Cloudflare Pages V3 Git integration
    if (config.assets && config.assets.binding === 'ASSETS') {
      delete config.assets.binding;
      console.log('✅ [patch-wrangler] Removed reserved ASSETS binding from generated wrangler.json');
    }

    fs.writeFileSync(wranglerJsonPath, JSON.stringify(config, null, 2), 'utf8');
  } catch (error) {
    console.error('❌ [patch-wrangler] Failed to patch wrangler.json:', error);
  }
} else {
  console.warn('⚠️ [patch-wrangler] wrangler.json not found at', wranglerJsonPath);
}
