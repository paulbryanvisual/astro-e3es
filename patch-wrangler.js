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

    // Remove the auto-injected SESSION KV namespace (Causes 500 errors on Cloudflare Pages)
    if (config.kv_namespaces) {
      delete config.kv_namespaces;
      console.log('✅ [patch-wrangler] Removed auto-injected SESSION KV binding from generated wrangler.json');
    }
    
    // Also remove previews.kv_namespaces just in case
    if (config.previews && config.previews.kv_namespaces) {
      delete config.previews.kv_namespaces;
    }

    // Create the _worker.js wrapper required by Cloudflare Pages since Astro 6 only outputs Workers format
    const workerWrapperPath = path.join(process.cwd(), 'dist', '_worker.js');
    const workerCode = `import entry from "./server/entry.mjs";\nexport default entry;`;
    fs.writeFileSync(workerWrapperPath, workerCode, 'utf8');
    console.log('✅ [patch-wrangler] Created _worker.js wrapper for Cloudflare Pages SSR compatibility');

    // Delete the generated wrangler.json completely so Cloudflare Pages CI/CD does not try to parse it
    // and crash due to Worker-specific keys (like 'main', 'rules', 'assets').
    fs.unlinkSync(wranglerJsonPath);
    console.log('✅ [patch-wrangler] Deleted generated wrangler.json to bypass Cloudflare Pages strict validation');

    // Also delete the .wrangler directory because Astro's adapter creates a redirect in .wrangler/deploy/config.json
    // that points to the deleted wrangler.json, which causes a "does not exist" crash!
    const wranglerCacheDir = path.join(process.cwd(), '.wrangler');
    if (fs.existsSync(wranglerCacheDir)) {
      fs.rmSync(wranglerCacheDir, { recursive: true, force: true });
      console.log('✅ [patch-wrangler] Deleted .wrangler cache directory to clear stale redirects');
    }

  } catch (error) {
    console.error('❌ [patch-wrangler] Failed to patch wrangler.json:', error);
  }
} else {
  console.warn('⚠️ [patch-wrangler] wrangler.json not found at', wranglerJsonPath);
}
