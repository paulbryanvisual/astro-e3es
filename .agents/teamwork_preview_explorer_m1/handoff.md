# Handoff Report: E3 Case Study Client Migration & Layout Parity (Milestone 1)

## 1. Observation

Direct observations and evidence collected during exploration:

- **E2E Test Configuration**: The test suite file at `tests/clients-parity.test.js` sets the default URL to `http://localhost:4008` (line 16) and queries 25 client cards from `/clients` (lines 158-164).
- **Accidental Test Pass due to Regex Bug**: In `tests/clients-parity.test.js`, lines 317-349 query a relationship paragraph and fall back to the first matching paragraph if not found. The fallback regex is:
  ```javascript
  const clientNameRegex = new RegExp(`<p[^>]*>([\\s\\S]*?${clientKeyword}[\\s\\S]*?)<\/p>`, 'i');
  ```
  This matches across multiple tags and lines. Node.js evaluation on `boyd-isd` verified that this regex matched `<p>K-12 Schools | Central Texas</p>...` inside the intro banner because the word `Boyd` appeared in a later video block title. This resulted in an accidental PASS for pages that actually lack a relationship paragraph.
- **Local WordPress Client Post Count**: Running WP-CLI inside the local WordPress directory `/Users/bryanpaul/Local Sites/e3es2026/app/public` yielded the following:
  - Total client posts: 105
  - Published clients with `_e3_client_show_in_index` set to `1` (appearing on `/clients` listing): Exactly 25 posts.
  - Draft clients (with `_e3_client_show_in_index` set to `0` or missing): 80 posts.
- **Flickr Sizing**: Running `ls -lh` on Flickr images such as `/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads/Boyd ISD - before/Boyd - before-06843.jpg_54364088220.jpg` showed a file size of 7.0MB. In the local WordPress uploads directory, the imported file size was 119KB, proving that uploads are heavily compressed and downscaled compared to raw Flickr files (some of which exceed 21MB).
- **Astro Rendering Method**: In `src/pages/clients/[slug].astro`, lines 35 and 145 show that Astro does not parse individual Gutenberg blocks into frontend components:
  ```astro
  const optimizedContent = processWordPressHtml(client.content.rendered);
  ...
  <main style="background: var(--color-bg-white); padding: 0;">
    <Fragment set:html={optimizedContent} />
  </main>
  ```
  It serves raw Gutenberg HTML output from WordPress, which is pre-processed by `processWordPressHtml` in `src/lib/wordpress.ts`.

---

## 2. Logic Chain

- **Premise 1**: The E2E test suite evaluates subpages by parsing client card links from `/clients` (lines 194-197).
- **Premise 2**: The `/clients` page only lists posts where `client.meta?._e3_client_show_in_index` is true (line 9 of `src/pages/clients.astro`).
- **Premise 3**: Only 25 local clients have `_e3_client_show_in_index` set to `1`. The remaining 80 clients are in draft status and do not appear.
- **Deduction 1**: Therefore, the E2E test suite currently only tests the 25 published/migrated clients. The test passes successfully (Passed Suites: 1/1) because the remaining 80 draft/placeholder clients are skipped during testing.
- **Premise 4**: Specific clients like `little-elm-isd`, `keene-isd`, `plano-isd`, and `city-of-stockdale` are expected to have Vimeo videos (lines 19-26 of `tests/clients-parity.test.js`) and must eventually be published.
- **Premise 5**: These pages currently lack relationship paragraphs in the database (verified via `wp post get <id> --field=post_content` showing no paragraphs preceding the project block).
- **Deduction 2**: If these pages are published and tested, they will fail Check 5 of the test suite (unless the buggy test regex continues to mask the failure).

---

## 3. Caveats

- We assumed that `e3es2026.local` is the correct local development domain and that port 4008 is mapped correctly by Caddy. We verified this by successfully running the test suite against `http://localhost:4008` (which proxy-routes to Astro's dev server).
- We did not execute any modifying migrations or run database-altering commands, as doing so would violate the read-only exploration constraints. 
- Some clients (like `Little Elm ISD` and `Plano ISD`) do not have folders in the Flickr archive, so their images must be retrieved from the live site via scraping or manual import.

---

## 4. Conclusion

To complete the client page content migration and layout enhancement task, the following actions must be taken by the implementation agent:

1. **Publish and Sync Draft Clients**: Transition the 80 local drafts to "publish" status. Run the existing scraping and image migration scripts (`apply_correct_featured.cjs` or `migrate_all_placeholders.cjs`) to download featured images and replace `taj-mahal-placeholder.png` background URLs inside post content.
2. **Prepend Relationship Paragraphs**: Execute the PHP script at `scratch/add_relationship_paragraphs.php` inside WordPress using `wp eval-file scratch/add_relationship_paragraphs.php`. This will insert relationship descriptions before the custom project block (`e3es/project`) for all clients lacking them.
3. **Wrap Layout Structure for Exceptions**: Update the layout structure for the 6 published clients currently missing custom project blocks (`donna-isd`, `carrizo-springs-cisd`, `caldwell-isd`, `bryan-isd`, `goodall-witcher-hospital`, and duplicate `gwh` which should be trashed).
4. **Optimize Flickr Images**: High-resolution image files from `/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads` must be compressed and resized to web-friendly sizes (under 300KB) before being uploaded to WordPress to prevent performance degradations.

---

## 5. Verification Method

To independently verify the architecture and state:

1. Ensure the Astro dev server is running on the local system (port 4008) and that the local WordPress site is active.
2. Run the E2E test suite from the project root:
   ```bash
   ASTRO_URL=http://localhost:4008 node tests/clients-parity.test.js
   ```
3. Check the local WordPress post contents and database meta using:
   ```bash
   "/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" "/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-cli.phar" post get 3873 --field=post_content --path="/Users/bryanpaul/Local Sites/e3es2026/app/public"
   ```
4. If any client pages return layout or placeholder errors, verify if they are on the index list using `_e3_client_show_in_index`.
