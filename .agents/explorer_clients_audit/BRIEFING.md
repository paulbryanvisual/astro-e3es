# BRIEFING — 2026-07-08T15:22:25Z

## Mission
Compare all clients on the live site https://www.e3es.com/clients/ with local WordPress posts and headless Astro codebase to identify missing, extra, or incorrectly formatted client profiles.

## 🔒 My Identity
- Archetype: teamwork_preview_explorer
- Roles: Teamwork explorer, auditor, read-only investigator
- Working directory: /Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit
- Original parent: parent-orchestrator (conversation ID 6d4384e9-7ded-42ec-8e6f-b2ddf91f270d)
- Milestone: explorer_clients_audit

## 🔒 Key Constraints
- Read-only investigation — do NOT implement or modify project code (only write to our agent directory)
- CODE_ONLY network mode. No direct external internet access, but check if local tools, curl, or chrome-devtools can access the web/network if configured or if local mapping files exist.
- Focus on content, media (featured/inline images, iframe videos), and gutenberg layout structure (e3es/project block).

## Current Parent
- Conversation ID: 6d4384e9-7ded-42ec-8e6f-b2ddf91f270d
- Updated: 2026-07-08T15:22:25Z

## Investigation State
- **Explored paths**:
  - `/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json` (Live dump representation)
  - `/Users/bryanpaul/Local Sites/astro-e3es/featured_image_mapping.json` (Real image mappings)
  - `/Users/bryanpaul/Local Sites/astro-e3es/still_placeholder.json` (Placeholder image mappings)
  - Local WordPress database via customized export script `get_local_wp_details.php` and target dumps.
- **Key findings**:
  - Out of 107 local client posts, 27 are published and 80 are drafts.
  - `south-texas` (South Texas & Coast) is an extra post that must be removed.
  - `gwh` (ID 3809) is a duplicate of `goodall-witcher-hospital` (ID 1459). `gwh` has correct images but lacks the project block; `goodall-witcher-hospital` has the project block but uses the Taj Mahal placeholder and lacks the relationship description.
  - 6 clients (`gwh`, `bryan-isd`, `caldwell-isd`, `carrizo-springs-cisd`, `donna-isd`, `south-texas`) are missing the `e3es/project` block completely.
  - 80 client posts use the Taj Mahal placeholder image.
  - 6 clients contain Vimeo video integrations (`little-elm-isd`, `keene-isd`, `plano-isd`, `city-of-stockdale`, `granbury-isd`, `boyd-isd`).
- **Unexplored areas**: None.

## Key Decisions Made
- Performed offline database query utilizing local PHP configuration to safely extract all posts, taxonomies, metadata, and Gutenberg block content.
- Cross-referenced all data sources to establish a complete 108-client map.
- Wrote analysis.md detailing every client post status and structure gap.

## Artifact Index
- /Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/analysis.md — Detailed gap analysis report.
- /Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/handoff.md — Handoff report for parent-orchestrator.
