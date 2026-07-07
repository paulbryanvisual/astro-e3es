## 2026-07-07: HTML Entity Decoding Fix

**Architectural Decisions:**
- Addressed an issue where HTML entities (like `&#038;`) were being doubly-escaped by Astro's native template parser when rendering titles from WordPress.
- Introduced a lightweight `decodeHtmlEntities()` utility within `src/lib/wordpress.ts` rather than overriding Astro's secure `set:html` directives across the UI, keeping the component markup clean and safe.

**Dependencies Added:**
- None. Used native JavaScript string `.replace()` with Regex matching.

**Core Files Modified:**
- `src/lib/wordpress.ts`
- `src/pages/clients/[slug].astro`
- `src/pages/clients.astro`
- `src/components/ClientsList.astro`
- `src/components/ProjectHistory.astro`

## 2026-07-07: E3 Project Hero Image Mask Styles

**Architectural Decisions:**
- Added three distinct Gutenberg block style variations for the `e3es/project` hero component: Default, White Mask, and Green Texture Behind Photo.
- Implemented a parallax mask effect where the photo itself remains fixed as the masks animate across the screen. This was achieved by passing a CSS variable `--hero-img` via the block container and dynamically binding it to `background-image` on the mask elements in SCSS.
- Safely applied the `--hero-img` inline property via JavaScript on the Astro frontend to avoid any potential filtering issues in WordPress.
- Re-synced Astro SCSS to the WordPress editor visual styles to ensure 1-to-1 feature parity in the Gutenberg editor.

**Dependencies Added:**
- None. Used native CSS masking, background-attachment properties, and vanilla JS.

**Core Files Modified:**
- `e3es-headless-helper/editor-blocks.js`
- `src/styles/mobile.scss`
- `e3es-headless-helper/editor-styles.css`
- `src/pages/clients/[slug].astro`
- `src/lib/wordpress.ts` (Fixed an unclosed parameter type that caused Vite build failures)

## 2026-07-07: Headless Workflow & Git Hygiene Audit

**Architectural Decisions:**
- Investigated the historical existence and subsequent disappearance of the `wp-post-id` meta tag on Astro pages. Verified that the tag was added by an agent on June 16 but abandoned as an uncommitted local change, which was eventually wiped out.
- Implemented the tag permanently in `Layout.astro` and bound it to `wpPostId` props across dynamic route templates (`[...slug].astro`, `clients/[slug].astro`).
- Implemented a strictly enforced "Zero-Delay Commit Rule" in global `AGENTS.md` to guarantee that all agents immediately commit file modifications before pausing for user review, preventing future loss of local, uncommitted code.
- Wrapped the `set:html={optimizedContent}` string within a new `<div class="services-page__content">` div inside `[...slug].astro` to fix layout constraints.

**Dependencies Added:**
- None.

**Core Files Modified:**
- `src/layouts/Layout.astro`
- `src/pages/[...slug].astro`
- `src/pages/clients/[slug].astro`
- `~/.gemini/config/AGENTS.md`

## 2026-07-07T22:29:00Z - Headless Live Preview Cache Fix
**Architectural Decisions:**
- Replaced the slow `npm run build` and ineffective `touch astro.config.mjs` background commands in the WordPress headless plugin with a lightweight File-based Cache Invalidation workflow.
- Created a cache-buster module (`src/lib/cache.ts`) in the Astro app and linked it to `wordpress.ts` fetch client (`?t=...&cb=...`).
- Configured the WP plugin to update this timestamp via `echo` on post updates, instantly triggering Vite HMR, clearing Astro's `getStaticPaths` cache, and bypassing the WP REST API cache.
- Removed invalid `export const prerender = false` directive from `services.astro` to fix static build errors.

**New Dependencies:**
- None

**Core Files Modified:**
- Astro: `src/lib/cache.ts` (New), `src/lib/wordpress.ts`, `src/pages/services.astro`
- WP Plugin: `website/wordpress-plugins/e3es-headless-helper/e3es-headless-helper.php`
