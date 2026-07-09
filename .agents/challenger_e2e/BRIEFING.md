# BRIEFING — 2026-07-08T10:30:50-05:00

## Mission
Design and write a comprehensive E2E test script (tests/clients-parity.test.js) to verify client list and detail page migration correctness on http://localhost:4008/.

## 🔒 My Identity
- Archetype: Empirical Challenger
- Roles: critic, specialist
- Working directory: /Users/bryanpaul/Local Sites/astro-e3es/.agents/challenger_e2e
- Original parent: parent-orchestrator, conversation ID 6d4384e9-7ded-42ec-8e6f-b2ddf91f270d
- Milestone: E2E testing
- Instance: 1 of 1

## 🔒 Key Constraints
- Review-only — do NOT modify implementation code (only write the tests and test config)
- Run verification code myself.
- Do NOT trust the worker's claims or logs.
- If you cannot reproduce a bug empirically, it does not count.
- Never write code to main or master branch directly.
- ZERO-DELAY COMMIT RULE: You MUST instantly commit every single file modification to Git locally BEFORE pausing to ask for feedback or asking me to test.

## Current Parent
- Conversation ID: 6d4384e9-7ded-42ec-8e6f-b2ddf91f270d
- Updated: not yet

## Review Scope
- **Files to review**: Astro project client list and client slug pages, layout, and block structure.
- **Interface contracts**: `/clients` client count, no `south-texas` or `gwh` duplicate, no placeholders, proper HTML structures and Vimeo iframes on specific slug pages.
- **Review criteria**: HTML layout structure, status codes, content parity.

## Loaded Skills
- None

## Attack Surface
- **Hypotheses tested**:
  - Astro listing contains 100 clients (FAIL: only 26 found).
  - Deduplication successfully excludes `south-texas` and duplicate `gwh` (FAIL: `gwh` is present).
  - Subpages return HTTP 200 and standard BEM markup (PASS).
  - All featured images migrated from Taj Mahal placeholder (FAIL: `houston-community-college` and `cooke-county` still use the placeholder).
  - Vimeo iframes map to the correct video IDs (PASS).
  - Project details are nested inside custom E3 Project blocks (FAIL: `rio-hondo-isd` is unwrapped).
  - Project sections are positioned under the relationship description paragraph (FAIL: multiple pages fail this ordering constraint).
- **Vulnerabilities found**:
  - Missing 74 clients on Astro dev server.
  - Duplicate `gwh` card rendered in client listing page.
  - Placeholder `taj-mahal-placeholder` image references found in HTML on multiple subpages.
  - Unwrapped details and bad block-ordering layout bugs found.
- **Untested angles**:
  - Dynamic page interactivity (e.g. SVG map clicking, filters, lightbox modal triggers) which requires full browser testing (out of scope for HTML content verification).

## Key Decisions Made
- Implement E2E script in `tests/clients-parity.test.js` using node-fetch and custom regex-based parsing to avoid external dependencies.
- Added concurrency controls (parallel queue limit of 5) to keep fetches efficient and avoid overwhelming the Astro server.

## Artifact Index
- `tests/clients-parity.test.js` — Standalone Node.js E2E test script.
- `TEST_READY.md` — Usage documentation and feature coverage checklist.
