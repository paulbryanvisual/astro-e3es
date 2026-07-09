# Original User Request

## 2026-07-08T15:04:23Z

# Teamwork Project Prompt — Final Draft

Update the styling on the Design-Build page (`http://localhost:4008/design-build`) so that the cards component (`design-build__pillars`) has a maximum width of 1200px, while its background container spans the full width of the screen.

Working directory: `/Users/bryanpaul/Local Sites/astro-e3es`
Integrity mode: development

## Requirements

### R1. Container and Background Layout
The background of the `design-build__pillars` section must span the full viewport width (edge-to-edge), while the inner container holding the actual cards must be constrained to a maximum width of 1200px and centered horizontally.

### R2. Styling Implementation
The styles should be implemented using the existing BEM SCSS architecture in the Astro project (likely in `mobile.scss` or `desktop.scss`). If this component exists as a Gutenberg block, ensure changes are synced to the editor via `node sync-styles.js`.

## Acceptance Criteria

### Layout Verification
- [ ] Programmatic/DOM inspection confirms the background wrapper's computed width matches the viewport width.
- [ ] Programmatic/DOM inspection confirms the `.design-build__pillars` card container's computed width does not exceed 1200px on large viewports.
- [ ] The cards container remains horizontally centered within the viewport.

## 2026-07-08T15:12:02Z

# Teamwork Project Prompt — Final Draft

Update the clients list on `http://localhost:4008/clients` to perfectly match the live site at `https://www.e3es.com/clients/` (specifically ensuring "South Texas & Coast" is removed). Furthermore, audit all individual client pages to ensure full content parity (including text, photographs, and videos) between the live site and the new headless Astro site.

The data source for the Astro frontend is the headless WordPress API. Agents must reference the project's documentation to fully understand the architecture and make any necessary additions or corrections directly within the WordPress database/content to trigger the correct Astro build output.

Working directory: `/Users/bryanpaul/Local Sites/astro-e3es`
Integrity mode: development

## Requirements

### R1. Client List Parity
The main `/clients` page on the local Astro site must contain the exact same list of clients as the live production site. Any discrepancies, such as "South Texas & Coast", must be identified and removed via the WordPress backend/API data.

### R2. Individual Page Content Parity
Every individual client page on the local site must contain all the content present on its live counterpart. This includes exact text blocks, embedded photographs, and embedded videos. Content must be added to the WordPress source data if it is currently missing on the Astro frontend.

### R3. Architectural Alignment
All changes must adhere strictly to the established headless WordPress-to-Astro architecture documented in the repository.

## Acceptance Criteria

### Verification
- [ ] Programmatic or agent-judge verification confirms the list of client names on `http://localhost:4008/clients` exactly matches `https://www.e3es.com/clients/`.
- [ ] For each client, an agent-judge confirms that the images and videos present on the live site's client page are fully represented and rendering correctly on the local site's corresponding client page.
- [ ] Verification confirms that data changes were made at the WordPress source level, not hardcoded into Astro templates.

## Follow-up — 2026-07-08T15:14:51Z

The user has provided an additional requirement for the ongoing task: "use the E3 Project to display info about the projects under a short discrption of the client relationship". Please ensure the Orchestrator incorporates this design requirement when auditing and updating the individual client pages.

## 2026-07-08T15:24:42Z

# Teamwork Project Prompt — Final Draft

Fix the map graphic on the Funding page (`http://localhost:4008/funding`). The current map is blurry and constrained. It must be updated to use raw SVG code (or CSS) to make it resolution-independent, break out of its skewed container to overlap the top and bottom of the section by 10%, and should not be clipped by the parent container's styling.

Working directory: `/Users/bryanpaul/Local Sites/astro-e3es`
Integrity mode: development

## Requirements

### R1. SVG Implementation
Replace the existing low-resolution image/graphic with raw SVG code (or equivalent CSS) to ensure the map renders sharply and is completely resolution-independent.

### R2. Layout & Overlap (Z-Index / Positioning)
The map graphic must overlap the top and bottom edges of its parent section by approximately 10%. It must not be visually clipped or constrained by the slanted/skewed box behind it. This may require CSS positioning adjustments (e.g., negative margins, absolute positioning, and removing `overflow: hidden` or `clip-path` limits from the parent that affect the map).

### R3. Implementation
Changes should be made cleanly within the existing BEM SCSS architecture or Astro components. If the map is generated via a WordPress block, the team must ensure the solution is synced appropriately.

## Acceptance Criteria

### Verification
- [ ] Programmatic or agent-judge verification confirms the image source is now raw inline SVG (or CSS) rather than an `<img>` tag pointing to a rasterized file.
- [ ] DOM inspection confirms the map element's bounding box extends vertically beyond the computed height of its immediate parent container by roughly 10% on both the top and bottom.
- [ ] Visual/DOM verification confirms the map is not clipped by any `overflow: hidden` or `clip-path` properties from the skewed container.


## 2026-07-09T14:44:11Z

# Content Migration and Layout Enhancement

Migrate client page content from the live website https://www.e3es.com/clients/ to the local WordPress database, layout project blocks, attach media assets from downloaded Flickr folders, construct image galleries, publish to Astro, and verify layout aesthetics visually.

Working directory: /Users/bryanpaul/Local Sites/astro-e3es
Integrity mode: development

## Requirements

### R1. Live Client Content Extraction & Sync
Extract all content (text, projects, services, metrics) from the subpages of `https://www.e3es.com/clients/` and synchronize it to the local WordPress database (site `http://e3es2026.local/`) under the corresponding custom post type `clients`. Do not modify page templates, only add content.

### R2. Layout, Block Structure, and Spacing
Ensure the content is laid out beautifully using native Gutenberg blocks (e.g., wrappers with inner blocks). Specifically:
- The intro banner must be followed by the relationship/description paragraph.
- The project block(s) (`wp:e3es/project`) must sit below the relationship paragraph.
- The project details (`project-details`) must be properly nested inside the custom project block structure.
- Spacing and margins must look visually correct (no extra empty paragraphs or awkward spacing).

### R3. Project Featured & Additional Images
- If a client has only one project, the project block must use the featured image.
- If a client has multiple projects, one project block uses the featured image, and the other project blocks must use relevant images automatically selected from the corresponding `flickr_downloads` folders.

### R4. Bottom Photo Galleries
Look inside the `/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads` directory for folders matching the client. Upload all related photos to the WordPress media library and build a WordPress native gallery block at the bottom of each client's page.

### R5. E2E Test Suite and Regression Checks
Execute the existing E2E clients test suite at `/Users/bryanpaul/Local Sites/astro-e3es/tests/clients-parity.test.js` and resolve any errors. Specifically verify:
- No pages use the unmigrated "taj-mahal-placeholder" featured image.
- All 100 client subpages resolve with HTTP 200.
- All layout structure assertions in the test suite pass.

### R6. Visual Verification
Perform visual layout verification on the rendered pages using Chrome DevTools (via the `chrome-devtools` MCP server tools since we are on macOS). Verify that page margins, column alignments, and text flow are correct. Output a layout verification report with screenshots of the modified pages.

## Acceptance Criteria

### Content and Layout Parity
- [ ] All 100 client pages from `https://www.e3es.com/clients/` are successfully migrated to WordPress.
- [ ] The relationship paragraph is correctly positioned directly above the project block(s) on all client subpages.
- [ ] The `project-details` block is correctly nested inside the custom `wp:e3es/project` block structure (resolving the layout regression on pages like `rio-hondo-isd`).

### Image and Media Parity
- [ ] For clients with only 1 project, the project block uses the featured image.
- [ ] For clients with multiple projects, additional project blocks use unique relevant photos from the client's flickr folder.
- [ ] A native gallery block is present at the bottom of each client subpage containing all images related to that client found in the local `flickr_downloads` directory.
- [ ] No page uses the `taj-mahal-placeholder` image (resolving pages like `idea-public-schools`).

### Verification & Testing
- [ ] Running `node tests/clients-parity.test.js` from the Astro project directory outputs `PASS` for all checks and all 100 client subpages.
- [ ] Visual inspection screenshots are captured and saved, and a verification report is generated and placed in the conversation artifacts directory.

