## 2026-07-09T15:14:46Z
You are a visual verification worker. Your working directory is /Users/bryanpaul/Local Sites/astro-e3es/.agents/teamwork_preview_worker_visual_audit.

Your task is to:
1. Make sure the Astro dev server is running on port 4008.
2. Use the chrome-devtools MCP server tools to navigate to key modified pages:
   - http://localhost:4008/clients
   - http://localhost:4008/clients/donna-isd
   - http://localhost:4008/clients/carrizo-springs-cisd
   - http://localhost:4008/clients/caldwell-isd
   - http://localhost:4008/clients/plano-isd
   - http://localhost:4008/clients/boyd-isd
   - http://localhost:4008/clients/goodall-witcher-hospital
3. Capture a screenshot for each of these pages. Save the screenshots in the conversation brain/artifacts directory:
   `/Users/bryanpaul/.gemini/antigravity/brain/2bb8ba92-a0f4-4610-bbf5-517d17e9615c/`
   Use clear filenames like `clients_list.png`, `donna_isd.png`, etc.
4. Verify that:
   - Page margins are consistent.
   - Grid cards are well-aligned.
   - Text flow and relationship paragraphs render correctly.
   - The bottom gallery blocks render correctly (e.g. on Somerset ISD or others).
5. Generate a layout verification report named `layout_verification_report.md` in the brain/artifacts directory `/Users/bryanpaul/.gemini/antigravity/brain/2bb8ba92-a0f4-4610-bbf5-517d17e9615c/` with Markdown format, describing your visual analysis and embedding the screenshots.
6. Write a handoff report at `/Users/bryanpaul/Local Sites/astro-e3es/.agents/teamwork_preview_worker_visual_audit/handoff.md`.
