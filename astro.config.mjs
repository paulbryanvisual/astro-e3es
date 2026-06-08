// @ts-check
import { defineConfig } from 'astro/config';

// https://astro.build/config
export default defineConfig({
  output: 'static',
  image: {
    domains: ['e3es2026.local'],
    remotePatterns: [{ protocol: 'https' }]
  }
});
