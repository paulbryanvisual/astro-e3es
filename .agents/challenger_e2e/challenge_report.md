# ADVERSARIAL REVIEW & CHALLENGE REPORT — 2026-07-08

## Challenge Summary

**Overall risk assessment**: HIGH

The E2E verification test suite (`tests/clients-parity.test.js`) executed successfully but identified critical vulnerabilities and layout defects. The migration of clients is far from complete, presenting significant parity gaps between the current Astro build and the target site specifications.

---

## Challenges

### [Critical] Client Database Sync Parity Failure
- **Assumption challenged**: The local WordPress backend and Astro dev server serve the full synced database of 100 clients.
- **Attack scenario**: Querying `/clients` for count and extracting cards.
- **Blast radius**: 74% of client case studies are missing. Visitors attempting to view those pages will receive 404 errors or see empty search results.
- **Mitigation**: Run the WordPress sync/migration pipelines (e.g. `migrate_all_placeholders.cjs`) to sync the remaining 74 client entries.

### [High] Deduplication Exclusions Failure
- **Assumption challenged**: Duplicate or legacy region clients (like `gwh` and `south-texas`) are excluded or deduplicated during import or rendering.
- **Attack scenario**: Extracting client card anchors on `/clients`.
- **Blast radius**: The duplicate card for Goodall-Witcher Healthcare (`gwh`) is still rendered alongside `goodall-witcher-hospital`, creating a duplicate UI entry.
- **Mitigation**: Update the query filtering in `src/pages/clients.astro` or the sync script to explicitly drop client posts where `slug === 'gwh'`.

### [High] Unmigrated Placeholder Media
- **Assumption challenged**: All pages have had their featured images migrated and do not contain placeholder references.
- **Attack scenario**: Text-search of HTML content for the substring `taj-mahal-placeholder`.
- **Blast radius**: Pages like `houston-community-college` and `cooke-county` still serve the default placeholder image `taj-mahal-placeholder.png` in their banners.
- **Mitigation**: Run the media migration utilities (`migrate_images.js` and `apply_correct_featured.cjs`) for the affected client IDs.

### [Medium] Unwrapped Project Details Layout Defect
- **Assumption challenged**: The Gutenberg block parser correctly wraps all `.project-details` elements inside parent `.wp-block-e3es-project.project-section` containers.
- **Attack scenario**: Nested tag structure validation.
- **Blast radius**: On `/clients/rio-hondo-isd`, the `.project-details` element is rendered as a root sibling of `.wp-block-e3es-project` instead of inside it. This breaks the SCSS styling structure, causing alignment and padding problems on mobile.
- **Mitigation**: Re-save the post layout in Gutenberg using the correct block hierarchy, or update the Astro parser to automatically wrap orphan project-details nodes.

### [Medium] Block Ordering Layout Defect
- **Assumption challenged**: Project details and block elements are rendered in correct reading order below the relationship description paragraph.
- **Attack scenario**: Index comparisons of `<p>` descriptions and `.wp-block-e3es-project` containers.
- **Blast radius**: On almost all client pages (e.g. `granbury-isd`, `boyd-isd`, `royal-isd`, `raymondville-isd`), the project block index is smaller than the relationship description paragraph index (meaning the description paragraph is inside or below the project details instead of above it).
- **Mitigation**: Adjust the Gutenberg block ordering to ensure the introduction/relationship paragraph is the first block in the editor content, placed directly after the intro banner block and ahead of the project blocks.

---

## Stress Test Results

- **Client Card Count Check** → Expected 100 cards → Actual: 26 cards → **FAIL**
- **Deduplication Check** → Exclude `south-texas` & `gwh` → Actual: `gwh` found → **FAIL**
- **Subpage Status Check** → Verify all active pages return HTTP 200 → Actual: 200 OK → **PASS**
- **BEM Layout Structure Check** → Verify presence of standard classes → Actual: Found → **PASS**
- **Placeholder Check** → Scan for `taj-mahal-placeholder` → Actual: Found on 2 pages → **FAIL**
- **Video Embed Integrity Check** → Verify Vimeo iframe and video IDs → Actual: Match → **PASS**
- **Project Details Wrapper Check** → Confirm wrapping inside project-section → Actual: Unwrapped on `rio-hondo-isd` → **FAIL**
- **Ordering Check** → Confirm paragraph is above project block → Actual: Multiple pages fail → **FAIL**
