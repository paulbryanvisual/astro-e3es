# E3 Client Migration and Layout Parity Exploration Report

## 1. Executive Summary

This exploration report provides a comprehensive architectural audit of the E3 Case Study Client Migration and Layout Enhancement task. The audit covers the headless Astro frontend codebase, the local WordPress REST API and MySQL database, the Flickr image archives, and the E2E test suite. 

The investigation reveals that the E2E test suite currently passes because it only audits the 25 clients listed on the frontend `/clients` directory. However, a significant gap exists between the local database and the production site: out of 105 clients total, 80 remain in draft status, use placeholder images, or lack required layout structures. If these clients are published or added to the index, they will fail the E2E test suite due to missing assets, layout wrappers, or relationship paragraphs.

---

## 2. E2E Test Suite Analysis (tests/clients-parity.test.js)

The E2E test suite operates against the local Astro development server (default port 4008, matching the Localhost Port Manager configuration) and enforces five core checks:

1. **Check 1: Client Listing Page (/clients)**
   - Expects exactly 25 client cards on the list page.
   - Specifically requires the exclusion of South Texas & Coast (`south-texas`) and any duplicates of Goodall-Witcher Healthcare (`gwh`).
2. **Check 2: Subpage Status & Layout**
   - Each individual subpage route (`/clients/[slug]`) must return HTTP 200.
   - The HTML structure must contain a `<main>` tag, a `<div class="breadcrumb-bar">`, and a hero section containing either class `db-page-hero` or `wp-block-e3es-intro-banner`.
3. **Check 3: Featured Image Verification**
   - The HTML page output and inline styles must not contain any reference to `taj-mahal-placeholder` or `taj-mahal-placeholder.png`.
4. **Check 4: Vimeo Video Integration**
   - Specific client slugs must contain a Vimeo player iframe with exact IDs:
     - `granbury-isd` -> `227283498`
     - `little-elm-isd` -> `946653874`
     - `keene-isd` -> `1176712805`
     - `plano-isd` -> `1007829512`
     - `city-of-stockdale` -> `1171901749`
     - `boyd-isd` -> `1179578579`
5. **Check 5: Project Details Wrapping & Position**
   - If a page has project details (class `project-details`), they must be nested within the custom project block: class `wp-block-e3es-project` and `project-section`.
   - The project block must be positioned under a short relationship description paragraph (which contains words like "partnered", "partnership", "collaborated", "cooperated" or is mapped via fallback to the client slug name).

### Bug Identification in Test Suite Regex
A critical bug was discovered in the E2E test suite's relationship paragraph check. The regex uses a lazy matching group `[\s\S]*?` which can span across HTML tags:
```javascript
const clientNameRegex = new RegExp(`<p[^>]*>([\\s\\S]*?${clientKeyword}[\\s\\S]*?)<\/p>`, 'i');
```
Because `[\s\S]*?` matches newlines and tag boundaries, if the client name is found in a video block or header later in the document, the regex matches from the first `<p>` tag in the document (often the one inside the intro banner block) all the way down to the closing `</p>` in the video block. This results in an accidental pass for pages that do not actually contain a relationship paragraph before the project block (such as `boyd-isd` and `city-of-stockdale`).

---

## 3. Headless Astro Codebase Architecture

The Astro frontend acts as a static consumer of WordPress content:
- **Routing**: Client listings are handled by `src/pages/clients.astro`, and detail pages are routed dynamically via `src/pages/clients/[slug].astro` using `getStaticPaths()`.
- **Data Fetching**: The `src/lib/wordpress.ts` module fetches client data using the local REST API `http://e3es2026.local/wp-json/wp/v2/clients`.
- **Block Rendering**: Astro does not parse Gutenberg block nodes into Astro components. Instead, it renders the raw HTML output of blocks using `<Fragment set:html={optimizedContent} />` under `<main>`.
- **HTML Pre-processing**: In `src/lib/wordpress.ts`, `processWordPressHtml` manipulates the raw HTML to:
  - Replace static image maps with an inline SVG of Texas.
  - Rewrite relative `/wp-content/` and `/images/` URLs to absolute WP paths.
  - Fix double-escaped entities like `&amp;amp;`.
  - Add native lazy loading (`loading="lazy"`) and high fetch priority (`fetchpriority="high"`) to LCP images.
- **Styling**: Gutenberg block classes are styled using Scss under `src/styles/`:
  - `wp-block-gallery` utilizes a CSS grid layout (2 columns on mobile in `mobile.scss`, 4 columns on desktop in `desktop.scss`).

---

## 4. Local WordPress DB and REST API Analysis

The local WordPress instance (registered at `http://e3es2026.local`) has the following metadata schema and content status:
- **Total Client Posts**: 105 posts exist in the `clients` custom post type.
- **Index Flag**: Exactly 25 clients have the post meta `_e3_client_show_in_index` set to `1`. The other 80 clients have it set to `0`.
- **Drafts**: 80 client posts are currently saved in draft status.
- **Featured Image Meta**:
  - `_thumbnail_id`: Reference to attachment ID. Many draft clients still reference the `taj-mahal-placeholder.png` attachment ID.
- **Custom Post Meta**:
  - `_e3_client_logo`: Stores the logo URL.
  - `_e3_client_region`: Region slugs like `panhandle`, `central`, `north`, etc.
  - `_e3_client_services`: Serialized array of services.
  - `_e3_client_show_in_index`: Set to `1` (show) or `0` (hide).

---

## 5. Flickr Archives Analysis

The Flickr downloads folder is located at `/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads`:
- **Directory Layout**: Contains 91 subdirectories named after clients or project topics (e.g. `Banquete - before and after`, `Keene ISD, Sports Lighting - before_after`).
- **File Sizing & Bloat Risk**: Image files inside the Flickr downloads are raw high-resolution files ranging from 7MB to over 21MB each. Directly uploading these to WordPress will cause database bloat and severe performance problems.
- **Data Gap**: There are folders for several targets (`Keene ISD`, `Boyd ISD`, `City of Stockdale`), but some targets requested in the E2E test suite (like `Little Elm ISD` and `Plano ISD`) do not have directories in the Flickr archive. Their images must be scraped or imported from the live production site.

---

## 6. Migration and Alignment Script Index

The repository contains several scripts designed to assist with images, content wrapping, and metadata fixing:

1. **`migrate_images.js`**
   - Scrapes client images from live site `e3es.com` for clients not marked as `nonLegacyClients`, downloads them, imports them as attachments, sets them as featured images, and appends a `wp:gallery` block.
2. **`apply_correct_featured.cjs`**
   - Uses `featured_image_mapping.json` to download real hero images and update `_thumbnail_id` and post content image URLs for local clients.
3. **`migrate_all_placeholders.cjs`**
   - Scrapes grid and project detail pages on `e3es.com` to resolve placeholder images.
4. **`find_placeholders.cjs`**
   - Queries WP for all client posts using placeholders. (Contains a bug where it crashes on clients lacking the `_thumbnail_id` meta key).
5. **`scratch/fix_local_featured_photos.cjs`**
   - Node script to fix local featured photos by matching slug names and uploading files from local path to WordPress.
6. **`scratch/fix_video_heroes.php`**
   - PHP script that maps `keene-isd`, `plano-isd`, `little-elm-isd`, `city-of-stockdale`, and `texas-facilities-commission` to local images (like `Keene.png`), imports them as attachments, and replaces placeholders in post contents.
7. **`scratch/add_relationship_paragraphs.php`**
   - PHP script that loops through client posts, detects if there is no paragraph block before the custom project block, and automatically prepends a default relationship paragraph based on their industry classification (K-12, Healthcare, Municipal).

---

## 7. Discrepancy Gap Audit Table

The table below summarizes the current status and gaps of client pages:

| Client Slug | In Live Dump? | Local Status | Featured Image Status | Project Block? | Relationship Paragraph? | Has Video? | Action Required |
|-------------|---------------|--------------|-----------------------|----------------|-------------------------|------------|-----------------|
| `little-elm-isd` | No | draft | Placeholder | Yes | No (Fails E2E Regex) | Yes (946653874) | Publish, import `Little_Elm.png`, prepend relationship paragraph. |
| `keene-isd` | No | draft | Placeholder | Yes | No (Fails E2E Regex) | Yes (1176712805) | Publish, import `Keene.png`, prepend relationship paragraph. |
| `plano-isd` | No | draft | Placeholder | Yes | No (Fails E2E Regex) | Yes (1007829512) | Publish, import `Plano.png`, prepend relationship paragraph. |
| `city-of-stockdale` | No | draft | Placeholder | Yes | No (Fails E2E Regex) | Yes (1171901749) | Publish, import `Stockdale.png`, prepend relationship paragraph. |
| `boyd-isd` | No | publish | Mapped | Yes | No (Fails E2E Regex) | Yes (1179578579) | Prepend relationship paragraph. |
| `granbury-isd` | Yes | publish | Mapped | Yes | Yes | Yes (227283498) | None. |
| `donna-isd` | Yes | publish | Mapped | No | N/A | No | Wrap project details in custom block. |
| `carrizo-springs-cisd`| Yes | publish | Mapped | No | N/A | No | Wrap project details in custom block. |
| `caldwell-isd` | Yes | publish | Mapped | No | N/A | No | Wrap project details in custom block. |
| `bryan-isd` | No | publish | Mapped | No | N/A | No | Wrap project details in custom block. |
| `gwh` | No | publish | Mapped | No | N/A | No | Trash duplicate client page. |
| `south-texas` | Yes | publish | Mapped | No | N/A | No | Trash client page (exclusion list). |
| `goodall-witcher-hospital`| Yes | publish | Placeholder | Yes | No | No | Import `ghw-crane.jpg` as featured, copy paragraph from `gwh`. |
| 76 other drafts | Yes | draft | Placeholder | Yes | Yes (Default) | No | Publish, import images using `apply_correct_featured.cjs`. |
