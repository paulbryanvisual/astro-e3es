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
- **Grid Layout**: On mobile, `.team-directory__grid` renders as a single-column flex list. On tablet, it transitions to a 2-column grid. On desktop, it scales to a 12-column grid with a maximum container width of `1440px`.
- **Asymmetric Desktop Grid**: To prevent repetitive layout patterns, card sizes span different column widths based on their `:nth-child` index (e.g. Leadership cards at the start span `6` columns, other rows alternate spans like `3`, `5`, `7`, and `8` columns, and overflow/new cards default to `4` columns).
- **Interactive States**: Hovering on team cards triggers a `scale(1.03)` zoom and transition of photos from grayscale to full color. Focused active keyboard navigation states trigger a prominent `--color-primary-green` focus ring outline. Sharp corners (`border-radius: 0;`) and soft depth box-shadows (`0 8px 24px rgba(0, 0, 0, 0.08)`) are enforced on cards and photos.

## TORCH Page Re-creation
- **Gutenberg Block Construction**: Created a custom seeder script `seed-torch.php` that programmatically builds the TORCH page. Standard Gutenberg `wp:columns` and `wp:column` block types define a 25/75 split-screen layout. Custom HTML blocks (`wp:html`) wrap the specific sidebar and main content areas to enforce BEM class naming conventions. To prevent WordPress from adding random paragraph or line break elements (`wpautop`) inside these custom blocks, the HTML contents are stripped of newlines and extra spaces.
- **BEM Styling Overrides**:
  - `torch-layout` aligns the columns structure. On mobile, it flex-stacks the sections; on desktop, it spans a responsive, sticky sidebar alongside a flexible main content column.
  - `torch-sidebar` renders the marketing links with SVG file/globe icons and contact details using brand-compliant backgrounds, font weights, and border accents.
  - `torch-main` arranges headings, embedded Vimeo video study frames, logos, and services bullet points. Interactive anchors feature 44px touch targets and distinct focus rings.
