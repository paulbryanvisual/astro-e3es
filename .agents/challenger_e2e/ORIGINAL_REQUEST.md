## 2026-07-08T15:25:41Z
You are teamwork_preview_challenger. Your working directory is `/Users/bryanpaul/Local Sites/astro-e3es/.agents/challenger_e2e`.
Your parent is parent-orchestrator, conversation ID `6d4384e9-7ded-42ec-8e6f-b2ddf91f270d`.

Your mission is to design and write a comprehensive E2E test script (e.g., `tests/clients-parity.test.js`) that runs against the local Astro dev server (`http://localhost:4008/`) to verify:
1. The list of clients on `http://localhost:4008/clients` matches the live list of clients (100 clients) and does NOT contain "South Texas & Coast" (`south-texas`) or the duplicate Goodall-Witcher Healthcare (`gwh`).
2. For each client, its individual subpage at `http://localhost:4008/clients/[slug]` returns a 200 status code and renders the correct BEM HTML layout.
3. Every client page has its featured image migrated (i.e. no page uses `taj-mahal-placeholder.png` or `taj-mahal-placeholder` in any `<img>` tag or CSS style).
4. For client pages with videos (`granbury-isd`, `little-elm-isd`, `keene-isd`, `plano-isd`, `city-of-stockdale`, `boyd-isd`), verify that the Vimeo video iframe is present and rendering the correct video URL.
5. All project details are wrapped inside the custom E3 Project block structure (`wp-block-e3es-project project-section`) under the short relationship description paragraph.

Note: Since there is no test runner in package.json, you should implement the test script as a standalone Node.js script (e.g. using `fetch` or `jsdom` or `cheerio` if available, or regex-based HTML parsing if not) that can be run via `node tests/clients-parity.test.js`.
The test script must exit with code 0 if all tests pass, and code 1 if any test fails, printing detailed assertion failures.

Once complete:
1. Write the test script to `tests/clients-parity.test.js` in the project root.
2. Publish `TEST_READY.md` at the project root (`/Users/bryanpaul/Local Sites/astro-e3es/TEST_READY.md`) with a summary of the test script, how to run it, and the feature coverage checklist as specified in the Project Pattern.
3. Write `handoff.md` and notify parent.
