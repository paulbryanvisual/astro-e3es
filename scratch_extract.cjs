const fs = require('fs');

const legacyHtml = fs.readFileSync('/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/legacy-html/project-history.html', 'utf8');

// Extract the section
const sectionRegex = /<!-- Map & Finder Section -->[\s\S]*?(?=<!-- Floating Chat Icon -->)/;
const match = legacyHtml.match(sectionRegex);

if (match) {
    let content = match[0];
    
    // Replace hardcoded clientsData with Astro prop
    content = content.replace(/const clients = \[.*?\];/s, "const clients = window.__ASTRO_CLIENTS__ || [];");
    
    // Create the Astro component
    const astroComponent = `---
import { getClients } from '../lib/wordpress';
const clientsData = await getClients();

// Map WP Client data to the format the JS expects
const mappedClients = clientsData.map((client: any) => ({
    year: client.meta?.year_completed || "Active",
    title: client.title?.rendered || "",
    location: client.meta?.location || "",
    scope: client.meta?.scope || "",
    contract: client.meta?.contract_type || "",
    logo: client.meta?.client_logo || "",
    region: client.meta?.region || "central"
}));
---
<script define:vars={{ mappedClients }}>
    window.__ASTRO_CLIENTS__ = mappedClients;
</script>

${content}
`;

    fs.writeFileSync('/Users/bryanpaul/Local Sites/astro-e3es/src/components/ProjectHistory.astro', astroComponent);
    console.log("ProjectHistory.astro created successfully.");
} else {
    console.log("Could not find section.");
}
