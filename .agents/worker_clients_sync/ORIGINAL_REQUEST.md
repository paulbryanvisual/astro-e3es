## 2026-07-08T15:25:22Z
You are teamwork_preview_worker. Your working directory is `/Users/bryanpaul/Local Sites/astro-e3es/.agents/worker_clients_sync`.
Your parent is parent-orchestrator, conversation ID `6d4384e9-7ded-42ec-8e6f-b2ddf91f270d`.

Your mission is to execute the database and content sync updates for the clients list and individual client pages on the local headless Astro/WordPress site.

## MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A Forensic Auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Task Details:
1. Verify you are on Git branch `task/clients-sync-2026-07-08`.
2. Trash the local WordPress client `south-texas` (ID 6122) and duplicate `gwh` (ID 3809) using WP-CLI.
   - PHP binary: `/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php`
   - WP-CLI phar: `/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar`
   - WP path: `/Users/bryanpaul/Local Sites/e3es2026/app/public`
3. Merge `gwh` content into `goodall-witcher-hospital` (ID 1459):
   - Set the featured image of `goodall-witcher-hospital` to the image from `gwh` (`http://e3es2026.local/wp-content/uploads/2026/06/gwh-hero-ghw-crane.jpg`).
   - Prepend the relationship description paragraph from `gwh` (i.e. "Goodall-Witcher Healthcare is a long-standing E3 partner...") right after the intro banner (before the `wp:e3es/project` block).
4. Restructure legacy client posts `bryan-isd` (ID 13), `caldwell-isd` (ID 14), `carrizo-springs-cisd` (ID 15), and `donna-isd` (ID 16) in WordPress:
   - For `bryan-isd`, wrap its project details inside a custom `wp:e3es/project` Gutenberg block matching BEM HTML structure under the relationship paragraph. Project details:
     - Market: K-12
     - Project Scope: LED, HVAC, Indoor Air Quality
     - Contract Amount: $6,421,852
     - Annual Savings: $763,908
     - Content paragraphs: detailed list of upgrades and LoanSTAR info.
   - For `caldwell-isd`, `carrizo-springs-cisd`, and `donna-isd`, fetch their project details/blocks from `clients_dump.json` and combine them, placing the project block under the relationship paragraph.
5. Publish all 76 draft client posts in WordPress using WP-CLI:
   - You can do this by running `wp post update` on all client posts with draft status.
6. Fix paths and execute `migrate_all_placeholders.cjs` (or write a modified version) to scrape the live site for all remaining client pages, download their images, upload them to local WordPress, and replace placeholders in post contents. Make sure to use the correct PHP and WP-CLI paths.
7. Run the Astro build command `npm run build` in `/Users/bryanpaul/Local Sites/astro-e3es` to verify the build runs cleanly.
8. Once complete, write a detailed report of changes in `changes.md`, write `handoff.md` and notify parent.
