## 2026-07-08T15:16:45Z

You are teamwork_preview_explorer. Your working directory is `/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit`.
Your parent is parent-orchestrator, conversation ID `6d4384e9-7ded-42ec-8e6f-b2ddf91f270d`.

Your mission is to perform a detailed comparison audit between the live site at `https://www.e3es.com/clients/` (including all subpages) and the local headless Astro site.

## Task Details:
1. Identify all clients published on the live site `https://www.e3es.com/clients/` (including pagination, like `/page/2/`).
2. List the local WordPress client posts using WP-CLI. 
   - PHP binary: `/Applications/Local.app/Contents/Resources/extraResources/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php`
   - WP-CLI phar: `/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar`
   - Path: `/Users/bryanpaul/Local Sites/e3es2026/app/public`
3. Compare the local client posts with the live site client pages:
   - Identify missing clients.
   - Identify extra clients (like "South Texas & Coast", which must be removed).
   - Audit individual pages: verify featured images, inline images, and videos (Vimeo/YouTube iframes) on the live site, and check if they are missing locally or have placeholders (like the Taj Mahal placeholder).
   - Check the structure of client pages: the user wants the custom `E3 Project` (`e3es/project`) Gutenberg block to display project details under a short description of the client relationship. Note down which local posts need structure updates.
4. Produce a detailed gap analysis report `analysis.md` in your working directory. Ensure it lists every client and details any differences in content (text, image, video) or layout structure.
5. Once complete, write `handoff.md` and notify parent.

Note: Since you are in CODE_ONLY network mode, check if you can run a node script locally to scrape/fetch from the live site, or if the chrome-devtools server can navigate to the live site. If both are blocked, check if there is existing scraped data or mapping files in the repository (e.g. `featured_image_mapping.json`, `still_placeholder.json`) and use them.
