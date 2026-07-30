# ⚙️ Environment & Config Setup + Sales Rep Region Selector Layout Audit

## 🚀 Section 1: Currently Working On
*(Environment & Config setup completed and moved to review)*

## 📋 Section 2: For Review
* 🛠️ **Task 0: Environment & LPM Setup** - Checked Git status on branch `task/rep-lock-and-1400px`. Registered LPM port `4008` (http://astro-e3es.localhost:8080) with Caddy active. Verified [package.json](file:///Users/bryanpaul/Local%20Sites/astro-e3es/package.json) dependencies (`astro` ^6.4.3, `sass` ^1.100.0) and dev script (`astro dev`).
* 📐 **Task 1: Max-Width 1400px Canvas Limit & Breakout Audit** - Evaluated SCSS rules across [desktop.scss](file:///Users/bryanpaul/Local%20Sites/astro-e3es/src/styles/desktop.scss#L639-L670) and [mobile.scss](file:///Users/bryanpaul/Local%20Sites/astro-e3es/src/styles/mobile.scss#L4780-L4815). Confirmed outer containers (`.map-container`, `.wp-block-columns`, `.has-background`, `.wp-block-group`) are targeted with CSS `:has()` pseudo-class to clamp inner selectors to `1400px` max-width with `margin: 0 auto`.
* ⚖️ **Task 2: Asymmetrical Grid vs 50/50 Split Audit** - Verified `1.35fr` map / `1fr` info card ratio in [SalesRepRegionSelector.astro](file:///Users/bryanpaul/Local%20Sites/astro-e3es/src/components/SalesRepRegionSelector.astro#L444) (`grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr)`) and `1.5` / `1` flex ratio in [TexasRegionSelector.astro](file:///Users/bryanpaul/Local%20Sites/astro-e3es/src/components/TexasRegionSelector.astro#L485-L492). Prevents 50/50 split-screen violations per design system rules.
* 📦 **Task 3: Custom Element Tag & Wrapper Class Rules** - Audited `<e3-sales-rep-selector>`, `<e3-texas-region-selector>`, `.wp-block-e3es-sales-rep-region`, `.sales-rep-selector`, and `.texas-region-selector-ui` in [SalesRepRegionSelector.astro](file:///Users/bryanpaul/Local%20Sites/astro-e3es/src/components/SalesRepRegionSelector.astro) and [TexasRegionSelector.astro](file:///Users/bryanpaul/Local%20Sites/astro-e3es/src/components/TexasRegionSelector.astro). All 5 target selectors are synchronized under the `1400px` canvas limit.

## ✅ Section 3: Approved
*(No items approved yet)*
