# Current State

- **GitHub Actions Cloudflare Workers Deployment Failures** (July 9, 2026):
  - **Issue**: The GitHub Actions deployment workflow run "Deploy Astro Site to Cloudflare Workers" was failing consistently.
  - **Cause**: The `.wrangler` state/cache directory was tracked by Git (missing from `.gitignore`) and had been committed. Inside `.wrangler/deploy/config.json`, the deploy config pointed to `dist/server/wrangler.json`, which does not exist because the Astro project is a purely static site (`output: 'static'`).
  - **Resolution**:
    1. Untracked the `.wrangler` directory from Git: `git rm -r --cached .wrangler`.
    2. Modified `.gitignore` to ignore `.wrangler/`.
    3. Committed the changes locally in `astro-e3es` on branch `fix/cloudflare-deploy-fail-1783614707` and merged to `main` following Phase 4.
    4. Updated `progress.MD` and synchronized both the website and Astro repositories.
    5. Verified the deployment on GitHub Actions; the workflow completed successfully (Run `29033873996`).
  - **Verification**: Verified that the GitHub Actions run succeeded and that `.wrangler` files are no longer tracked.
  - **Git Branch**: `fix/cloudflare-deploy-fail-1783614707` (in Astro workspace) and `fix/cloudflare-deploy-fail-1783614529` (in WordPress workspace).

- The Texas SVG map component has been updated to include a staggered scroll reveal animation on load/scroll, bringing it in line with the expected delay behavior from the original K-12 site.
- An `IntersectionObserver` was added globally in `src/layouts/Layout.astro` to detect when elements with `.texas-svg-map` enter the viewport and apply an `.is-visible` class.
- The staggered animations are handled entirely via CSS keyframes in `src/styles/mobile.scss` utilizing `nth-child` delay increments to animate `.texas-region` elements smoothly.
- Changes were safely pushed to `main` following the conflict-free branch merging protocol.
- **Design-Build Styling Update**: Implemented styling updates for the Design-Build page (`/design-build`).
  - Modified `src/styles/mobile.scss` to apply a maximum width of 1200px and centering to `.design-build` containers, including the direct `> .wp-block-columns` child.
  - Constrained block editor mobile columns overrides to 1200px max-width and centered them.
  - Modified `src/styles/desktop.scss` block editor columns overrides to set `max-width: 1200px !important` and centered them.
- **Breadcrumb Industry Fallback**: Fixed client page breadcrumb logic (`src/pages/clients/[slug].astro`) to check the native `industry` taxonomy terms before falling back to legacy meta fields or default K-12. This correctly assigns clients like Goodall-Witcher to Healthcare.
- **South Texas Layout Update**: Re-aligned the layout of the South Texas page (`/k12/south-texas`) to match the live page design.
  - Modified the seeder script `seed-south-texas.php` to wrap the Design+Build Advantage section in a group block styled as a grey `db-feature`, add a BEM avatar image container in the "Meet Bill" sidebar, and append the Superintendent testimonial.
  - Updated the Funding section in the seeder to include the cooperatives logos (TIPS and BuyBoard) and the Facilities Director testimonial with BEM avatar image styling.
  - Added `.bill-sidebar`, `.coop-logos`, and `.full-width-testimony` BEM avatar styles in `src/styles/mobile.scss` and responsive overrides in `src/styles/desktop.scss`.
  - Styled standard columns blocks inside `.db-feature` to align with the layout container on desktop.
  - Compiled and synced styles to the WordPress editor, and successfully ran the seeder script.
- **Client Featured Photos Parity**: Fixed and synchronized all client card featured images locally. Resolved the 2 remaining mismatches (`eagle-pass-isd` and `edgewood-isd`) by converting/fixing filenames and importing them using WP-CLI media import, achieving 100% featured image parity with the live production database dump.
- **Client Listing Visibility Custom Field**: Registered a new post meta field `_e3_client_show_in_index` in WordPress. Created a `ToggleControl` in Gutenberg to allow editors to toggle visibility on the clients search page. Seeded the field to only show the 25 target clients present on the live site. Updated Astro to fetch and merge all paginated clients (all 105) and dynamically filter the cards, map, and region filters based on this custom field.
- **Alphabetical Sorting**: Updated `src/pages/clients.astro` to sort the client cards array alphabetically by client name (`localeCompare`), ensuring they list alphabetically on the frontend.
- **Our Team Styling & Layout Fix**: Fixed the team directory page layout regression and applied E3 premium, modern design tokens:
  - Formatted the mobile view as a clean, single-column flex list, transitioning to a 2-column grid on tablet viewport size (`$breakpoint-sm`).
  - Implemented desktop overrides under a max width container limit of `1440px` with vertical spacing margins/padding of `80px 2rem`.
  - Defined a 12-column asymmetric grid layout on desktop matching the team member count indices.
  - Applied hover transitions: photos transition from grayscale to full color and scale up by `1.03`, and cards translate upwards with standard soft-depth shadow.
  - Set sharp corners (`border-radius: 0;`) on cards and images.
  - Added highly visible focus indicators utilizing a primary green outline focus ring.
  - Compiled the assets successfully with no compilation warnings or errors.
- **TORCH Page Re-creation**: Successfully re-created the live TORCH page at `http://localhost:4008/torch`.
  - Imported the official logo (`E3_TORCH_2024.png` - ID 6882) and the hero background photo (`Operation-room.jpg` - ID 6884) to local WordPress media.
  - Created the root page slug `torch` (ID 6883) and seeded its content using the new `seed-torch.php` helper seeder, structuring the Gutenberg layout with 25/75 split-screen columns, minified HTML blocks, and BEM class names.
  - Implemented responsive BEM stylesheet definitions in `src/styles/mobile.scss` and `src/styles/desktop.scss` specifying a sticky sidebar layout, custom brand colors, custom font scaling, and accessible 44px link touch targets.
  - Successfully verified building with `npm run build` and captured layout screenshot.
- **TORCH Sidebar Removal & Hero Spacing**:
  - Removed the sidebar from `/torch`, centering the main content in a 1200px container.
  - Added a default `margin-bottom: 4rem` to `.db-page-hero` in `mobile.scss` to ensure proper spacing when text follows the hero banner.
- **FAQ Keywords Removal**:
  - Removed `faq-section__keywords` tag container rendering from both the WordPress PHP block rendering (`e3_render_faq_section` in `e3es-headless-helper.php`) and the Gutenberg JavaScript block edit/save functions (`editor-blocks.js`) to keep FAQ sections clean.
- **WP Update Post Backslash Stripping Bug Fix**:
  - Fixed a bug where `wp_update_post` calls in `e3es-headless-helper.php` stripped backslashes from JSON comment attributes (e.g., converting `\u0026` to `u0026` literals), triggering Gutenberg "Attempt Block Recovery" validation errors.
  - Resolved this by wrapping `post_content` in `wp_slash()` for all `wp_update_post` calls.
  - Executed a batch migration (`sync_block_attrs.php`) using the Local PHP binary to automatically restore correct backslashed `\u0026` escaping in all 156 affected posts in the database.
- **Clients Page Improvements**:
  - Added breadcrumbs directly under the header / above the hero banner on the `/clients` page by importing and rendering the `<Breadcrumb>` component with a custom home dropdown path hierarchy.
  - Removed rounded corners (set `border-radius: 0`) from all boxes, containers, inputs, selection filters, tags/pills, and result wrappers on the clients page to guarantee sharp box corners.
- **Team Page Grid & Hover Layout Update**:
  - Changed the desktop grid layout in `src/styles/desktop.scss` for `.team-directory__grid` to be a 4-column grid instead of a 3-column grid.
  - Removed physical translation/movement (`transform: translateY(-4px)`) on hover of team member cards in `src/styles/mobile.scss` so they do not falsely indicate clickability.
  - Restructured the hover rules in `src/styles/mobile.scss` by nesting the `.team-directory__photo` zoom and grayscale-to-color transition directly inside the `&__card:hover` block. This ensures correct compilation of the photo hover transition and retains the default `filter: grayscale(100%)` and transition attributes on `&__photo`.
- **Our Story Page Video Block**:
  - Embedded the original Port Neches-Groves ISD case study Vimeo video block (`e3es/video-embed`) on the Our Story page (post ID 23) in WordPress using a custom PHP script.
  - Temporarily disabled KSES filters during database update via `kses_remove_filters()` to successfully retain the custom iframe code.
- **Client Index Page Restriction**:
  - Configured `_e3_client_show_in_index` custom fields in the WordPress database so that only the 25 user-specified clients are active on the `/clients` index page.
  - Set all other 80 clients to hidden (`0`) using a batch script mapping names, including mapping "GOODALL-WITCHER HEALTHCARE" to the local database title "Goodall Witcher Hospital" for exact matches.
- **Client Parity & Publishing**: Transitioned all 80 draft client posts to "publish" status. Configured `_e3_client_show_in_index` to show exactly the 25 selected clients on the frontend.
- **Project Details Restored**: Restored missing project details blocks for `donna-isd`, `carrizo-springs-cisd`, and `caldwell-isd` by parsing details from `clients_dump.json`.
- **Flickr Image Downscaling & Gallery Blocks**: Resized, compressed, and imported Flickr photos for all matched clients, ensuring all files are under 300KB. Associated featured images, mapped images to multiple project blocks, and appended a native WordPress gallery block to the bottom of the content.
- **Verified E2E Test Suite**: Ran E2E test suite checks for all 100 clients successfully (0 failures, 100% pass status).
- **Production Build Check**: Verified that the Astro static build compiles successfully with no errors or warnings.
- **Client Services Tagging & Detail Pages**: Added a dynamic Brand-styled header block to `src/pages/clients/[slug].astro` that extracts and displays Region, Industry, and Services Provided tags (HVAC, Lighting, Building Controls) cleanly at the top of client pages, mapped with fallback checks on post taxonomy terms and custom post metadata.
- **Project Details Grid List Block Style**: Registered `grid-2-col` and `grid-3-col` block styles for Gutenberg's core list block in the WordPress plugin `e3es-headless-helper.php`. Designed responsive SASS grid layouts for `.is-style-grid-2-col` and `.is-style-grid-3-col` in `mobile.scss` that scale down to a single column on mobile viewports. Applied the 2-column grid style block to the "The Comprehensive Project Included:" list block inside Boyd ISD (post ID 12).
- **Texas SVG Map Brand Colors & Stars**: Unified the regional map fills in `wordpress.ts` by setting all `.cls-1` through `.cls-8` styles in `TEXAS_MAP_SVG` to use the E3 primary brand green color (`#215734`). We mapped and updated the coordinates of exactly 64 star `<polygon>` shapes, matching the count and placement from the reference map image (`Texas-Map---green-with-dark-stars.jpg`) exactly.
- **Client Detail Page Hero Spacing**: Added a `margin-bottom: 4rem` margin to the `.client-hero` element in `src/pages/clients/[slug].astro`. This ensures that when a paragraph appears immediately after the hero banner, it has a consistent, professional vertical spacing (64px) and does not touch the banner.
- **Design-Build Spacing Gap**: Modified `src/styles/mobile.scss` to set `margin-bottom: 0 !important` unconditionally on `> .wp-block-e3es-intro-banner` elements within `.services-page__content`. This collapses the margin-bottom spacing gap between the top page hero and any following full-width background blocks (like the gray core pillars group) on the dynamic dynamic pages.
- **Desktop Project Hero Height Increase**: Updated the desktop height of `.project-section__hero` (under the `@media (min-width: 768px)` viewport breakpoint in `mobile.scss`) from `400px` to `500px` to increase visual prominence.
- **Client Detail Hero Banner Standardization**: Standardized the top of all client pages to render a unified E3 brand hero banner centered-layout matching Cooke County courthouse styling. Added metadata extraction regex to parse client logos (`clientLogoUrl` or `.db-page-hero__logo-img`) and background images (`bgImageUrl` or `background-image`) dynamically from the WordPress post content, falling back to CPT metadata and static maps. Removed duplicate `e3es/intro-banner` section blocks from the WordPress parsed HTML to prevent duplicate banner rendering.




