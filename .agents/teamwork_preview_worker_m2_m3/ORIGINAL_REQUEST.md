## 2026-07-09T14:49:14Z

You are the implementation worker for E3 Client Migration & Layout Parity.
Your working directory is /Users/bryanpaul/Local Sites/astro-e3es/.agents/teamwork_preview_worker_m2_m3.

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A Forensic Auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Your objective is to complete Milestones 2, 3, and 4:
1. Transition all 80 draft client posts to "publish" status.
2. Ensure the list of clients on /clients matches the live site at https://www.e3es.com/clients/ exactly. Exclude "south-texas" (South Texas & Coast) and any duplicate "gwh" posts (ensure only one goodall-witcher-hospital post remains). Set post meta `_e3_client_show_in_index` to 1 for the 100 migrated clients, and 0 for others.
3. Download/extract real featured images to replace all references to "taj-mahal-placeholder" (in post content, meta, bgImageUrl, style background, etc.). Import them into the local WordPress media library and associate them correctly. You may adapt existing scripts like `apply_correct_featured.cjs` or `migrate_all_placeholders.cjs`.
4. Prepend a relationship description paragraph for all client pages (including newly published ones) preceding the project blocks. You may adapt and run `scratch/add_relationship_paragraphs.php`.
5. Wrap project details in the custom `wp:e3es/project` block structure for clients missing them (e.g. donna-isd, carrizo-springs-cisd, caldwell-isd, bryan-isd, goodall-witcher-hospital). You may adapt and run `restructure_legacy.php`.
6. For clients with multiple project blocks, ensure the first project block uses the featured image, and other project blocks use relevant images automatically selected from the corresponding `flickr_downloads` folders.
7. For each client, look inside the `/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads` directory for folders matching the client name. Upload all related photos to the WordPress media library and build a WordPress native gallery block at the bottom of the client's page. To avoid server bloat or memory crashes, downscale/compress large Flickr images (which can be over 10-20MB) to web-friendly sizes (e.g. under 300KB) before importing them to WordPress.
8. Update/resolve the E2E test suite at `tests/clients-parity.test.js` to verify all 100 client subpages (update the card count check from 25 to 100, and ensure all checks, including the layout and status checks, pass correctly).
9. Verify that Astro dev server is running, build the site using `npm run build` or run the E2E test command `node tests/clients-parity.test.js` and verify it outputs PASS.
10. Write your changes and handoff report to `/Users/bryanpaul/Local Sites/astro-e3es/.agents/teamwork_preview_worker_m2_m3/handoff.md`. Include the test outputs.
