// @ts-check
import { defineConfig } from 'astro/config';

// https://astro.build/config
export default defineConfig({
  output: 'static',
  image: {
    remotePatterns: [{ protocol: 'https' }]
  }
});
