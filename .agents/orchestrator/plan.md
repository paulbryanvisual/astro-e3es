# Project: Funding Page Map Graphic Fix

## Architecture
- Astro Frontend: Funding page located at `/funding`.
- SCSS: Styling utilizing BEM methodology, likely divided into mobile and desktop files.
- Gutenberg/WordPress: If the section/page is dynamic, syncing layout styles using `node sync-styles.js` might be required.
- Image Asset: The blurry map graphic (currently an `<img>` tag or background-image) to be replaced with raw inline SVG.

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|---|---|---|---|
| 1 | Exploration | Locate Funding page components, SCSS files, map assets, and plan CSS overlap/non-clipping strategy. | None | DONE (1a934946-f2a5-4fe0-b972-596022a12701) |
| 2 | Implementation | Replace the blurry image with inline SVG, implement SCSS positioning for 10% vertical overlap, remove parent clipping. | 1 | DONE (9ba79044-6977-4237-b9c4-f5494ed0c927) |
| 3 | Review | Verify SVG usage, 10% overlap on top/bottom, and absence of overflow/clip-path clipping. Run builds and tests. | 2 | DONE (acb56cc5-94e1-4b73-9b6e-813a4fe679c2) |
| 4 | Forensic Audit | Verify integrity, ensure no cheating/facades are used, and that changes are made cleanly at source. | 3 | IN_PROGRESS (efe9debb-e482-4a7c-b124-3a0fbed77063) |
| 5 | Integration | Merge task branch `task/funding-map-svg-20260708` into `main` using Phase 4 protocol. | 4 | PLANNED |

## Interface Contracts
- CSS classes used in Astro template: must match BEM conventions and not cause regression on other pages.
- Map wrapper and container boundaries.
