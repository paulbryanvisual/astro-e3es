// @ts-check
import { defineConfig } from 'astro/config';

// https://astro.build/config
export default defineConfig({
  output: 'static',
  image: {
    domains: ['e3es2026.local'],
    remotePatterns: [{ protocol: 'https' }, { protocol: 'http' }]
  },
  server: {
    port: 4383,       // E3-specific port (43 = E3). Shared across dev sessions.
  },
  vite: {
    server: {
      proxy: {
        '/wp-content': {
          target: 'http://e3es2026.local',
          changeOrigin: true
        },
        '/wp-includes': {
          target: 'http://e3es2026.local',
          changeOrigin: true
        }
      }
    }
  },
  // Redirect legacy slug variants to WP canonical slugs
  redirects: {
    // Regional pages — hyphen normalization & client to page redirects
    '/clients/south-texas':                '/k12/south-texas',
    '/clients/north-texas':                '/k12/north-texas',
    '/clients/central-texas':              '/k12/central-texas',
    '/clients/east-texas':                 '/k12/east-texas',
    '/clients/west-texas':                 '/k12/west-texas',
    '/clients/far-west-texas':             '/k12/far-west-texas',
    '/clients/south-east-texas':           '/k12/south-east-texas',
    '/clients/north-east-texas':           '/k12/north-east-texas',
    '/clients/panhandle':                  '/k12/panhandle',
    '/clients/hill-country':               '/k12/hill-country',
    '/southeast-texas':                    '/k12/south-east-texas',
    '/northeast-texas':                    '/k12/north-east-texas',
    '/northwest-texas':                    '/k12/north-west-texas',
    '/southwest-texas':                    '/k12/south-west-texas',
    '/client-pages — slug changed during WP import': '',
    '/gwh':                                '/clients/goodall-witcher-healthcare',
    '/houston-cc':                         '/clients/houston-community-college',
    '/carrizo-springs-consolidated-isd':   '/clients/carrizo-springs-cisd',
  }
});
