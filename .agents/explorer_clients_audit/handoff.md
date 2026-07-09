# Handoff Report: Clients Parity & Content Audit

## 1. Observation
- **Local WordPress Client Post Statuses**: Exported using `wp post list` and verified in `local_wp_details.json`:
  * Total client posts: 107.
  * Published locally: 27 posts (including `south-texas` and duplicate `gwh`).
  * Drafts locally: 80 posts.
- **Duplicate Healthcare Client Posts**: 
  * `gwh` (ID: 3809) has status `publish` and uses featured image `http://e3es2026.local/wp-content/uploads/2026/06/gwh-hero-ghw-crane.jpg` but lacks the `e3es/project` Gutenberg block in its content.
  * `goodall-witcher-hospital` (ID: 1459) has status `publish` and uses featured image `http://e3es2026.local/wp-content/uploads/2026/06/taj-mahal-placeholder.png` and has the `e3es/project` Gutenberg block in its content, but has no introductory description.
- **Missing Project Blocks**: 
  * 6 published local posts do not contain the `e3es/project` block: `south-texas`, `gwh`, `bryan-isd`, `caldwell-isd`, `carrizo-springs-cisd`, and `donna-isd`.
- **Assets and Placeholders**:
  * 80 local posts are using the Taj Mahal placeholder (`taj-mahal-placeholder.png`).
  * 6 posts have embedded Vimeo video frames (`little-elm-isd`, `keene-isd`, `plano-isd`, `city-of-stockdale`, `granbury-isd`, `boyd-isd`).
- **Extra Client**: `south-texas` (title "South Texas & Coast", ID 6122) is published locally but not present in the live client dump.

---

## 2. Logic Chain
- **Status Gaps**: Cross-referencing `clients_dump.json` (live data dump) and `local_wp_details.json` shows that 76 client posts are drafts locally but should be published. One post (`e3-general`) is in the dump but is not a real client page, and thus doesn't need publishing.
- **Extra and Duplicate Removal**:
  * The user requested the removal of "South Texas & Coast", which corresponds to local slug `south-texas`.
  * The live site uses the slug `goodall-witcher-hospital`, while local has both `goodall-witcher-hospital` and `gwh`. Therefore, `gwh` is a local duplicate and should be removed.
- **Structure Update Action**:
  * The user wants client pages structured with the `E3 Project` (`e3es/project`) block displaying project details under a short relationship description.
  * Since `goodall-witcher-hospital` has the project block but lacks the relationship description, and `gwh` has the relationship description but lacks the project block, their contents must be merged.
  * Legacy clients (`bryan-isd`, `caldwell-isd`, `carrizo-springs-cisd`, `donna-isd`) are missing the project block entirely, and need their content restructured to wrap their details in the custom project block.
- **Image and Media Updates**:
  * 80 posts currently use the Taj Mahal placeholder image. Using `featured_image_mapping.json` (25 mappings), some can be restored, but the rest will need real images migrated.
  * 6 pages contain Vimeo videos which should be preserved in their content blocks.

---

## 3. Caveats
- Direct HTTP requests to the live site were not executed because the system operates in CODE_ONLY network mode.
- Audit relies on `clients_dump.json` as the source of truth for the live client list, and `featured_image_mapping.json` and `still_placeholder.json` for image asset parity.

---

## 4. Conclusion
- The local headless Astro / WordPress environment requires:
  1. Trashing `south-texas` and duplicate `gwh` posts.
  2. Publishing 76 draft client posts.
  3. Merging description text from `gwh` into `goodall-witcher-hospital` and updating its featured image.
  4. Restructuring the contents of legacy client posts (`bryan-isd`, `caldwell-isd`, `carrizo-springs-cisd`, `donna-isd`) to wrap their project specifications in the custom `e3es/project` Gutenberg block.
  5. Migrating featured images to replace Taj Mahal placeholders where mappings exist.
- A detailed gap analysis report has been written to `/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/analysis.md`.

---

## 5. Verification Method
- Inspect the gap analysis report: `cat "/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/analysis.md"`
- Run `node compare_clients.js` and `node analyze_structures.js` in `/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/` to verify comparison statistics.
- Verify client posts in local WordPress using WP-CLI:
  `wp post list --post_type=clients`
