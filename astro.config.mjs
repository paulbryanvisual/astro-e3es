// @ts-check
import { defineConfig } from 'astro/config';

// https://astro.build/config
export default defineConfig({
  output: 'static',
  image: {
    domains: ['e3es2026.local'],
    remotePatterns: [{ protocol: 'https' }]
  },
  server: {
    port: 4383,       // E3-specific port (43 = E3). Shared across dev sessions.
  },
  // Redirect legacy slug variants to WP canonical slugs
  redirects: {
    // Regional pages — hyphen normalization & client to page redirects
    '/clients/south-texas':                '/home/industries/k12/south-texas',
    '/clients/north-texas':                '/home/industries/k12/north-texas',
    '/clients/central-texas':              '/home/industries/k12/central-texas',
    '/clients/east-texas':                 '/home/industries/k12/east-texas',
    '/clients/west-texas':                 '/home/industries/k12/west-texas',
    '/clients/far-west-texas':             '/home/industries/k12/far-west-texas',
    '/clients/south-east-texas':           '/home/industries/k12/south-east-texas',
    '/clients/north-east-texas':           '/home/industries/k12/north-east-texas',
    '/clients/panhandle':                  '/home/industries/k12/panhandle',
    '/clients/hill-country':               '/home/industries/k12/hill-country',
    '/southeast-texas':                    '/home/industries/k12/south-east-texas',
    '/northeast-texas':                    '/home/industries/k12/north-east-texas',
    '/northwest-texas':                    '/home/industries/k12/north-west-texas',
    '/southwest-texas':                    '/home/industries/k12/south-west-texas',
    // Client pages — slug changed during WP import
    '/gwh':                                '/clients/goodall-witcher-healthcare',
    '/houston-cc':                         '/clients/houston-community-college',
    '/carrizo-springs-consolidated-isd':   '/clients/carrizo-springs-cisd',
  }
});
