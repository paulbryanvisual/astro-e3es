# BRIEFING — 2026-07-08T10:33:26-05:00

## Mission
Implement the map graphic fix on the Funding page by replacing blurry PNG map with inline SVG, implementing SCSS overflow and positioning, and verifying the changes.

## 🔒 My Identity
- Archetype: specialized worker agent
- Roles: implementer, qa, specialist
- Working directory: /Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_funding_map_1
- Original parent: d53947e6-4bb2-440f-b2a2-a2081c31a71d
- Milestone: Funding map graphic fix

## 🔒 Key Constraints
- Replace static-map-600x400.png with an inline SVG of Texas containing 15-20 small client location dots in src/lib/wordpress.ts
- Modify src/styles/mobile.scss to overflow and prevent clipping for .db-feature--map-spill
- Zero-Delay Commit Rule: Instantly commit every file modification locally before pausing/testing
- Run npm run build and verify dist/funding/index.html

## Current Parent
- Conversation ID: d53947e6-4bb2-440f-b2a2-a2081c31a71d
- Updated: not yet

## Task Summary
- **What to build**: Replace blurry map PNG with inline SVG map of Texas (with dots) on the Funding page and adjust SCSS styles to overflow 10% on top/bottom.
- **Success criteria**: Clean SVG is rendered on the funding page, map spills over top and bottom properly, and the project builds successfully.
- **Interface contracts**: None
- **Code layout**: src/lib/wordpress.ts and src/styles/mobile.scss

## Key Decisions Made
- Replaced the blurry map img tag with inline SVG before processing other images so that the SVG is not treated as a regular image.
- Placed 20 dots on the map corresponding to key Texas cities (Dallas, Houston, Austin, San Antonio, El Paso, Brownsville, etc.).

## Change Tracker
- **Files modified**:
  - `src/lib/wordpress.ts`: Replaced static-map-600x400.png img tag with TEXAS_MAP_SVG in `processWordPressHtml`.
  - `src/styles/mobile.scss`: Updated `.db-feature--map-spill` styles to implement overflow, positioning, transform scale, clip-path, and heights.
  - `src/lib/cache.ts`: Cache buster updated.
- **Build status**: pass
- **Pending issues**: none

## Quality Status
- **Build/test result**: pass (npm run build successful)
- **Lint status**: 0 violations
- **Tests added/modified**: none

## Loaded Skills
- None

## Artifact Index
- /Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_funding_map_1/handoff.md — Handoff report
- /Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_funding_map_1/progress.md — Progress tracker
