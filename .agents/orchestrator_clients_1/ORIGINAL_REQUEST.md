# Original User Request

## 2026-07-08T15:12:02Z

Update the clients list on `http://localhost:4008/clients` to perfectly match the live site at `https://www.e3es.com/clients/` (specifically ensuring "South Texas & Coast" is removed). Furthermore, audit all individual client pages to ensure full content parity (including text, photographs, and videos) between the live site and the new headless Astro site.

The data source for the Astro frontend is the headless WordPress API. Agents must reference the project's documentation to fully understand the architecture and make any necessary additions or corrections directly within the WordPress database/content to trigger the correct Astro build output.

Working directory: `/Users/bryanpaul/Local Sites/astro-e3es`
Integrity mode: development

## Requirements

### R1. Client List Parity
The main `/clients` page on the local Astro site must contain the exact same list of clients as the live production site. Any discrepancies, such as "South Texas & Coast", must be identified and removed via the WordPress backend/API data.

### R2. Individual Page Content Parity
Every individual client page on the local site must contain all the content present on its live counterpart. This includes exact text blocks, embedded photographs, and embedded videos. Content must be added to the WordPress source data if it is currently missing on the Astro frontend.

### R3. Architectural Alignment
All changes must adhere strictly to the established headless WordPress-to-Astro architecture documented in the repository.

## Acceptance Criteria

### Verification
- [ ] Programmatic or agent-judge verification confirms the list of client names on `http://localhost:4008/clients` exactly matches `https://www.e3es.com/clients/`.
- [ ] For each client, an agent-judge confirms that the images and videos present on the live site's client page are fully represented and rendering correctly on the local site's corresponding client page.
- [ ] Verification confirms that data changes were made at the WordPress source level, not hardcoded into Astro templates.
