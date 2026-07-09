const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const sharp = require('sharp');

const WP_DIR = '/Users/bryanpaul/Local Sites/e3es2026/app/public';
const WP_CLI = `${WP_DIR}/wp-cli.phar`;
const PHP_BIN = '/Users/bryanpaul/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php';
const FLICKR_DIR = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads';
const UPLOADS_DIR = path.join(WP_DIR, 'wp-content', 'uploads');
const TEMP_DIR = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/temp_images';

// Ensure temp dir exists
if (!fs.existsSync(TEMP_DIR)) {
    fs.mkdirSync(TEMP_DIR, { recursive: true });
}

// Stop-words to ignore in matching
const stopWords = new Set(['isd', 'cisd', 'consolidated', 'county', 'medical', 'center', 'hospital', 'public', 'schools', 'community', 'college', 'facilities', 'commission', 'district', 'of', 'the', 'and', 'city', 'state']);

function getKeywords(slug) {
    return slug.split('-').filter(w => w.length > 2 && !stopWords.has(w));
}

function runWpCli(args) {
    const cmd = `"${PHP_BIN}" "${WP_CLI}" ${args}`;
    try {
        return execSync(cmd, { cwd: WP_DIR, encoding: 'utf8' }).trim();
    } catch (e) {
        console.error(`Error running WP-CLI: ${cmd}`, e.message);
        throw e;
    }
}

// Get all clients
const localOutput = runWpCli('post list --post_type=clients --post_status=any --fields=ID,post_name,post_title --posts_per_page=200 --format=json');
const localClients = JSON.parse(localOutput);

// Read flickr folders
const flickrFolders = fs.readdirSync(FLICKR_DIR).filter(f => fs.statSync(path.join(FLICKR_DIR, f)).isDirectory());

async function processClient(client, forceAll = false) {
    const slug = client.post_name;
    const postId = client.ID;
    const title = client.post_title;
    
    console.log(`\n========================================`);
    console.log(`Processing: ${slug} (ID: ${postId})`);
    console.log(`========================================`);
    
    // Check if post currently uses taj-mahal-placeholder
    const postContent = runWpCli(`post get ${postId} --field=post_content`);
    let thumbnailId = '';
    try {
        thumbnailId = runWpCli(`post meta get ${postId} _thumbnail_id`);
    } catch (e) {
        // Doesn't exist, which is fine
    }
    
    const usesPlaceholder = postContent.includes('taj-mahal-placeholder') || thumbnailId === 'placeholder_id';
    
    // Find matching Flickr folders
    const keywords = getKeywords(slug);
    if (keywords.length === 0) {
        keywords.push(...slug.split('-').filter(w => !stopWords.has(w)));
    }
    
    const matchedFolders = flickrFolders.filter(f => {
        const folderLower = f.toLowerCase();
        if (slug === 'hondo-isd' && folderLower.includes('rio')) return false;
        return keywords.every(kw => {
            if (kw === 'hondo' && !slug.includes('rio') && folderLower.includes('rio hondo')) return false;
            return folderLower.includes(kw);
        });
    });
    
    if (matchedFolders.length > 0) {
        console.log(`Matched Flickr folders: ${matchedFolders.join(', ')}`);
        // Gather all image files
        const imageFiles = [];
        matchedFolders.forEach(folder => {
            const folderPath = path.join(FLICKR_DIR, folder);
            const files = fs.readdirSync(folderPath);
            files.forEach(file => {
                const ext = path.extname(file).toLowerCase();
                if (ext === '.jpg' || ext === '.jpeg' || ext === '.png') {
                    imageFiles.push(path.join(folderPath, file));
                }
            });
        });
        
        if (imageFiles.length > 0) {
            console.log(`Found ${imageFiles.length} images in Flickr folders.`);
            // Sort to ensure deterministic order (e.g. after/before/construction)
            imageFiles.sort((a, b) => {
                const aName = a.toLowerCase();
                const bName = b.toLowerCase();
                if (aName.includes('after') && !bName.includes('after')) return -1;
                if (!aName.includes('after') && bName.includes('after')) return 1;
                return aName.localeCompare(bName);
            });
            
            const importedAttachments = [];
            // We only import up to 8 images to avoid bloat and keep things fast
            const imagesToImport = imageFiles.slice(0, 8);
            
            for (let i = 0; i < imagesToImport.length; i++) {
                const sourcePath = imagesToImport[i];
                const destName = `${slug}_flickr_${i}.jpg`;
                const destPath = path.join(TEMP_DIR, destName);
                
                console.log(` - Compressing ${path.basename(sourcePath)} -> under 300KB...`);
                // Use sharp to resize and compress
                await sharp(sourcePath)
                    .resize(1600, null, { withoutEnlargement: true })
                    .jpeg({ quality: 82, progressive: true })
                    .toFile(destPath);
                
                // Import to WP
                console.log(` - Importing to WP...`);
                const importRes = runWpCli(`media import "${destPath}" --title="${title} Case Study ${i + 1}" --post_id=${postId} --porcelain`);
                const attachmentId = parseInt(importRes.trim(), 10);
                if (attachmentId) {
                    const guid = runWpCli(`post get ${attachmentId} --field=guid`);
                    importedAttachments.push({ id: attachmentId, url: guid.trim() });
                    console.log(`   Imported attachment ID: ${attachmentId}, URL: ${guid.trim()}`);
                }
                
                // Delete temp file
                fs.unlinkSync(destPath);
            }
            
            if (importedAttachments.length > 0) {
                const featured = importedAttachments[0];
                console.log(`Setting featured image: ID ${featured.id}`);
                runWpCli(`post meta update ${postId} _thumbnail_id ${featured.id}`);
                
                // Update post content
                let updatedContent = postContent;
                
                // Replace taj-mahal-placeholder in banner
                // Find intro-banner: e.g. <!-- wp:e3es/intro-banner {"title":"...", "bgImageUrl":"..."} -->
                const bannerPattern = /<!-- wp:e3es\/intro-banner (\{.*?\}) -->/s;
                const match = updatedContent.match(bannerPattern);
                if (match) {
                    const bannerJson = JSON.parse(match[1]);
                    bannerJson.bgImageUrl = featured.url;
                    
                    // Also replace the background style in the rendered tag
                    const sectionPattern = /<section class="wp-block-e3es-intro-banner db-page-hero"[^>]*>/i;
                    const newSectionTag = `<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image:linear-gradient(rgba(14, 53, 27, 0.75), rgba(14, 53, 27, 0.75)), url(${featured.url});background-size:cover;background-position:50% 50%;background-repeat:no-repeat">`;
                    
                    updatedContent = updatedContent.replace(bannerPattern, `<!-- wp:e3es/intro-banner ${JSON.stringify(bannerJson)} -->`);
                    updatedContent = updatedContent.replace(sectionPattern, newSectionTag);
                }
                
                // Update project blocks
                // We find all project blocks: e.g. <!-- wp:e3es/project ... -->
                // Let's replace each project block's heroImageUrl
                const projectPattern = /<!-- wp:e3es\/project (\{.*?\}) -->/g;
                let projectIndex = 0;
                updatedContent = updatedContent.replace(projectPattern, (m, p1) => {
                    const projJson = JSON.parse(p1);
                    const imgToUse = importedAttachments[projectIndex] || featured;
                    projJson.heroImageUrl = imgToUse.url;
                    projectIndex++;
                    return `<!-- wp:e3es/project ${JSON.stringify(projJson)} -->`;
                });
                
                // Also update the style --hero-img properties in project divs
                const projectDivPattern = /<div class="wp-block-e3es-project project-section"([^>]*style="--hero-img:[^"]*")?/g;
                let divIndex = 0;
                // Wait, it is simpler to replace the style background attribute directly or use a regex replacement
                // Let's find each div block and replace --hero-img
                let searchIndex = 0;
                while (true) {
                    const divPos = updatedContent.indexOf('class="wp-block-e3es-project project-section"', searchIndex);
                    if (divPos === -1) break;
                    
                    const nextTagEnd = updatedContent.indexOf('>', divPos);
                    const tagContent = updatedContent.substring(divPos, nextTagEnd);
                    
                    const imgToUse = importedAttachments[divIndex] || featured;
                    const newTagContent = tagContent.replace(/style="--hero-img:[^"]*"/i, `style="--hero-img:url('${imgToUse.url}')"`);
                    
                    updatedContent = updatedContent.substring(0, divPos) + newTagContent + updatedContent.substring(nextTagEnd);
                    divIndex++;
                    searchIndex = divPos + newTagContent.length;
                }
                
                // Build a WordPress native gallery block at the bottom
                let galleryBlock = `\n<!-- wp:gallery {"linkTo":"none","sizeSlug":"large","images":[` + importedAttachments.map(a => a.id).join(',') + `]} -->\n`;
                galleryBlock += `<figure class="wp-block-gallery has-nested-images columns-default is-cropped">`;
                importedAttachments.forEach(a => {
                    galleryBlock += `<!-- wp:image {"id":${a.id},"sizeSlug":"large","linkDestination":"none"} -->\n`;
                    galleryBlock += `<figure class="wp-block-image size-large"><img src="${a.url}" alt="" class="wp-image-${a.id}"/></figure>\n`;
                    galleryBlock += `<!-- /wp:image -->\n`;
                });
                galleryBlock += `</figure>\n<!-- /wp:gallery -->\n`;
                
                // Append gallery block before FAQ section or at the end
                const faqPos = updatedContent.indexOf('<!-- wp:e3es/faq-section -->');
                if (faqPos !== -1) {
                    updatedContent = updatedContent.substring(0, faqPos) + galleryBlock + updatedContent.substring(faqPos);
                } else {
                    updatedContent += galleryBlock;
                }
                
                // Update post content in WP
                const tempContentFile = path.join(TEMP_DIR, 'temp_content.txt');
                fs.writeFileSync(tempContentFile, updatedContent);
                runWpCli(`post update ${postId} "${tempContentFile}"`);
                fs.unlinkSync(tempContentFile);
                console.log(`Successfully imported Flickr photos and updated post content for ${slug}.`);
            }
        }
    } else {
        console.log(`No Flickr folder matched. Finding local image...`);
        // Find matching image in database or uploads folder
        // Let's call a PHP helper script to find the best image and apply it
        const phpHelper = `
            require_once '${WP_DIR}/wp-load.php';
            $post_id = ${postId};
            $slug = '${slug}';
            
            // Search database for attachments containing keywords
            global $wpdb;
            $keywords = array(${keywords.map(k => `'${k}'`).join(',')});
            
            $attachments = array();
            foreach ($keywords as $kw) {
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT ID, guid FROM $wpdb->posts WHERE post_type = 'attachment' AND (post_title LIKE %s OR guid LIKE %s)",
                    '%' . $kw . '%', '%' . $kw . '%'
                ));
                foreach ($results as $r) {
                    $attachments[$r->ID] = $r->guid;
                }
            }
            
            $best_id = 0;
            $best_url = '';
            
            // Filter out logos/icons
            foreach ($attachments as $id => $url) {
                $lower = strtolower($url);
                if (strpos($lower, 'logo') === false && 
                    strpos($lower, '150x150') === false && 
                    strpos($lower, '300x115') === false && 
                    strpos($lower, 'cropped-') === false &&
                    (strpos($lower, '.jpg') !== false || strpos($lower, '.jpeg') !== false)) {
                    $best_id = $id;
                    $best_url = $url;
                    break;
                }
            }
            
            // If still no ID, try PNGs
            if (!$best_id) {
                foreach ($attachments as $id => $url) {
                    $lower = strtolower($url);
                    if (strpos($lower, 'logo') === false && 
                        strpos($lower, '150x150') === false && 
                        strpos($lower, '300x115') === false) {
                        $best_id = $id;
                        $best_url = $url;
                        break;
                    }
                }
            }
            
            if ($best_id) {
                echo "ATTACH_FOUND:" . $best_id . ":" . $best_url;
            } else {
                echo "NO_ATTACH";
            }
        `;
        
        const helperPath = path.join(TEMP_DIR, 'helper.php');
        fs.writeFileSync(helperPath, phpHelper);
        const phpRes = execSync(`"${PHP_BIN}" "${helperPath}"`, { encoding: 'utf8' }).trim();
        fs.unlinkSync(helperPath);
        
        if (phpRes.startsWith('ATTACH_FOUND:')) {
            const parts = phpRes.split(':');
            const attachId = parts[1];
            const attachUrl = parts[2] + ':' + parts[3]; // handle http:// prefix
            
            console.log(`Found local attachment: ID ${attachId}, URL ${attachUrl}`);
            runWpCli(`post meta update ${postId} _thumbnail_id ${attachId}`);
            
            // Replace taj-mahal-placeholder in content
            let updatedContent = postContent;
            
            // Replace taj-mahal-placeholder in banner
            const bannerPattern = /<!-- wp:e3es\/intro-banner (\{.*?\}) -->/s;
            const match = updatedContent.match(bannerPattern);
            if (match) {
                const bannerJson = JSON.parse(match[1]);
                if (bannerJson.bgImageUrl.includes('taj-mahal-placeholder')) {
                    bannerJson.bgImageUrl = attachUrl;
                }
                updatedContent = updatedContent.replace(bannerPattern, `<!-- wp:e3es/intro-banner ${JSON.stringify(bannerJson)} -->`);
            }
            
            const sectionPattern = /<section class="wp-block-e3es-intro-banner db-page-hero"[^>]*>/i;
            const newSectionTag = `<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image:linear-gradient(rgba(14, 53, 27, 0.75), rgba(14, 53, 27, 0.75)), url(${attachUrl});background-size:cover;background-position:50% 50%;background-repeat:no-repeat">`;
            updatedContent = updatedContent.replace(sectionPattern, newSectionTag);
            
            // Also replace in project blocks
            const projectPattern = /<!-- wp:e3es\/project (\{.*?\}) -->/g;
            updatedContent = updatedContent.replace(projectPattern, (m, p1) => {
                const projJson = JSON.parse(p1);
                if (!projJson.heroImageUrl || projJson.heroImageUrl.includes('taj-mahal-placeholder')) {
                    projJson.heroImageUrl = attachUrl;
                }
                return `<!-- wp:e3es/project ${JSON.stringify(projJson)} -->`;
            });
            
            // Replaces --hero-img
            let searchIndex = 0;
            while (true) {
                const divPos = updatedContent.indexOf('class="wp-block-e3es-project project-section"', searchIndex);
                if (divPos === -1) break;
                
                const nextTagEnd = updatedContent.indexOf('>', divPos);
                const tagContent = updatedContent.substring(divPos, nextTagEnd);
                
                if (tagContent.includes('taj-mahal-placeholder') || tagContent.includes('none') || !tagContent.includes('--hero-img')) {
                    const newTagContent = tagContent.replace(/style="--hero-img:[^"]*"/i, `style="--hero-img:url('${attachUrl}')"`);
                    updatedContent = updatedContent.substring(0, divPos) + newTagContent + updatedContent.substring(nextTagEnd);
                    searchIndex = divPos + newTagContent.length;
                } else {
                    searchIndex = nextTagEnd;
                }
            }
            
            const tempContentFile = path.join(TEMP_DIR, 'temp_content.txt');
            fs.writeFileSync(tempContentFile, updatedContent);
            runWpCli(`post update ${postId} "${tempContentFile}"`);
            fs.unlinkSync(tempContentFile);
            console.log(`Successfully updated placeholder references for ${slug}.`);
        } else {
            console.log(`No local attachment found. Searching file on disk...`);
            // We can search uploads folder for files matching keywords
            // And import it
            const uploadsFiles = fs.readdirSync(UPLOADS_DIR); // Wait, uploads is nested by year/month
            // Let's use a recursively matched file from the previous find_all_local_uploads output!
            // Wait, we can write a simple php script that imports the file if found!
            const phpImportHelper = `
                require_once '${WP_DIR}/wp-load.php';
                $post_id = ${postId};
                $slug = '${slug}';
                $keywords = array(${keywords.map(k => `'${k}'`).join(',')});
                
                $uploads_dir = wp_upload_dir()['basedir'];
                $dir_iterator = new RecursiveDirectoryIterator($uploads_dir);
                $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
                
                $found_file = '';
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $fname = strtolower($file->getFilename());
                        if (strpos($fname, 'logo') !== false || strpos($fname, '150x150') !== false || strpos($fname, '300x115') !== false) {
                            continue;
                        }
                        $match = true;
                        foreach ($keywords as $kw) {
                            if (strpos($fname, $kw) === false) {
                                $match = false;
                                break;
                            }
                        }
                        if ($match) {
                            $found_file = $file->getPathname();
                            break;
                        }
                    }
                }
                
                if ($found_file) {
                    // Import the file
                    $file_array = array(
                        'name' => basename($found_file),
                        'tmp_name' => $found_file
                    );
                    // Copy file to temp location to avoid deleting original in media_handle_sideload
                    $temp_file = tempnam(sys_get_temp_dir(), 'wp_import_');
                    copy($found_file, $temp_file);
                    $file_array['tmp_name'] = $temp_file;
                    
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    
                    $attach_id = media_handle_sideload($file_array, $post_id, '${title} Featured Image');
                    if (!is_wp_error($attach_id)) {
                        $url = wp_get_attachment_url($attach_id);
                        echo "IMPORTED:" . $attach_id . ":" . $url;
                    } else {
                        echo "ERROR:" . $attach_id->get_error_message();
                    }
                    @unlink($temp_file);
                } else {
                    echo "NOT_FOUND_ON_DISK";
                }
            `;
            
            const helperPath = path.join(TEMP_DIR, 'helper.php');
            fs.writeFileSync(helperPath, phpImportHelper);
            const phpRes = execSync(`"${PHP_BIN}" "${helperPath}"`, { encoding: 'utf8' }).trim();
            fs.unlinkSync(helperPath);
            
            if (phpRes.startsWith('IMPORTED:')) {
                const parts = phpRes.split(':');
                const attachId = parts[1];
                const attachUrl = parts[2] + ':' + parts[3];
                
                console.log(`Imported and associated image from disk: ID ${attachId}, URL ${attachUrl}`);
                runWpCli(`post meta update ${postId} _thumbnail_id ${attachId}`);
                
                // Replace in content
                let updatedContent = postContent;
                
                // Replace in banner
                const bannerPattern = /<!-- wp:e3es\/intro-banner (\{.*?\}) -->/s;
                const match = updatedContent.match(bannerPattern);
                if (match) {
                    const bannerJson = JSON.parse(match[1]);
                    if (bannerJson.bgImageUrl.includes('taj-mahal-placeholder')) {
                        bannerJson.bgImageUrl = attachUrl;
                    }
                    updatedContent = updatedContent.replace(bannerPattern, `<!-- wp:e3es/intro-banner ${JSON.stringify(bannerJson)} -->`);
                }
                
                const sectionPattern = /<section class="wp-block-e3es-intro-banner db-page-hero"[^>]*>/i;
                const newSectionTag = `<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image:linear-gradient(rgba(14, 53, 27, 0.75), rgba(14, 53, 27, 0.75)), url(${attachUrl});background-size:cover;background-position:50% 50%;background-repeat:no-repeat">`;
                updatedContent = updatedContent.replace(sectionPattern, newSectionTag);
                
                // Also replace in project blocks
                const projectPattern = /<!-- wp:e3es\/project (\{.*?\}) -->/g;
                updatedContent = updatedContent.replace(projectPattern, (m, p1) => {
                    const projJson = JSON.parse(p1);
                    if (!projJson.heroImageUrl || projJson.heroImageUrl.includes('taj-mahal-placeholder')) {
                        projJson.heroImageUrl = attachUrl;
                    }
                    return `<!-- wp:e3es/project ${JSON.stringify(projJson)} -->`;
                });
                
                // Replaces --hero-img
                let searchIndex = 0;
                while (true) {
                    const divPos = updatedContent.indexOf('class="wp-block-e3es-project project-section"', searchIndex);
                    if (divPos === -1) break;
                    
                    const nextTagEnd = updatedContent.indexOf('>', divPos);
                    const tagContent = updatedContent.substring(divPos, nextTagEnd);
                    
                    if (tagContent.includes('taj-mahal-placeholder') || tagContent.includes('none') || !tagContent.includes('--hero-img')) {
                        const newTagContent = tagContent.replace(/style="--hero-img:[^"]*"/i, `style="--hero-img:url('${attachUrl}')"`);
                        updatedContent = updatedContent.substring(0, divPos) + newTagContent + updatedContent.substring(nextTagEnd);
                        searchIndex = divPos + newTagContent.length;
                    } else {
                        searchIndex = nextTagEnd;
                    }
                }
                
                const tempContentFile = path.join(TEMP_DIR, 'temp_content.txt');
                fs.writeFileSync(tempContentFile, updatedContent);
                runWpCli(`post update ${postId} "${tempContentFile}"`);
                fs.unlinkSync(tempContentFile);
                console.log(`Successfully updated placeholder references for ${slug}.`);
            } else {
                console.log(`Fallback: using default general marketing image...`);
                // Let's use general marketing image (ID 126 or GUID http://e3es2026.local/wp-content/uploads/2026/06/54474213788_147e72a636_k.jpg)
                const fallbackId = 126;
                const fallbackUrl = 'http://e3es2026.local/wp-content/uploads/2026/06/54474213788_147e72a636_k.jpg';
                
                runWpCli(`post meta update ${postId} _thumbnail_id ${fallbackId}`);
                let updatedContent = postContent;
                
                // Replace in banner
                const bannerPattern = /<!-- wp:e3es\/intro-banner (\{.*?\}) -->/s;
                const match = updatedContent.match(bannerPattern);
                if (match) {
                    const bannerJson = JSON.parse(match[1]);
                    if (bannerJson.bgImageUrl.includes('taj-mahal-placeholder')) {
                        bannerJson.bgImageUrl = fallbackUrl;
                    }
                    updatedContent = updatedContent.replace(bannerPattern, `<!-- wp:e3es/intro-banner ${JSON.stringify(bannerJson)} -->`);
                }
                
                const sectionPattern = /<section class="wp-block-e3es-intro-banner db-page-hero"[^>]*>/i;
                const newSectionTag = `<section class="wp-block-e3es-intro-banner db-page-hero" style="background-image:linear-gradient(rgba(14, 53, 27, 0.75), rgba(14, 53, 27, 0.75)), url(${fallbackUrl});background-size:cover;background-position:50% 50%;background-repeat:no-repeat">`;
                updatedContent = updatedContent.replace(sectionPattern, newSectionTag);
                
                const tempContentFile = path.join(TEMP_DIR, 'temp_content.txt');
                fs.writeFileSync(tempContentFile, updatedContent);
                runWpCli(`post update ${postId} "${tempContentFile}"`);
                fs.unlinkSync(tempContentFile);
                console.log(`Successfully applied fallback featured image for ${slug}.`);
            }
        }
    }
}

async function main() {
    // If a slug argument is passed, only run for that slug
    const targetSlug = process.argv[2];
    if (targetSlug) {
        const client = localClients.find(c => c.post_name === targetSlug);
        if (client) {
            await processClient(client, true);
        } else {
            console.error(`Client not found for slug: ${targetSlug}`);
        }
    } else {
        // Run for all clients!
        for (const client of localClients) {
            await processClient(client);
        }
    }
}

main().catch(console.error);
