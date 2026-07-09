# Architecture Notes

## Map Component Animations
- **CSS Staggering**: Texas map SVG elements animate their regions (.texas-region) sequentially. The CSS resides globally in `src/styles/mobile.scss` to allow map blocks to trigger correctly regardless of whether they are rendered dynamically by WordPress or statically in Astro.
- **Scroll Reveal**: Instead of executing unconditionally on DOM load, a global `IntersectionObserver` within `src/layouts/Layout.astro` triggers animations (`.is-visible`) when the SVG map enters the viewport. This is more resilient for maps placed lower down on the page (like K-12).

## Design-Build Column Constraints
- **1200px Grid Bounds**: Consistent with user layout restraints, standard Design-Build columns layout constraints are set to a max-width of `1200px` and centered on the page.
- **Editor Synchronization**: SCSS files (`src/styles/mobile.scss` and `src/styles/desktop.scss`) configure these limits for both the frontend rendering and Gutenberg editor styling overrides. This prevents layout differences between the block editor view and the frontend.

## Breadcrumb Data Resolution
- **Taxonomy Precedence**: When determining a client's industry for breadcrumb trails, native WordPress `industry` taxonomy terms take precedence over legacy ACF meta fields (`_e3_client_industry`). If both are missing, it defaults to K-12.

## South Texas Page Layout Overrides
- **Custom Sidebar & Testimonials**: To support asymmetrical sidebar layouts with avatars and testimonials, we created a `.bill-sidebar` column container class that adds a left green border and padding on desktop, and stacks gracefully on mobile. Avatars inside testimonials are styled using `.full-width-testimony__avatar-wrap` and `.full-width-testimony__avatar` BEM styles rather than inline styling.
- **Cooperative Logos Grid**: Added the `.coop-logos` flex grid system to display purchasing cooperatives logos (BuyBoard/TIPS) cleanly inside section columns without inline styles.
- **Columns Inside db-feature Overrides**: Core Gutenberg columns placed inside a `.db-feature` block are automatically styled on desktop to match the standard skewed feature layout alignment (`align-items: flex-start`, `gap: 6rem`), avoiding the need to code custom container wrappers in Gutenberg.

## Client Listing Visibility & Pagination
- **Custom Visibility Toggle**: We register the `_e3_client_show_in_index` meta key (boolean) on the `clients` custom post type with `show_in_rest => true` to expose it in standard REST API calls. A Gutenberg `ToggleControl` in the editor sidebar UI enables post editors to toggle it in WordPress.
- **REST API Pagination**: Since there are 105 clients total, the standard `/clients?per_page=100` queries truncate the list. We updated `getClients()` in `src/lib/wordpress.ts` to paginated page fetches, merging page 1 and page 2 to retrieve all 105 posts before filtering in Astro.
- **Astro Listing Filtering & Sorting**: The `/clients` page (`src/pages/clients.astro`) dynamically filters the retrieved clients array by `!!client.meta?._e3_client_show_in_index` before mapping, ensuring that the results grid, Texas SVG map dots, and region sidebar filters only display the selected clients. It then sorts the mapped clients array alphabetically using the standard JS `sort` method and `localeCompare` on the client names.

## Our Team Directory Block
- **Grid Layout**: On mobile, `.team-directory__grid` renders as a single-column flex list. On tablet, it transitions to a 2-column grid. On desktop, it scales to a 4-column grid with a maximum container width of `1440px`.
- **Interactive States**: Hovering on team cards triggers a `scale(1.03)` zoom and transition of photos from grayscale to full color, without causing any physical translate/movement of the card (to avoid indicating it is clickable). Focused active keyboard navigation states trigger a prominent `--color-primary-green` focus ring outline. Sharp corners (`border-radius: 0;`) and soft depth box-shadows (`0 8px 24px rgba(0, 0, 0, 0.08)`) are enforced on cards and photos.

## TORCH Page Re-creation
- **Gutenberg Block Construction**: Created a custom seeder script `seed-torch.php` that programmatically builds the TORCH page. Standard Gutenberg `wp:columns` and `wp:column` block types define a 25/75 split-screen layout. Custom HTML blocks (`wp:html`) wrap the specific sidebar and main content areas to enforce BEM class naming conventions. To prevent WordPress from adding random paragraph or line break elements (`wpautop`) inside these custom blocks, the HTML contents are stripped of newlines and extra spaces.
- **BEM Styling Overrides**:
  - `torch-layout` aligns the columns structure. On mobile, it flex-stacks the sections; on desktop, it spans a responsive, sticky sidebar alongside a flexible main content column.
  - `torch-sidebar` renders the marketing links with SVG file/globe icons and contact details using brand-compliant backgrounds, font weights, and border accents.
  - `torch-main` arranges headings, embedded Vimeo video study frames, logos, and services bullet points. Interactive anchors feature 44px touch targets and distinct focus rings.
- **WordPress Slashing & Block Integrity**:
  - **Slashing Protocol**: All custom PHP code that updates `post_content` via `wp_update_post()` must wrap the content string in `wp_slash()` to ensure backslashes in serialized JSON block comments (like `\u0026`) are preserved. Otherwise, WordPress's internal `wp_unslash()` will strip the backslash and convert it to literal `u0026`, causing Gutenberg "Attempt Block Recovery" validation failures.
- **FAQ Section Keywords Isolation**:
  - The custom Gutenberg block `e3es/faq-section` isolates the dynamic rendering of keyword tags to keep standard list views clean. Keywords rendering is deactivated in both JavaScript (`editor-blocks.js`) and PHP (`e3es-headless-helper.php`).

## Clients Page Architecture
- **Static Breadcrumb Items**: The `/clients` page is a static template, so breadcrumbs are defined using a static `breadcrumbItems` list containing the Home page hierarchy and a pointer to the current Clients directory.
- **Sharp Corner Constraint**: In accordance with E3 styling guidelines, all inline `border-radius` styles on the clients finder elements (the search sidebar, search filter selectors, search input fields, client card containers, labels, and no results message container) are explicitly set to `0` to enforce sharp corners.

## Client Parity, Restructuring, and Flickr Image Import
- **Post Status Transition**: Transitioned all 80 draft client posts to "publish" status.
- **Index Parity and Meta Toggle**: Set `_e3_client_show_in_index` to `1` for the 100 migrated clients (to match the live site list, excluding `south-texas` and duplicate `gwh` posts) and `0` for others.
- **Project Structure Restoration**: Restored missing `wp:e3es/project-details` blocks inside `wp:e3es/project` wrappers for `donna-isd`, `carrizo-springs-cisd`, and `caldwell-isd` by parsing values from the live database JSON dump.
- **Flickr Media Import Automation**: Created `scratch/import_and_associate_images.cjs` to downscale and compress high-res Flickr images from `flickr_downloads/` to under 300KB using `sharp`, import them via WP-CLI, set the first image as the post's featured image, map subsequent images to multiple project blocks, and dynamically build a native WordPress Gallery block at the bottom of the client pages.
- **Client Detail Hero Banner Architecture**: Dynamically parses CPT taxonomies and metadata terms on the clients detail static paths page (`src/pages/clients/[slug].astro`), rendering a unified E3 brand centered hero banner. To ensure all 105 clients display a custom banner with a logo, a regex parser extracts logo URLs (`clientLogoUrl` or class `db-page-hero__logo-img` sources) and background images (`bgImageUrl` or `background-image` styles) directly from the raw database Gutenberg block comments. The parser then strips the `wp-block-e3es-intro-banner` element from the Gutenberg HTML payload dynamically to prevent duplicate banner rendering.
- **WordPress Gutenberg List Block Styles**: Registered `core/list` custom styles (`grid-2-col` and `grid-3-col`) inside `e3es_register_inline_arrow_button_styles` within `e3es-headless-helper.php`. The styling compiles to `editor-styles.css` using `sync-styles.js` to ensure visual parity inside the Gutenberg editor canvas iframe and frontend Astro build.
- **Unified SVG Map Rendering**: In `wordpress.ts`, `processWordPressHtml()` replaces static map image placeholders with `TEXAS_MAP_SVG`. The SVG contains inline stylesheet overrides to force all region paths (`.cls-1` through `.cls-8`) to use the brand green color. To align the star markers exactly with the reference map image (`Texas-Map---green-with-dark-stars.jpg`, dimensions `517x491`), we detected the center coordinates of all 64 stars in the JPEG space and mapped them to the `941.76 x 907.17` SVG viewBox space using a mathematically verified bounding box mapping formula based on the true Texas shape silhouette boundaries (JPEG: `X=[2, 515], Y=[3, 490]`; SVG: `X=[87, 867], Y=[89, 834]`). This places exactly 64 star polygons at the precise relative locations of the original map with zero deviation.
- **Client Detail Spacing & Project Eyebrow**: Spacing below the hero banner is handled conditionally to prevent white margin gaps when layout blocks with background colors are placed immediately below the banner. It targets `main > :first-child:not(.db-feature):not(.has-background):not(.wp-block-group):not(.wp-block-cover)` to apply `margin-top: 4rem !important` only to standard content elements (like paragraphs). Additionally, we implement dynamic project eyebrow hiding using the pure CSS selector `body:not(:has(.project-section ~ .project-section)) .project-section__eyebrow { display: none !important; }` to hide the "Project 1" label on client pages or mockups containing only a single project section.
- **Map Spill Overlay**: To make the SVG map overlap into adjacent sections by approximately 15-20%, `.db-feature--map-spill .db-feature__image-wrapper` applies a `transform: scale(1.6) !important` scaling factor and a physical height of `550px` on desktop viewports. On mobile, it adjusts to `scale(1.2)` and `height: 350px`.


## Wrangler Configuration & Deployment State
- **Wrangler State Directory (`.wrangler/`)**: The `.wrangler` state/cache directory is generated during local preview/deployment testing and must remain ignored by version control to prevent state corruption. This directory contains session/deployment caches and config overrides (e.g. `.wrangler/deploy/config.json`) which could override target worker paths (such as directing Wrangler to search for SSR paths like `dist/server/wrangler.json` instead of deploying the static `dist/` directory assets).


