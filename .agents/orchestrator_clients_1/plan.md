# Plan: Clients Parity & Content Audit

## Objective
1. Remove "South Texas & Coast" from the client list.
2. Align the local clients list (`http://localhost:4008/clients`) with the live site (`https://www.e3es.com/clients/`).
3. Audit all individual client pages and ensure full content parity (text, images, videos) between local and live site.
4. Structure the client pages to use the `E3 Project` (`e3es/project`) Gutenberg block to display project details under a short description of the client relationship.
5. Adhere to strict branch management, macOS browser automation rule, and other guidelines.

## Phase 1: Investigation & Audit (Milestone 1)
- **Task**: Compare the live clients list and individual client pages against local WordPress/Astro pages. Identify gaps (missing clients, missing/incorrect images, missing videos, structure mismatches).
- **Worker**: Spawn `teamwork_preview_explorer_audit` (Explorer).
- **Output**: An audit report documenting all discrepancies and a mapping of actions required (add, update, delete) for each client.

## Phase 2: E2E and Unit Test Suite Creation (Milestone 2 - E2E Track)
- **Task**: Create an E2E test suite to verify:
  1. Local clients list matches live clients list (specifically checking the absence of "South Texas & Coast").
  2. For each client, individual pages render successfully and match the live site content structure (including images, videos, and project blocks).
  3. No hardcoding in Astro (all data fetched dynamically from WordPress).
- **Worker**: Spawn `teamwork_preview_challenger_e2e` (Challenger) or E2E worker.
- **Output**: Automated test scripts and `TEST_READY.md`.

## Phase 3: Content Sync & WP Database Updates (Milestone 3 - Implementation Track)
- **Task**:
  1. Delete/trash the "South Texas & Coast" post.
  2. Implement updates to local WordPress client posts: update featured images, add missing images/videos to post content.
  3. Restructure post content to use `e3es/project` Gutenberg blocks to display project details under the short relationship description.
- **Worker**: Spawn `teamwork_preview_worker_sync` (Worker) with domain skills.
- **Output**: Updated WordPress database, verified build output.

## Phase 4: Verification & Hardening (Milestone 4)
- **Task**: Run E2E tests, review code and editor rendering, ensure all checklist items are ticked off.
- **Worker**: Spawn `teamwork_preview_reviewer` (Reviewer) and `teamwork_preview_auditor` (Auditor).
- **Output**: Verified, clean run, and final report.
