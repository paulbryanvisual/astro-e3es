# Project: Clients List Parity & Content Audit

## Architecture
- Astro Frontend fetches client data from WordPress REST API (`/wp-json/wp/v2/clients`) at build time.
- Individual client pages are rendered dynamically or statically based on WordPress slug (`src/pages/clients/[slug].astro`).
- Content includes text blocks, featured images, inline images, and videos.

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|---|---|---|---|
| 1 | Investigation & Audit | Compare local site vs. live site clients and individual pages; identify missing content | None | COMPLETED (cb515917-83d6-438b-93ec-8cf697003c2b) |
| 2 | E2E Testing Suite | Create a robust automated test suite to check list and subpage parity | M1 | COMPLETED (2e9c163a-7eb4-42b6-8c48-50ed35ccaa16) |
| 3 | Content Sync / WP Database Updates | Add/remove clients, update text/image/video fields in local WordPress backend | M1, M2 | COMPLETED (74bdddbc-1e26-4794-a488-d96a8786c039) |
| 4 | Final Verification & Hardening | Run all tests, ensure 100% parity, perform adversarial checks | M3 | IN_PROGRESS (32f3c841-ce2a-4500-979e-776260f2c422) |

## Code Layout
- Astro pages: `src/pages/clients.astro`, `src/pages/clients/[slug].astro`
- Data fetch logic: `src/lib/wordpress.ts`
- Dev server: `http://localhost:4008/` or proxy URL
- WordPress database management: via WP-CLI / local sqlite or mysql database
