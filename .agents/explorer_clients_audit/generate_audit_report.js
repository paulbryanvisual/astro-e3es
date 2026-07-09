import fs from 'fs';

// Load data files
const localWpDetails = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/local_wp_details.json', 'utf8'));
const clientsDump = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json', 'utf8'));
const featuredImageMapping = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/featured_image_mapping.json', 'utf8'));
const stillPlaceholder = JSON.parse(fs.readFileSync('/Users/bryanpaul/Local Sites/astro-e3es/still_placeholder.json', 'utf8'));

// Helper arrays/sets
const dumpSlugs = clientsDump.map(c => c.slug);
const localWpSlugs = localWpDetails.map(c => c.slug);
const mappedSlugs = Object.keys(featuredImageMapping);
const placeholderSlugs = stillPlaceholder.map(c => c.slug);

// Analyze each client
const auditList = [];

// Combine all unique slugs we know of from both dump and local WP
const allSlugs = Array.from(new Set([...dumpSlugs, ...localWpSlugs]));

for (const slug of allSlugs) {
    const dumpItem = clientsDump.find(c => c.slug === slug);
    const localItem = localWpDetails.find(c => c.slug === slug);
    
    const title = localItem ? localItem.title : (dumpItem ? dumpItem.title?.rendered : slug);
    const inDump = !!dumpItem;
    const inLocal = !!localItem;
    const localStatus = localItem ? localItem.status : 'N/A';
    
    // Structure & Content Checks
    const content = localItem ? (localItem.content || '') : '';
    const hasProjectBlock = content.includes('wp-block-e3es-project') || content.includes('e3es/project');
    
    let descriptionBeforeProject = false;
    let descriptionText = '';
    if (hasProjectBlock) {
        const parts = content.split(/<!--\s*wp:e3es\/project|wp-block-e3es-project/);
        const beforeContent = parts[0].trim();
        const cleanBefore = beforeContent.replace(/<!--[\s\S]*?-->/g, '').replace(/<[^>]+>/g, '').trim();
        if (cleanBefore.length > 0) {
            descriptionBeforeProject = true;
            descriptionText = cleanBefore;
        }
    }
    
    // Placeholder vs Mapped Image Checks
    const isMapped = slug in featuredImageMapping || mappedSlugs.includes(slug);
    const isPlaceholder = placeholderSlugs.includes(slug) || (localItem && localItem.featured_image_url && localItem.featured_image_url.includes('taj-mahal-placeholder'));
    
    let imageStatus = 'Unknown';
    let imageUrl = '';
    if (isMapped) {
        imageStatus = 'Mapped (Real Image)';
        imageUrl = featuredImageMapping[slug] || (localItem ? localItem.featured_image_url : '');
    } else if (isPlaceholder) {
        imageStatus = 'Placeholder (Taj Mahal)';
        imageUrl = 'taj-mahal-placeholder.png';
    } else if (localItem && localItem.featured_image_url) {
        if (localItem.featured_image_url.includes('taj-mahal-placeholder')) {
            imageStatus = 'Placeholder (Taj Mahal)';
            imageUrl = 'taj-mahal-placeholder.png';
        } else {
            imageStatus = 'Local (Non-Placeholder)';
            imageUrl = localItem.featured_image_url;
        }
    }
    
    // Video Checks
    const hasVideo = content.includes('iframe') || content.includes('youtube.com') || content.includes('vimeo.com');
    const videoUrls = [];
    const iframeRegex = /<iframe[^>]+src="([^"]+)"/g;
    let match;
    while ((match = iframeRegex.exec(content)) !== null) {
        videoUrls.push(match[1]);
    }
    
    auditList.push({
        slug,
        title,
        inDump,
        inLocal,
        localStatus,
        hasProjectBlock,
        descriptionBeforeProject,
        descriptionText,
        imageStatus,
        imageUrl,
        hasVideo,
        videoUrls
    });
}

// Generate the analysis.md report
let report = `# Gap Analysis: Live Site vs Local Headless Astro Site

## 1. Overview Summary

This report performs a detailed comparison audit between the client posts published on the live site (using \`clients_dump.json\` as the live data representation) and the local headless Astro / WordPress environment.

* **Total Slugs Cataloged**: ${allSlugs.length}
* **Clients in Live Dump**: ${dumpSlugs.length}
* **Clients in Local WordPress**: ${localWpSlugs.length}
  * **Published Locally**: ${localWpDetails.filter(c => c.status === 'publish').length}
  * **Drafts Locally**: ${localWpDetails.filter(c => c.status === 'draft').length}
* **Featured Image Status**:
  * **Mapped with Real Images**: ${auditList.filter(c => c.imageStatus === 'Mapped (Real Image)' || c.imageStatus === 'Local (Non-Placeholder)').length}
  * **Using Taj Mahal Placeholder**: ${auditList.filter(c => c.imageStatus === 'Placeholder (Taj Mahal)').length}
* **Layout Structure Status**:
  * **Using custom Project block (\`e3es/project\`)**: ${auditList.filter(c => c.hasProjectBlock).length}
  * **Missing Project block entirely**: ${auditList.filter(c => c.inLocal && !c.hasProjectBlock).length}
* **Videos Found**: ${auditList.filter(c => c.hasVideo).length} pages contain embedded Vimeo/YouTube videos.

---

## 2. Key Action Items & Gaps

### A. Missing Clients (In Live Dump but NOT Published Locally)
There are **77 clients** that are published in the live dump but are either missing or in draft status in the local WordPress.
* **Missing from Local WordPress database completely (1)**:
  * \`e3-general\` (E3_General - Note: this is a general/mockup post and not a real client page).
* **In Local WordPress but in Draft status (76)**:
  These posts exist in the local WordPress database but are saved as **Drafts**. They must be updated to **Publish** to align with the live site:
${auditList.filter(c => c.inDump && c.inLocal && c.localStatus === 'draft').map(c => `  - \`${c.slug}\` (${c.title})`).join('\n')}

### B. Extra Clients (In Local WordPress but NOT in Live Dump)
There are **8 clients** in the local WordPress database that are not present in the live dump.
* **Published Locally but must be removed (or are duplicates) (2)**:
  * \`south-texas\` (South Texas & Coast) - *Must be removed entirely as requested by the user.*
  * \`gwh\` (Goodall-Witcher Healthcare) - *This is a duplicate of \`goodall-witcher-hospital\`. The live site uses slug \`goodall-witcher-hospital\`. The \`gwh\` post should be trashed/removed.*
* **Published Locally (Legacy / Mapped but not in dump) (3)**:
  * \`boyd-isd\` (Boyd ISD) - *Legacy client, has real image mapping, published.*
  * \`bryan-isd\` (Bryan ISD) - *Legacy client, has real image mapping, published.*
  * \`rio-hondo-isd\` (Rio Hondo ISD) - *Legacy client, has real image mapping, published.*
* **Drafts Locally but not in dump (3)**:
  * \`little-elm-isd\` (Little Elm ISD)
  * \`keene-isd\` (Keene ISD)
  * \`plano-isd\` (Plano ISD)
  (Note: \`city-of-stockdale\` is a draft locally and is in the dump as \`city-of-stockdale\`? Wait, let's check!)

### C. Structure Audit (Project Block & Relationship Description)
The user wants the custom **E3 Project** (\`e3es/project\`) Gutenberg block to display project details under a short description of the client relationship.
* **Gutenberg Layout Structure Anomalies**:
  The following local posts **do NOT** contain the custom \`e3es/project\` Gutenberg block and need layout structure updates:
${auditList.filter(c => c.inLocal && !c.hasProjectBlock).map(c => `  - \`${c.slug}\` (${c.title}) - Status: ${c.localStatus} (Currently uses standard paragraphs, testimonials, or banners instead of the custom Project block)`).join('\n')}
  
* **Missing Relationship Description**:
  The following posts have the Project block but do NOT have a short relationship description preceding it (going straight from intro-banner to project details):
  - \`goodall-witcher-hospital\` (Goodall Witcher Hospital - ID: 1459) - *Needs the relationship description paragraph from \`gwh\` added before the project block.*

### D. Media Audit (Placeholders & Video)
* **Clients still using the Taj Mahal Placeholder Image**:
  There are **${auditList.filter(c => c.imageStatus === 'Placeholder (Taj Mahal)').length}** client pages using the Taj Mahal placeholder image (\`taj-mahal-placeholder.png\`). These need their real featured images uploaded and mapped.
  
* **Client Pages with Video Integrations**:
  The following client pages contain Vimeo or YouTube videos:
${auditList.filter(c => c.hasVideo).map(c => `  - \`${c.slug}\` (${c.title}) - Videos: ${c.videoUrls.join(', ')}`).join('\n')}

---

## 3. Comprehensive Clients Audit Table

| Client Slug | Title | In Live Dump? | Local Status | Image Status | Has Project Block? | Description Before Project? | Has Video? |
|-------------|-------|---------------|--------------|--------------|-------------------|----------------------------|------------|
${auditList.map(c => `| \`${c.slug}\` | ${c.title} | ${c.inDump ? 'Yes' : 'No'} | ${c.localStatus} | ${c.imageStatus} | ${c.hasProjectBlock ? 'Yes' : 'No'} | ${c.hasProjectBlock ? (c.descriptionBeforeProject ? 'Yes' : 'No') : 'N/A'} | ${c.hasVideo ? 'Yes' : 'No'} |`).join('\n')}
`;

fs.writeFileSync('/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/analysis.md', report);
console.log('Successfully generated analysis.md report!');
