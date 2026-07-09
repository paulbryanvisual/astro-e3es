# Project: Content Migration and Layout Enhancement

## Architecture
- Headless WordPress API (`http://e3es2026.local/`) serves custom post type `clients` content.
- Astro Frontend (`http://localhost:4008/`) fetches client posts and renders pages dynamically.
- WordPress Gutenberg Block editor is used to lay out client subpages. Standard block structure:
  - Banner block
  - Relationship/description paragraph
  - Custom Project block (`wp:e3es/project`) enclosing project details
  - WP native Gallery block at the bottom using Flickr downloaded images
- Astro pulls these blocks, parses them, and renders them.

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | Exploration & Architecture Audit | Audit WP database client post types, Astro route rendering, Flickr downloads directory, and existing test suite `tests/clients-parity.test.js`. | None | PLANNED |
| 2 | Media Upload & Migration Scripting | Write/run migration tool to extract live client content, upload matching Flickr photos to WP media library, and map Flickr images to additional projects. | M1 | PLANNED |
| 3 | Gutenberg Layout Refactoring | Implement Gutenberg block structure alignment: relationship paragraph, nested project blocks, and bottom gallery block for all 100 client subpages. | M2 | PLANNED |
| 4 | Test Suite Resolution & Integrity Verification | Run `node tests/clients-parity.test.js` to ensure 100% pass, fix placeholders, run Forensic Audit. | M3 | PLANNED |
| 5 | Visual Verification & Report Generation | Use headless Chrome DevTools to visually inspect rendered Astro pages, capture screenshots, and generate layout verification report. | M4 | PLANNED |

## Code Layout
- Astro Client Pages: `src/pages/clients/` or similar routes
- E2E Test: `tests/clients-parity.test.js`
- Flickr Downloads: `/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads`
- WordPress Sync & Helpers: `scripts/` (to be determined during exploration)

## Interface Contracts
### WordPress REST API ↔ Astro Client Route
- WordPress client post type returns content containing HTML comments representing Gutenberg blocks.
- Astro frontend parses blocks (specifically `wp:e3es/project`) and renders layout accordingly.
