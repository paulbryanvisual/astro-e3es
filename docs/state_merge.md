## 2026-07-07: Global Typography & Angled Block Exclusions

**Architectural Decisions:**
- Implemented global typography overrides in `mobile.scss` using specific `:not()` exclusions (`:not(:is(.wp-block-e3es-two-column, .wp-block-e3es-two-column-cover, .db-feature) *)`) to ensure Gutenberg styles apply to standard content universally across all pages, while preserving the custom layouts of angled feature blocks.
- Added standard Gutenberg layout wrappers to Astro's `clients/[slug].astro` and `blog/[slug].astro` templates to enforce consistent spacing and typography hierarchy across dynamic routes.
- Adjusted padding variables in `mobile.scss` for `.db-page-hero` to increase visual whitespace per the user's manual commits.

**Dependencies Added:**
- None.

**Core Files Modified:**
- `src/styles/mobile.scss`
- `src/pages/[...slug].astro`
- `src/pages/clients/[slug].astro`
- `src/pages/blog/[slug].astro`

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

## 2026-07-07T22:36:00Z - Comparison Table Layout & Background Spacing Fixes
**Architectural Decisions:**
- Addressed an issue where Gutenberg Block Editor CSS overrides for the `.comparison-table` leaked into the frontend Astro template, causing tables to collapse horizontally due to `display: flex`. Scoped the editor styles safely within `.editor-styles-wrapper`.
- Added `.wp-block-e3es-comparison-table` to the CSS Grid breakout items in `mobile.scss` so its background stretches across the full viewport, while keeping its internal content constrained to a 1200px max-width wrapper.
- Introduced a global SCSS adjacent sibling utility using `:is()` pseudo-selectors on `.services-page__content` to automatically collapse `margin-top: 0` between adjacent full-width background blocks (like Comparison Tables and FAQ Sections), eliminating unintended white space gaps.
- Handled exclusion rules for headings/paragraphs within two-column components.

**New Dependencies:**
- None

**Core Files Modified:**
- `src/styles/mobile.scss`

## 2026-07-07: Services Page Hybrid SSR Migration
**Architectural Decisions:**
- Transitioned the `/services/` and `/services/[slug]` routes to Hybrid Server-Side Rendering (SSR) via Cloudflare Workers to provide immediate frontend updates without requiring full site rebuilds.
- Kept the project default output as `static`, selectively opting in dynamic routes with `export const prerender = false;`.
- Implemented `stale-while-revalidate` Edge caching (`Cache-Control: public, max-age=0, s-maxage=60, stale-while-revalidate=31536000`) for the services pages to guarantee instant load times with background WP synchronization.
- Rewrote the main `services.astro` template to dynamically pull parent (root) level WP service CPT items and their respective featured images, excluding irrelevant ones like `k-12`.

**Dependencies Added:**
- `@astrojs/cloudflare` (Astro Cloudflare adapter)

**Core Files Modified:**
- `astro.config.mjs`: Added cloudflare adapter and tested output mode configs.
- `src/pages/services.astro`: Built dynamic grid pulling from `getServices()`, configured `prerender = false`, and added edge caching.
- `src/pages/[...slug].astro`: Disabled `getStaticPaths()` pre-rendering for SSR compatibility; dynamically queries `Astro.params.slug` directly on the server to retrieve WordPress pages and services.
- `src/pages/clients.astro`: Configured `prerender = false` and edge caching.

## 2026-07-07: Clean Breadcrumb Labels
**Architectural Decisions:**
- Added a `cleanLabel` helper in `src/lib/wordpress.ts` to automatically strip the words "Solutions" and "Services" from the end of breadcrumb labels as they are built in Astro.
- This allows keeping the WordPress titles intact while displaying cleaner names (e.g., "Decarbonization, Solar & Storage") in the breadcrumbs menu.

**Dependencies Added:**
- None.

**Core Files Modified:**
- `src/lib/wordpress.ts`

---
### Timestamp: 2026-07-07T21:42:41-05:00
**Architectural Decisions & Actions Taken:**
- Restored missing `Breadcrumb` component and `buildBreadcrumbs` logic in `src/pages/[...slug].astro` and `src/lib/wordpress.ts` after they were accidentally removed in a recent template refactor.
- Modified `buildBreadcrumbs` to accurately render child pages in the dropdown, rather than siblings, aligning the breadcrumbs with the site hierarchy.
- The user independently introduced a top-level `Services` breadcrumb injection if the current path root is a service.

**New Dependencies:**
- None.

**Core Files Modified:**
- `src/pages/[...slug].astro`
- `src/lib/wordpress.ts`

## Session Wrapup: 2026-07-08T02:57:25Z

**Architectural Decisions**:
- Migrated `.breadcrumb-bar__dropdown` and `.header__dropdown-menu` CSS definitions in `mobile.scss` to use dynamic `width: max-content` bounded by `max-width: 450px` and `min-width: 280px` instead of a fixed 220px block. This ensures dropdowns expand to the right without collapsing lines.

**New Dependencies**:
- None.

**Core Files Modified**:
- `src/styles/mobile.scss`


## Session Wrapup: 2026-07-08T03:08:07Z - Fix Broken Astro Image Links

**Architectural Decisions**:
- Configured Astro's built-in image optimization `remotePatterns` to support the `http` protocol in addition to `https`. This allows Astro to successfully proxy and optimize images fetched from the local HTTP WordPress instance (e3es2026.local) without returning 500 Internal Server Error.

**New Dependencies**:
- None.

**Core Files Modified**:
- `astro.config.mjs`

---
### Timestamp: 2026-07-08T03:12:00Z
**Architectural Decisions**:
- Integrated the WordPress homepage node ("E3 Homepage") into the root breadcrumb "Home" instead of displaying it as a duplicate link, preserving its subpages dropdown menu at the root breadcrumb level.
- Cleaned and normalized all relative URL outputs inside the breadcrumbs list and dropdown links by automatically stripping `/home/` and `/home/industries/` path prefixes to match Astro's clean routing.

**New Dependencies**:
- None.

**Core Files Modified**:
- `src/lib/wordpress.ts`
- `src/pages/[...slug].astro`
- `src/lib/cache.ts`

## Timestamp: 2026-07-08T03:23:00Z
### Thread Summary: Services Parent Page Layout Updates
- **Architectural Decisions**: 
  - Dynamic generation of `wp:e3es/two-column` blocks for services parent pages based on their children pages.
  - Integration of standard Gutenberg buttons inside the `db-feature__content` column to seamlessly link back to children pages.
  - Refined layout styles: enforcing 1.5rem bottom margin for the added buttons on mobile/desktop, moving frontend desktop media queries to `desktop.scss` per project rules.
- **Core Files Modified**:
  - WordPress Database (`wp_posts`): Services post contents were recursively updated using custom scripts (`update_services.php` and `update_services_buttons.php`).
  - `src/styles/mobile.scss`: Adjusted spacing (` margin-top: 1.5rem`) and integrated manual user style updates (e.g. `.design-build__pillars`).
  - `src/styles/desktop.scss`: Added `.wp-block-columns` layout fallback for `.design-build`.
- **Dependencies Added**: None.

## Timestamp: 2026-07-08T03:30:00Z
### Thread Summary: Button Injection and WP Process HTML
- **Architectural Decisions**:
  - Injected standard native Gutenberg `wp:buttons` directly into the backend WordPress post content (`wp:e3es/two-column` blocks) instead of using Astro frontend hacks, obeying the backend data accuracy rule.
  - Used dynamically generated, accessible button text (e.g., "Explore Indoor Air Quality") based on the section heading instead of generic "Learn More" labels.
  - Renamed the image optimization function in Astro to `processWordPressHtml` and implemented double-escaped HTML entity (`&amp;amp;`) cleanup inside it.
- **New Dependencies**: None.
- **Core Files Modified**:
  - `src/lib/wordpress.ts` (Renamed `optimizeHtmlImages` -> `processWordPressHtml`, added entity fixes)
  - `src/pages/[...slug].astro`, `src/pages/clients/[slug].astro`, `src/pages/index.astro`, `src/lib/cache.ts` (Updated to use new function name)
  - `wp_posts` database natively via one-shot PHP script.

## Timestamp: 2026-07-08T03:35:00Z
### Thread Summary: Mask Animation and Full Width Blocks
- **Architectural Decisions**:
  - Implemented dynamic script toggling of display properties for mask blocks (`.project-section__mask--left`/`right`) to support the "Green Texture Behind" variation without breaking standard layouts.
  - Tweaked image scaling to `scale(1.15)` for the texture-behind style in SCSS to prevent white borders from bleeding on specific resolutions.
  - Added `.wp-block-e3es-core-pillars` and `.db-pillars` natively to CSS grid full-bleed layout rules, and applied the sibling element top-margin collapse reset.
- **New Dependencies**: None.
- **Core Files Modified**:
  - `src/pages/clients/[slug].astro`
  - `src/styles/mobile.scss`
