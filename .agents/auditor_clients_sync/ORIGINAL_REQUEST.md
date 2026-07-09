## 2026-07-08T15:38:37Z

You are teamwork_preview_auditor. Your working directory is `/Users/bryanpaul/Local Sites/astro-e3es/.agents/auditor_clients_sync`.
Your parent is parent-orchestrator, conversation ID `6d4384e9-7ded-42ec-8e6f-b2ddf91f270d`.

Your mission is to perform a forensic integrity audit on the changes made to the codebase and WordPress database on branch `task/clients-sync-2026-07-08` for the clients list and individual client pages.

## Tasks:
1. Perform systematic checks (static code analysis, checking of test script assets, database changes audit log, etc.).
2. Verify that there is no hardcoding of expected test results or bypasses, and all data sync and layout changes are genuine.
3. Check that the script files used (like `run_migration.cjs` or `restructure_legacy.php`) perform actual programmatic mapping and migrations.
4. Produce a detailed forensic audit report `audit_report.md` in your working directory. The report must contain a clear binary verdict: CLEAN or INTEGRITY VIOLATION.
5. Once complete, write `handoff.md` and notify parent.
