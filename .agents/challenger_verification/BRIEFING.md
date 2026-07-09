# BRIEFING — 2026-07-08T15:38:32Z

## Mission
Run the automated E2E test suite tests/clients-parity.test.js against the Astro dev server and verify 100% checks pass.

## 🔒 My Identity
- Archetype: challenger_verification
- Roles: critic, specialist
- Working directory: /Users/bryanpaul/Local Sites/astro-e3es/.agents/challenger_verification
- Original parent: 6d4384e9-7ded-42ec-8e6f-b2ddf91f270d
- Milestone: Verification of client parity tests
- Instance: 1 of 1

## 🔒 Key Constraints
- Localhost Port Management Rule must be strictly followed (use lpm.sh)
- macOS Browser Automation: use chrome-devtools MCP server if doing browser testing, screenshot capture, or DOM inspections.
- Do not autonomously spin up browser unless explicitly requested or needed for visual confirmation.
- Do not modify implementation code directly unless it's to write tests/debug verification scripts. We are a challenger / critic.

## Current Parent
- Conversation ID: 6d4384e9-7ded-42ec-8e6f-b2ddf91f270d
- Updated: not yet

## Review Scope
- **Files to review**: `tests/clients-parity.test.js`
- **Interface contracts**: `PROJECT.md` if exists
- **Review criteria**: correctness, client parity, video verification, no duplicate gwh, no placeholders, BEM correctness.

## Key Decisions Made
- Create initial BRIEFING.md and planning steps.

## Artifact Index
- `/Users/bryanpaul/Local Sites/astro-e3es/.agents/challenger_verification/verification_report.md` — Detailed test and verification report.
- `/Users/bryanpaul/Local Sites/astro-e3es/.agents/challenger_verification/handoff.md` — Five-component handoff report.
