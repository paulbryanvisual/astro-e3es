## 2026-07-08T15:33:26Z
You are a specialized worker agent. Your mission is to implement the map graphic fix on the Funding page.

Your working directory is: /Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_funding_map_1
Please create this directory and your own progress.md/handoff.md files there.

## Task Details:

### Step 1: Replace blurry PNG map with inline SVG
Modify `src/lib/wordpress.ts` inside the `processWordPressHtml` function to detect the blurry map image tag:
`static-map-600x400.png`
And replace it with a clean inline SVG map of Texas. 
You can extract the Texas map SVG paths from `src/pages/clients.astro` (lines 90 to 150/200).
Please add 15-20 small client location dots (using SVG `<circle>` elements, styled with white fill and green stroke/borders) inside the SVG map at logical coordinates (e.g. Dallas, Houston, Austin, San Antonio, El Paso, Rio Grande Valley, etc.) to represent client locations.
Ensure the SVG has class `db-feature__image` so it inherits the correct page styles.

### Step 2: Implement SCSS overflow and positioning
Modify `src/styles/mobile.scss` (around line 1326 under `.db-feature--map-spill`) to ensure the map is not clipped and overflows by 10% on top and bottom.
Use the following SCSS rule:
```scss
.db-feature--map-spill {
    overflow: visible !important;
    z-index: 10;
    position: relative;

    .db-feature__image-wrapper {
        transform: scale(1.2) !important; // 1.2 scale creates roughly 10% overflow top & 10% bottom
        clip-path: none !important;        // Prevent clipping by skewed arrow clip-path
        overflow: visible !important;      // Allow image to overflow container
        z-index: 10;
        height: 400px;

        .db-feature__image {
            transform: none !important;    // Remove default counter-skew & zoom
            object-fit: contain !important;
            width: 100% !important;
            height: 100% !important;
        }
    }
}

@media (max-width: 768px) {
    .db-feature--map-spill {
        .db-feature__image-wrapper {
            height: 300px;
        }
    }
}
```

### Step 3: Zero-Delay Commit Rule
- You MUST instantly commit every single file modification to Git locally BEFORE pausing to ask for feedback or asking me to test it.
- Run `git status` to verify and commit with a clear, descriptive message (e.g. `feat: replace blurry funding map with inline SVG and fix clipping`).

### Step 4: Build & Verify
- Run `npm run build` to verify the Astro site builds successfully and the changes are incorporated into `dist/funding/index.html`.
- Run `node sync-styles.js` if necessary to sync styles to the WordPress editor.

Write your handoff report to /Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_funding_map_1/handoff.md and report back to parent.
