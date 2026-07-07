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
