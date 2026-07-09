# BRIEFING — 2026-07-09T09:45:00-05:00

## Mission
Migrate client page content from live e3es.com to local WordPress, correct layout structure for all client subpages, attach Flickr photos/galleries, and verify via E2E testing and visual audits.

## 🔒 My Identity
- Archetype: teamwork_preview_orchestrator
- Roles: orchestrator, user_liaison, human_reporter, successor
- Working directory: /Users/bryanpaul/Local Sites/astro-e3es/.agents/orchestrator_clients_migration
- Original parent: top-level
- Original parent conversation ID: 2bb8ba92-a0f4-4610-bbf5-517d17e9615c

## 🔒 My Workflow
- **Pattern**: Project Pattern
- **Scope document**: /Users/bryanpaul/Local Sites/astro-e3es/.agents/orchestrator_clients_migration/PROJECT.md
1. **Decompose**: We will decompose this into exploration, content migration setup/audit, implementation of WordPress block corrections and image attachments, running test suites, and performing visual checks.
2. **Dispatch & Execute**:
   - **Delegate (sub-orchestrator)**: We will decompose into milestones and delegate to subagents/sub-orchestrators.
3. **On failure**:
   - Retry: nudge stuck agent or re-send task
   - Replace: spawn fresh agent with partial progress
   - Skip: proceed without (only if non-critical)
   - Redistribute: split stuck agent's remaining work
   - Redesign: re-partition decomposition
   - Escalate: report to parent (sub-orchestrators only, last resort)
4. **Succession**: Self-succeed at 16 spawns. Write handoff.md, spawn successor.
- **Work items**:
  1. Explore and Analyze Codebase & Database [pending]
  2. Implement WordPress Content Migration & Flickr Gallery Upload [pending]
  3. Correct Block Structures and Layout (e.g. nested project-details, featured images) [pending]
  4. Run E2E Tests & Fix Any Layout Failures [pending]
  5. Perform Visual Verification and Generate Reports [pending]
- **Current phase**: 1
- **Current focus**: Explore and Analyze Codebase & Database

## 🔒 Key Constraints
- Never write or edit code directly as the orchestrator.
- Never run build/test commands directly.
- All file edits must be delegated to workers.
- Verify changes using the Forensic Auditor before completing a milestone iteration.
- Strict version control: isolate work in Git branches.

## Current Parent
- Conversation ID: 2bb8ba92-a0f4-4610-bbf5-517d17e9615c
- Updated: not yet

## Key Decisions Made
- [TBD]

## Team Roster
| Agent | Type | Work Item | Status | Conv ID |
|-------|------|-----------|--------|---------|
| M1 Auditor | teamwork_preview_explorer | Milestone 1 Audit | in-progress | 4f8e304f-08f3-4b27-8778-49ae7c8e107e |

## Succession Status
- Succession required: no
- Spawn count: 1 / 16
- Pending subagents: 4f8e304f-08f3-4b27-8778-49ae7c8e107e
- Predecessor: none
- Successor: not yet spawned

## Active Timers
- Heartbeat cron: task-13
- Safety timer: none

## Artifact Index
- /Users/bryanpaul/Local Sites/astro-e3es/.agents/orchestrator_clients_migration/BRIEFING.md — Coordination index
- /Users/bryanpaul/Local Sites/astro-e3es/.agents/orchestrator_clients_migration/progress.md — Heartbeat and status check
- /Users/bryanpaul/Local Sites/astro-e3es/.agents/orchestrator_clients_migration/PROJECT.md — Milestone decomposition and architecture
