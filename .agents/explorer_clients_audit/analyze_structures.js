import fs from 'fs';

const localWpDetailsPath = '/Users/bryanpaul/Local Sites/astro-e3es/.agents/explorer_clients_audit/local_wp_details.json';
const dumpPath = '/Users/bryanpaul/Local Sites/astro-e3es/clients_dump.json';
const featImgPath = '/Users/bryanpaul/Local Sites/astro-e3es/featured_image_mapping.json';

const clients = JSON.parse(fs.readFileSync(localWpDetailsPath, 'utf8'));
const dumpData = JSON.parse(fs.readFileSync(dumpPath, 'utf8'));
const featImgData = JSON.parse(fs.readFileSync(featImgPath, 'utf8'));

const analysis = [];

for (const client of clients) {
    const slug = client.slug;
    const content = client.content || '';
    
    // 1. Check for E3 Project block (wp-block-e3es-project or e3es/project)
    const hasProjectBlock = content.includes('wp-block-e3es-project') || content.includes('e3es/project');
    
    // 2. Check structure: description before project block
    // We split by the project block comment or div. If there is text/paragraphs before it, then description is before it.
    let descriptionBeforeProject = false;
    let descriptionText = '';
    if (hasProjectBlock) {
        const parts = content.split(/<!--\s*wp:e3es\/project|wp-block-e3es-project/);
        const beforeContent = parts[0].trim();
        // Remove comments and whitespace to see if there is actual content
        const cleanBefore = beforeContent.replace(/<!--[\s\S]*?-->/g, '').replace(/<[^>]+>/g, '').trim();
        if (cleanBefore.length > 0) {
            descriptionBeforeProject = true;
            descriptionText = cleanBefore.substring(0, 100) + '...';
        }
    }
    
    // 3. Check for Taj Mahal placeholder in featured image or in post content
    const featuredIsPlaceholder = client.featured_image_url && client.featured_image_url.includes('taj-mahal-placeholder');
    const contentHasPlaceholder = content.includes('taj-mahal-placeholder');
    const hasPlaceholder = featuredIsPlaceholder || contentHasPlaceholder;
    
    // 4. Check for video iframes (Vimeo/YouTube)
    const hasVideo = content.includes('iframe') || content.includes('youtube.com') || content.includes('vimeo.com');
    const videoMatches = [];
    const iframeRegex = /<iframe[^>]+src="([^"]+)"/g;
    let match;
    while ((match = iframeRegex.exec(content)) !== null) {
        videoMatches.push(match[1]);
    }
    
    // 5. Check if in featured image mapping
    const isMappedInImgJson = slug in featImgData;
    const mappedImgUrl = featImgData[slug] || '';
    
    // 6. Check if in dump
    const dumpItem = dumpData.find(c => c.slug === slug);
    const inDump = !!dumpItem;
    
    analysis.push({
        id: client.id,
        slug: slug,
        title: client.title,
        status: client.status,
        hasProjectBlock,
        descriptionBeforeProject,
        descriptionSnippet: descriptionText,
        featured_image_url: client.featured_image_url || '',
        featuredIsPlaceholder,
        contentHasPlaceholder,
        hasPlaceholder,
        hasVideo,
        videoUrls: videoMatches,
        inImgMapping: isMappedInImgJson,
        mappedImgUrl,
        inDump,
        regions: client.regions,
        industries: client.industries,
        services: client.services,
        meta: client.meta
    });
}

fs.writeFileSync('client_structure_analysis.json', JSON.stringify(analysis, null, 2));

console.log(`Analyzed ${analysis.length} local client posts.`);
console.log(`Has Project Block: ${analysis.filter(c => c.hasProjectBlock).length}`);
console.log(`Description Before Project Block: ${analysis.filter(c => c.descriptionBeforeProject).length}`);
console.log(`Has Placeholder Image: ${analysis.filter(c => c.hasPlaceholder).length}`);
console.log(`Has Video: ${analysis.filter(c => c.hasVideo).length}`);
