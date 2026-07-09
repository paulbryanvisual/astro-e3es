# Current State

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



