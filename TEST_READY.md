# E3 Clients Parity E2E Test Suite

A standalone, Zero-Dependency Node.js testing harness to verify the clients listing and detail pages migration against the local Astro development server.

## Overview

The test script (`tests/clients-parity.test.js`) executes integration assertions against the active Astro development server (`http://localhost:4008` by default) to validate content parity, layout BEM specifications, video integrations, image migration, and proper project block hierarchies.

## How to Run

1. Ensure the Astro development server is running:
   ```bash
   npm run dev
   ```
   *(By default, this server should run on `http://localhost:4008/`)*

2. Run the test script using Node.js:
   ```bash
   node tests/clients-parity.test.js
   ```

3. (Optional) To run against a different port or host, specify the `ASTRO_URL` environment variable:
   ```bash
   ASTRO_URL=http://localhost:3000 node tests/clients-parity.test.js
   ```

The script will output detailed PASS/FAIL metrics for each validation item and exit with code `0` if all assertions pass, or code `1` if any fail.

## Feature Coverage Checklist

| Feature Verification | Test Assertion Details | Status |
| :--- | :--- | :--- |
| **Client List Count** | Confirms `/clients` renders exactly 100 client cards. | ⚠️ Fails (currently 26) |
| **Exclusions & Deduplication** | Verifies `South Texas & Coast` (`south-texas`) is excluded and duplicate `Goodall-Witcher Healthcare` (`gwh`) is removed. | ⚠️ Fails (`gwh` present) |
| **HTTP Status Codes** | Verifies each individual client subpage returns status `200`. | ✅ Passed for all active pages |
| **BEM Layout Validation** | Validates presence of BEM standard elements: `<main>`, `.breadcrumb-bar`, and `.db-page-hero` / `.wp-block-e3es-intro-banner`. | ✅ Passed |
| **Featured Image Migration** | Ensures no client page references the `taj-mahal-placeholder` image (in `<img>` tags or inline CSS styles). | ⚠️ Fails (found on `houston-community-college`, `cooke-county`) |
| **Video Integrations** | Verifies the Vimeo video iframe is present and matches the correct video URL/ID for specific clients (`granbury-isd`, `little-elm-isd`, `keene-isd`, `plano-isd`, `city-of-stockdale`, `boyd-isd`). | ✅ Passed |
| **Project Details Wrapping** | Asserts all project details (`.project-details`) are wrapped inside the custom project block (`.wp-block-e3es-project.project-section`). | ⚠️ Fails (found unwrapped on `rio-hondo-isd`) |
| **Relationship Description Ordering** | Verifies the custom project block is positioned *under* (after) the short relationship description paragraph. | ⚠️ Fails (order incorrect/inside project on multiple pages) |

## Test Script Implementation Details

- **Path**: `tests/clients-parity.test.js`
- **Dependency Scope**: Native JavaScript (ES Module) using Node.js standard libraries (`fetch`, `child_process`). Does not require third-party libraries (no `jsdom`, `cheerio` or test runners required).
- **Concurrency**: Integrates an asynchronous worker queue with a concurrency limit of `5` parallel page requests to prevent local server rate-limiting or socket exhaustion.
- **Reporting**: Full stdout logging using ANSI escape characters for color-coded test suites and structured bulleted lists of failures.
