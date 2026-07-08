# Analysis of Design-Build Styling Updates

## 1. Locate Styling for `.design-build__pillars` in Source SCSS
The `.design-build__pillars` and `.design-build` selectors are defined in the following files and locations:
- **`src/styles/mobile.scss`**:
  - **Lines 2301-2307**: Mobile padding layout.
    ```scss
    .design-build,
    .design-build__pillars,
    .design-build__pillars,
    .wp-block-cover:has(.wp-block-e3es-design-build-advantage),
    .wp-block-cover:has(.design-build__grid) {
        padding: 3rem 1.5rem;
    }
    ```
  - **Lines 2873-2886**: Inner container constraint (width, max-width, margins) for Gutenberg blocks matching mockup.
    ```scss
    .design-build,
    .design-build__pillars,
    .wp-block-cover:has(.wp-block-e3es-design-build-advantage),
    .wp-block-cover:has(.design-build__grid) {
      .wp-block-cover__inner-container,
      .wp-block-group__inner-container {
        width: 100% !important;
        max-width: 1200px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
      }
    ```
  - **Lines 3891-3899**: Gutenberg editor mobile styling override for padding.
    ```scss
    .design-build,
    .design-build__pillars,
    .wp-block-cover.design-build,
    .wp-block-group.design-build {
      padding-top: 6rem !important;
      padding-bottom: 6rem !important;
      padding-left: 2rem !important;
      padding-right: 2rem !important;
    ```
- **`src/styles/desktop.scss`**:
  - **Lines 170-195**: Gutenberg editor desktop overrides for `.design-build` and `.design-build__pillars` columns.
    ```scss
    .design-build,
    .design-build__pillars {
      .wp-block-columns,
      .wp-block-columns > .block-editor-inner-blocks > .block-editor-block-list__layout {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 2rem !important;
        width: 100% !important;
        max-width: none !important;
        margin-top: 4rem !important;
        margin-bottom: 0 !important;
      }
    ```
  - **Lines 432-437**: Frontend desktop column direction override.
    ```scss
    .design-build,
    .design-build__pillars {
      .wp-block-columns {
        flex-direction: row;
      }
    }
    ```

---

## 2. HTML Component Structure
Inspecting `dist/design-build/index.html` (rendered page) shows that `.design-build__pillars` is a native Gutenberg Group Block, not an Astro component:
```html
<div class="wp-block-group design-build__pillars has-background is-layout-flow wp-block-group-is-layout-flow" style="background-color:#F4F6F8;padding-top:5rem;padding-right:2rem;padding-bottom:5rem;padding-left:2rem">
  <div class="wp-block-columns is-layout-flex wp-container-core-columns-is-layout-794e3cfa wp-block-columns-is-layout-flex">
    <div class="wp-block-column is-layout-flow wp-block-column-is-layout-flow">
      <div class="wp-block-group design-build__pillar has-background is-layout-flow wp-block-group-is-layout-flow" style="border-top-color:#7DA044;border-top-width:4px;background-color:#ffffff">
        <h3 class="wp-block-heading has-text-color" style="color:#7DA044">A Collaborative Workflow</h3>
        <p class="wp-block-paragraph">...</p>
      </div>
    </div>
    ...
  </div>
</div>
```
### Analysis:
- Since modern Gutenberg renders groups without a `.wp-block-group__inner-container` wrapper in some settings, the existing rule matching `.wp-block-group__inner-container` fails to apply to `.design-build__pillars`' inner content.
- The columns container (`.wp-block-columns`) is a direct child of the `.design-build__pillars` container.

---

## 3. Recommended SCSS Modifications
To constrain the inner content (cards columns container) to a max-width of 1200px and horizontally center it while keeping the outer background block full-width:

### Mod A: Frontend styles (`src/styles/mobile.scss`)
Add `> .wp-block-columns` to the inner container styling list so it targets the direct column child of `.design-build__pillars` and `.design-build` blocks:
```scss
/* Styling native Gutenberg Blocks inside .design-build to match mockup (Mobile First) */
.design-build,
.design-build__pillars,
.wp-block-cover:has(.wp-block-e3es-design-build-advantage),
.wp-block-cover:has(.design-build__grid) {
  .wp-block-cover__inner-container,
  .wp-block-group__inner-container,
  > .wp-block-columns { // <-- ADDED: Target direct columns child
    width: 100% !important;
    max-width: 1200px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding-left: 1.5rem !important;
    padding-right: 1.5rem !important;
  }
```

### Mod B: Editor Mobile override (`src/styles/mobile.scss`)
Update the editor columns block under `.design-build` and `.design-build__pillars` to ensure width constraint is visual inside the editor canvas:
```scss
  // Enforce mockup padding on Design-Build sections inside editor
  .design-build,
  .design-build__pillars,
  .wp-block-cover.design-build,
  .wp-block-group.design-build {
    padding-top: 6rem !important;
    padding-bottom: 6rem !important;
    padding-left: 2rem !important;
    padding-right: 2rem !important;

    // Design-Build Columns mobile layout overrides (fallbacks for core columns block)
    .wp-block-columns,
    .wp-block-columns > .block-editor-inner-blocks > .block-editor-block-list__layout {
      flex-direction: column !important;
      gap: 2rem !important;
      width: 100% !important;        // <-- ADDED: Width constraints
      max-width: 1200px !important;  // <-- ADDED: Max width limits
      margin-left: auto !important;  // <-- ADDED: Centering
      margin-right: auto !important; // <-- ADDED: Centering
    }
```

### Mod C: Editor Desktop override (`src/styles/desktop.scss`)
Change `max-width: none !important` to `max-width: 1200px !important` and center the editor block columns layout:
```scss
    // Desktop layout for Design-Build Columns in editor (fallbacks for core columns block)
    .design-build,
    .design-build__pillars {
      .wp-block-columns,
      .wp-block-columns > .block-editor-inner-blocks > .block-editor-block-list__layout {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 2rem !important;
        width: 100% !important;
        max-width: 1200px !important; // <-- MODIFIED: Constrain from 'none' to '1200px'
        margin-left: auto !important;  // <-- ADDED: Centering
        margin-right: auto !important; // <-- ADDED: Centering
        margin-top: 4rem !important;
        margin-bottom: 0 !important;
      }
```

---

## 4. Style Synchronization Process
The styles are compiled and synchronized using the `sync-styles.js` script in the root directory:
- Running `node sync-styles.js` compiles the Sass source from `src/styles/global.scss` to CSS.
- It copies/writes the compiled CSS to the local WordPress environment plugin folder at:
  `/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/editor-styles.css`
- This ensures the block editor backend replicates the front-end styles.
