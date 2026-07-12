<?php
/**
 * Image & Video Content Audit Script
 * Analyzes all client pages and generates a CSV audit report.
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at: $wp_load\n");
}
require_once $wp_load;

echo "🚀 Starting Image & Video Content Audit...\n";

// Configuration paths
$flickr_downloads_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/flickr_downloads';
$legacy_html_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/website/legacy-html';
$output_csv_path = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/image_video_audit.csv';

// Helper function to normalize filenames for fuzzy matching
function normalize_filename_audit($filename) {
    // Strip query parameters
    $filename = strtok($filename, '?');
    // Strip extension
    $name = preg_replace('/\.(?:jpg|jpeg|png|gif|webp|svg|jfif)$/i', '', $filename);
    // Strip WordPress dimension suffix (e.g. -1024x768 or -370x280)
    $name = preg_replace('/-\d+x\d+$/i', '', $name);
    // Lowercase and strip non-alphanumeric
    $name = preg_replace('/[^a-z0-9]/', '', strtolower($name));
    return $name;
}

// Helper function to normalize client name
function normalize_client_name_audit($name) {
    $name = str_replace(['isd', 'cisd', 'consolidated', 'school', 'schools', 'district', 'city of'], '', strtolower($name));
    return preg_replace('/[^a-z0-9]/', '', $name);
}

// Helper to find actual client association when confidence is low
function get_actual_association_details($filename, $current_client_slug) {
    static $all_clients = [
        'anderson-shiro-cisd' => 'Anderson-Shiro CISD',
        'baird-isd' => 'Baird ISD',
        'ballinger-isd' => 'Ballinger ISD',
        'banquete-isd' => 'Banquete ISD',
        'bellevue-isd' => 'Bellevue ISD',
        'big-sandy-isd' => 'Big Sandy ISD',
        'bishop-cisd' => 'Bishop CISD',
        'bowie-isd' => 'Bowie ISD',
        'boyd-isd' => 'Boyd ISD',
        'brenham-isd' => 'Brenham ISD',
        'brownsville-isd' => 'Brownsville ISD',
        'bryan-isd' => 'Bryan ISD',
        'caddo-mills-isd' => 'Caddo Mills ISD',
        'caldwell-isd' => 'Caldwell ISD',
        'carrizo-springs-cisd' => 'Carrizo Springs CISD',
        'castleberry-isd' => 'Castleberry ISD',
        'cedar-hill-isd' => 'Cedar Hill ISD',
        'chico-isd' => 'Chico ISD',
        'cleveland-isd' => 'Cleveland ISD',
        'columbia-brazoria-isd' => 'Columbia-Brazoria ISD',
        'cooke-county' => 'Cooke County',
        'corsicana-isd' => 'Corsicana ISD',
        'desoto-isd' => 'DeSoto ISD',
        'donna-isd' => 'Donna ISD',
        'eagle-pass-isd' => 'Eagle Pass ISD',
        'edcouch-elsa-isd' => 'Edcouch-Elsa ISD',
        'edgewood-isd' => 'Edgewood ISD',
        'ennis-isd' => 'Ennis ISD',
        'ferris-isd' => 'Ferris ISD',
        'gainesville-isd' => 'Gainesville ISD',
        'galena-park-isd' => 'Galena Park ISD',
        'glen-rose-medical-center' => 'Glen Rose Medical Center',
        'goodall-witcher-hospital' => 'Goodall Witcher Hospital',
        'granbury-isd' => 'Granbury ISD',
        'greenville-isd' => 'Greenville ISD',
        'gruver-isd' => 'Gruver ISD',
        'hardin-county' => 'Hardin County',
        'hardin-jefferson-isd' => 'Hardin-Jefferson ISD',
        'hawkins-isd' => 'Hawkins ISD',
        'hondo-isd' => 'Hondo ISD',
        'houston-community-college' => 'Houston Community College',
        'idea-public-schools' => 'IDEA Public Schools',
        'ingram-isd' => 'Ingram ISD',
        'italy-isd' => 'Italy ISD',
        'jasper-isd' => 'Jasper ISD',
        'katy-isd' => 'Katy ISD',
        'kountze-isd' => 'Kountze ISD',
        'lake-worth-isd' => 'Lake Worth ISD',
        'lancaster-isd' => 'Lancaster ISD',
        'liberty-isd' => 'Liberty ISD',
        'little-elm-isd' => 'Little Elm ISD',
        'llano-isd' => 'Llano ISD',
        'lubbock-isd' => 'Lubbock ISD',
        'lyford-isd' => 'Lyford ISD',
        'manor-isd' => 'Manor ISD',
        'marble-falls-isd' => 'Marble Falls ISD',
        'mercedes-isd' => 'Mercedes ISD',
        'mesquite-isd' => 'Mesquite ISD',
        'moulton-isd' => 'Moulton ISD',
        'nacogdoches-isd' => 'Nacogdoches ISD',
        'needville-isd' => 'Needville ISD',
        'new-boston-isd' => 'New Boston ISD',
        'nocona-isd' => 'Nocona ISD',
        'normangee-isd' => 'Normangee ISD',
        'north-texas-medical-center' => 'North Texas Medical Center',
        'odem-edroy-isd' => 'Odem-Edroy ISD',
        'pecos-isd' => 'Pecos ISD',
        'pilot-point-isd' => 'Pilot Point ISD',
        'plano-isd' => 'Plano ISD',
        'poolville-isd' => 'Poolville ISD',
        'port-neches-groves-isd' => 'Port Neches-Groves ISD',
        'prosper-isd' => 'Prosper ISD',
        'raymondville-isd' => 'Raymondville ISD',
        'ricardo-isd' => 'Ricardo ISD',
        'rio-hondo-isd' => 'Rio Hondo ISD',
        'robstown-isd' => 'Robstown ISD',
        'roscoe-collegiate-isd' => 'Roscoe Collegiate ISD',
        'royal-isd' => 'Royal ISD',
        'rusk-isd' => 'Rusk ISD',
        'saint-jo-isd' => 'Saint Jo ISD',
        'san-benito-cisd' => 'San Benito CISD',
        'san-jacinto-community-college' => 'San Jacinto Community College',
        'santa-fe-isd' => 'Santa Fe ISD',
        'silsbee-isd' => 'Silsbee ISD',
        'skidmore-tynan-isd' => 'Skidmore-Tynan ISD',
        'texas-facilities-commission' => 'Texas Facilities Commission',
        'tom-bean-isd' => 'Tom Bean ISD',
        'trenton-isd' => 'Trenton ISD',
        'trinity-isd' => 'Trinity ISD',
        'valley-view-isd' => 'Valley View ISD',
        'vernon-isd' => 'Vernon ISD',
        'waxahachie-isd' => 'Waxahachie ISD',
        'weslaco-isd' => 'Weslaco ISD',
        'west-hardin-ccisd' => 'West Hardin CCISD',
        'woodville-isd' => 'Woodville ISD'
    ];

    // Check specific file ID matches first
    $assoc_slug = null;
    if (strpos($filename, '54474213788') !== false) {
        $assoc_slug = 'glen-rose-medical-center';
    } else {
        // Try fuzzy slug match inside filename
        $norm_filename = strtolower(str_replace('_', '-', $filename));
        foreach ($all_clients as $slug => $name) {
            $slug_clean = str_replace('-isd', '', str_replace('-cisd', '', str_replace('-ccisd', '', $slug)));
            if (strpos($norm_filename, $slug_clean) !== false) {
                $assoc_slug = $slug;
                break;
            }
        }
    }

    if ($assoc_slug && $assoc_slug !== $current_client_slug) {
        // Find if it is on the associated client's page
        $p = get_page_by_path($assoc_slug, OBJECT, 'clients');
        $is_on_page = 'No';
        if ($p) {
            if (strpos($p->post_content, basename($filename)) !== false) {
                $is_on_page = 'Yes';
            }
        }
        
        $name = $all_clients[$assoc_slug];
        $link = "http://astro-e3es.localhost:8080/clients/{$assoc_slug}/";
        return [
            'explanation' => "This image is actually associated with {$name} ({$link}). It is " . ($is_on_page === 'Yes' ? 'present' : 'not present') . " on that page.",
            'confidence' => 'Low'
        ];
    }

    return null;
}

// 1. Scan Flickr Downloads recursively
echo "Scanning Flickr downloads folder...\n";
$flickr_map = [];
if (is_dir($flickr_downloads_dir)) {
    $directory = new RecursiveDirectoryIterator($flickr_downloads_dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($iterator as $fileinfo) {
        if ($fileinfo->isFile()) {
            $ext = strtolower($fileinfo->getExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'])) {
                $filename = $fileinfo->getFilename();
                $norm = normalize_filename_audit($filename);
                $folder = basename(dirname($fileinfo->getPathname()));
                $flickr_map[$norm] = $folder;
            }
        }
    }
    echo "  Mapped " . count($flickr_map) . " Flickr files.\n";
} else {
    echo "  [WARNING] Flickr downloads directory not found.\n";
}

// 2. Scan Legacy HTML files
echo "Scanning legacy HTML files...\n";
$html_img_map = [];
$html_video_map = [];
if (is_dir($legacy_html_dir)) {
    $files = glob($legacy_html_dir . '/*.html');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $basename = basename($file);
        
        // Find image tags
        if (preg_match_all('/src=["\']([^"\']+\.(?:jpg|jpeg|png|gif|webp|svg|jfif))["\']/i', $content, $img_matches)) {
            foreach ($img_matches[1] as $img_src) {
                $filename = basename($img_src);
                $norm = normalize_filename_audit($filename);
                if (!isset($html_img_map[$norm])) {
                    $html_img_map[$norm] = [];
                }
                if (!in_array($basename, $html_img_map[$norm])) {
                    $html_img_map[$norm][] = $basename;
                }
            }
        }
        
        // Find Vimeo video links
        if (preg_match_all('/(?:player\.)?vimeo\.com\/(?:video\/)?([0-9]+)/i', $content, $video_matches)) {
            foreach ($video_matches[1] as $vimeo_id) {
                if (!isset($html_video_map[$vimeo_id])) {
                    $html_video_map[$vimeo_id] = [];
                }
                if (!in_array($basename, $html_video_map[$vimeo_id])) {
                    $html_video_map[$vimeo_id][] = $basename;
                }
            }
        }
    }
    echo "  Mapped " . count($html_img_map) . " HTML image tags and " . count($html_video_map) . " Vimeo videos.\n";
} else {
    echo "  [WARNING] Legacy HTML directory not found.\n";
}

// 3. Query Client Pages
echo "Querying all clients from database...\n";
$query = new WP_Query([
    'post_type' => 'clients',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
]);

echo "Found " . $query->found_posts . " client posts.\n";

$audit_rows = [];

// Helper function to get image details from filesystem
function get_image_details($url) {
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    $base_dir = $upload_dir['basedir'];
    
    $width = 'Unknown';
    $height = 'Unknown';
    $size_kb = 'Unknown';
    
    if (strpos($url, $base_url) !== false) {
        $rel_path = str_replace($base_url, '', $url);
        $abs_path = $base_dir . $rel_path;
        
        // Check both original and sized files
        $orig_abs_path = preg_replace('/-\d+x\d+(\.[a-z0-9]+)$/i', '$1', $abs_path);
        
        $file_to_check = file_exists($orig_abs_path) ? $orig_abs_path : (file_exists($abs_path) ? $abs_path : null);
        
        if ($file_to_check) {
            $info = @getimagesize($file_to_check);
            if ($info) {
                $width = $info[0];
                $height = $info[1];
            }
            $size_kb = round(filesize($file_to_check) / 1024, 1);
        }
    }
    
    return [
        'width' => $width,
        'height' => $height,
        'size_kb' => $size_kb
    ];
}

// Helper to check for AI metadata signatures in local image files
function check_image_ai_signatures($url) {
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    $base_dir = $upload_dir['basedir'];
    
    if (strpos($url, $base_url) === false) {
        return 'No (External or non-uploaded asset)';
    }
    
    $rel_path = str_replace($base_url, '', $url);
    $abs_path = $base_dir . $rel_path;
    
    // Check both original and sized files
    $orig_abs_path = preg_replace('/-\d+x\d+(\.[a-z0-9]+)$/i', '$1', $abs_path);
    $file_to_check = file_exists($orig_abs_path) ? $orig_abs_path : (file_exists($abs_path) ? $abs_path : null);
    
    if (!$file_to_check) {
        return 'No (File not found on disk)';
    }
    
    // Check filename
    $fn = strtolower(basename($file_to_check));
    if (strpos($fn, 'ai-generated') !== false || strpos($fn, 'midjourney') !== false || strpos($fn, 'dall-e') !== false) {
        return 'Yes (Filename match)';
    }
    
    // Check metadata in the raw file content (binary scan)
    try {
        $size = filesize($file_to_check);
        if ($size === 0) {
            return 'No';
        }
        
        $fp = fopen($file_to_check, 'rb');
        if (!$fp) {
            return 'No';
        }
        
        // Read first 30KB and last 10KB
        $data = '';
        if ($size < 40000) {
            $data = fread($fp, $size);
        } else {
            $data = fread($fp, 30000);
            fseek($fp, $size - 10000);
            $data .= fread($fp, 10000);
        }
        fclose($fp);
        
        $data_lower = strtolower($data);
        
        $ai_keywords = [
            'midjourney', 'dall-e', 'dalle', 'stable diffusion', 'stablediffusion', 
            'firefly', 'algorithmic', 'generative', 'artificial intelligence', 'ai-generated'
        ];
        
        $matches = [];
        foreach ($ai_keywords as $kw) {
            if (strpos($data_lower, $kw) !== false) {
                $matches[] = $kw;
            }
        }
        
        if (!empty($matches)) {
            return 'Yes (Signature: ' . implode(', ', $matches) . ')';
        }
    } catch (Exception $e) {
        // Safe fallback
    }
    
    return 'No';
}

// Helper to extract Flickr ID prefix from a filename
function get_flickr_id_prefix($filename) {
    if (preg_match('/^(\d+)/', $filename, $matches)) {
        return $matches[1];
    }
    return null;
}

// Helper to fetch and cache images from the live original project page
function get_live_project_page_images($client_slug) {
    $cache_dir = '/Users/bryanpaul/Local Sites/astro-e3es/scratch/live_project_pages_cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $cache_file = $cache_dir . '/' . $client_slug . '.html';
    $html = '';
    
    if (file_exists($cache_file)) {
        $html = file_get_contents($cache_file);
    } else {
        $url = "https://www.e3es.com/projects-item/{$client_slug}/";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $html = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status === 200 && !empty($html)) {
            file_put_contents($cache_file, $html);
        } else {
            return [];
        }
    }
    
    $images = [];
    if (preg_match_all('/src=["\']([^"\']+\.(?:jpg|jpeg|png|gif|webp|svg|jfif))["\']/i', $html, $matches)) {
        foreach ($matches[1] as $img_url) {
            if (strpos($img_url, 'wp-content/uploads') !== false) {
                $images[] = $img_url;
            }
        }
    }
    
    return array_unique($images);
}

// Helper to recursively traverse blocks for images/videos
function extract_assets_from_blocks($blocks, &$assets) {
    foreach ($blocks as $block) {
        if (empty($block['blockName'])) {
            continue;
        }
        
        if ($block['blockName'] === 'e3es/intro-banner') {
            if (!empty($block['attrs']['bgImageUrl'])) {
                $assets[] = [
                    'type' => 'Image',
                    'location' => 'Intro Banner Background',
                    'url' => $block['attrs']['bgImageUrl']
                ];
            }
            if (!empty($block['attrs']['clientLogoUrl'])) {
                $assets[] = [
                    'type' => 'Image',
                    'location' => 'Intro Banner Logo',
                    'url' => $block['attrs']['clientLogoUrl']
                ];
            }
        }
        
        elseif ($block['blockName'] === 'e3es/project') {
            if (!empty($block['attrs']['heroImageUrl'])) {
                $assets[] = [
                    'type' => 'Image',
                    'location' => 'Project Hero',
                    'url' => $block['attrs']['heroImageUrl']
                ];
            }
        }
        
        elseif ($block['blockName'] === 'core/image') {
            if (!empty($block['attrs']['url'])) {
                $assets[] = [
                    'type' => 'Image',
                    'location' => 'Content Image',
                    'url' => $block['attrs']['url']
                ];
            } else {
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $block['innerHTML'], $matches)) {
                    $assets[] = [
                        'type' => 'Image',
                        'location' => 'Content Image',
                        'url' => $matches[1]
                    ];
                }
            }
        }
        
        elseif ($block['blockName'] === 'e3es/video-embed') {
            if (!empty($block['attrs']['videoUrl'])) {
                $assets[] = [
                    'type' => 'Video',
                    'location' => 'Video Embed',
                    'url' => $block['attrs']['videoUrl']
                ];
            }
        }
        
        if (!empty($block['innerBlocks'])) {
            $is_gallery = ($block['blockName'] === 'core/gallery');
            $temp = [];
            extract_assets_from_blocks($block['innerBlocks'], $temp);
            foreach ($temp as &$item) {
                if ($is_gallery && $item['location'] === 'Content Image') {
                    $item['location'] = 'Gallery Image';
                }
                $assets[] = $item;
            }
        }
    }
}

foreach ($query->posts as $post) {
    $client_name = $post->post_title;
    $client_slug = $post->post_name;
    $is_featured = get_post_meta($post->ID, '_e3_client_show_in_index', true) === '1' ? 'Yes' : 'No';
    
    $assets = [];
    
    // 1. Featured Image
    $thumb_id = get_post_meta($post->ID, '_thumbnail_id', true);
    if ($thumb_id) {
        $thumb_url = wp_get_attachment_url($thumb_id);
        if ($thumb_url) {
            $assets[] = [
                'type' => 'Image',
                'location' => 'Featured Image',
                'url' => $thumb_url
            ];
        }
    }
    
    // 2. Client Logo
    $logo_url = get_post_meta($post->ID, '_e3_client_logo', true);
    if ($logo_url) {
        $assets[] = [
            'type' => 'Image',
            'location' => 'Client Logo',
            'url' => $logo_url
        ];
    }
    
    // 3. Blocks content
    if (!empty($post->post_content)) {
        $blocks = parse_blocks($post->post_content);
        extract_assets_from_blocks($blocks, $assets);
    }
    
    // De-duplicate assets by URL
    $unique_assets = [];
    foreach ($assets as $asset) {
        $unique_assets[$asset['url']] = $asset;
    }
    
    foreach ($unique_assets as $asset) {
        $type = $asset['type'];
        $location = $asset['location'];
        $url = $asset['url'];
        
        $width = 'N/A';
        $height = 'N/A';
        $size_kb = 'N/A';
        $sufficient = 'N/A';
        
        $source = 'Unknown';
        $explanation = '';
        $confidence = 'Low';
        
        $norm_client = normalize_client_name_audit($client_name);
        
        if ($type === 'Image') {
            // Get dimensions
            $info = get_image_details($url);
            if ($info['width'] !== 'Unknown') {
                $width = $info['width'];
                $height = $info['height'];
                $size_kb = $info['size_kb'];
                $sufficient = ($width >= 1900) ? 'Yes' : 'No';
            }
            
            $filename = basename($url);
            $norm_img = normalize_filename_audit($filename);
            
            // Check placeholders
            if (strpos($filename, 'taj-mahal-placeholder') !== false) {
                $source = 'Placeholder';
                $explanation = 'Unrelated Taj Mahal placeholder image';
                $confidence = 'Low';
            } elseif (strpos($filename, 'placeholder') !== false) {
                $source = 'Placeholder';
                $explanation = 'Generic placeholder image';
                $confidence = 'Low';
            } else {
                // Check if it is a direct document extraction
                if (strpos($filename, 'extracted-docx-') !== false || strpos($filename, 'extracted-pdf-') !== false || strpos($filename, 'ntmc-cropped-photo') !== false || strpos($filename, 'best_') !== false) {
                    $source = 'Document Extraction';
                    $explanation = "Extracted directly from the client's official case study Word (.docx) document or Reference Sheet PDF.";
                    $confidence = 'High';
                }
                
                // 1. Check live project page images for exact, Flickr ID, or fuzzy match
                else {
                    $live_images = get_live_project_page_images($client_slug);
                $matched_live_image = null;
                $match_type = '';
                $local_flickr_id = get_flickr_id_prefix($filename);
                
                foreach ($live_images as $live_img_url) {
                    $live_filename = basename($live_img_url);
                    $norm_live = normalize_filename_audit($live_filename);
                    
                    // Exact or normalized filename match
                    if ($norm_img === $norm_live) {
                        $matched_live_image = $live_img_url;
                        $match_type = 'exact';
                        break;
                    }
                    
                    // Flickr ID match
                    if ($local_flickr_id) {
                        $live_flickr_id = get_flickr_id_prefix($live_filename);
                        if ($live_flickr_id && $local_flickr_id === $live_flickr_id) {
                            $matched_live_image = $live_img_url;
                            $match_type = 'flickr_id';
                            break;
                        }
                    }
                    
                    // Fuzzy match (one contains the other)
                    if (strlen($norm_img) > 10 && strlen($norm_live) > 10) {
                        if (strpos($norm_img, $norm_live) !== false || strpos($norm_live, $norm_img) !== false) {
                            $matched_live_image = $live_img_url;
                            $match_type = 'fuzzy';
                            break;
                        }
                    }
                }
                
                if ($matched_live_image) {
                    $source = "Original Live Page";
                    $confidence = "High";
                    if ($match_type === 'exact') {
                        $explanation = "Exact match with image on original project page: '" . basename($matched_live_image) . "'";
                    } elseif ($match_type === 'flickr_id') {
                        $explanation = "Matches Flickr Photo ID (" . $local_flickr_id . ") on original project page: '" . basename($matched_live_image) . "'";
                    } else {
                        $explanation = "Fuzzy filename similarity match with image on original project page: '" . basename($matched_live_image) . "'";
                    }
                }
                
                // 2. Check Flickr map
                elseif (isset($flickr_map[$norm_img])) {
                    $folder = $flickr_map[$norm_img];
                    $norm_folder = normalize_filename_audit($folder);
                    $source = "Flickr Folder: '$folder'";
                    
                    if (strpos($norm_folder, $norm_client) !== false || strpos($norm_client, $norm_folder) !== false) {
                        $confidence = 'High';
                        $explanation = "Matches filename inside matching Flickr folder: '$folder'";
                    } else {
                        $confidence = 'Low';
                        $explanation = "Found in Flickr folder, but belongs to a different client: '$folder'";
                    }
                }
                
                // Check Legacy HTML map
                elseif (isset($html_img_map[$norm_img])) {
                    $pages = $html_img_map[$norm_img];
                    $source = "Legacy HTML: '" . implode(', ', $pages) . "'";
                    
                    $matched = false;
                    foreach ($pages as $p) {
                        if (strpos($p, $client_slug) !== false || strpos($client_slug, str_replace('.html', '', $p)) !== false) {
                            $matched = true;
                            break;
                        }
                    }
                    
                    if ($matched) {
                        $confidence = 'High';
                        $explanation = "Filename matches image found on original legacy HTML page: '" . implode(', ', $pages) . "'";
                    } else {
                        $confidence = 'Medium';
                        $explanation = "Image found in legacy HTML files, but on a different client's page: '" . implode(', ', $pages) . "'";
                    }
                }
                
                // Check name match fallback
                elseif (strpos($norm_img, $norm_client) !== false) {
                    $source = 'Filename Match';
                    $explanation = "Filename contains client name: '$filename'";
                    $confidence = 'High';
                }
                
                else {
                    $source = 'Unknown';
                    $explanation = 'No reference found in Flickr downloads, legacy HTML, or name parameters';
                    $confidence = 'Low';
                }
                }

                // Post-check for low confidence to identify actual association
                if ($confidence === 'Low') {
                    $assoc_details = get_actual_association_details($filename, $client_slug);
                    if ($assoc_details) {
                        $source = 'Mismatched Fallback';
                        $explanation = $assoc_details['explanation'];
                    }
                }
            }
        }
        
        elseif ($type === 'Video') {
            // Vimeo matching
            if (preg_match('/(?:video\/)?([0-9]+)/', $url, $vid_matches)) {
                $vimeo_id = $vid_matches[1];
                
                if (isset($html_video_map[$vimeo_id])) {
                    $pages = $html_video_map[$vimeo_id];
                    $source = "Legacy HTML: '" . implode(', ', $pages) . "'";
                    
                    $matched = false;
                    foreach ($pages as $p) {
                        if (strpos($p, $client_slug) !== false || strpos($client_slug, str_replace('.html', '', $p)) !== false) {
                            $matched = true;
                            break;
                        }
                    }
                    
                    if ($matched) {
                        $confidence = 'High';
                        $explanation = "Vimeo ID $vimeo_id matches video embed on original legacy page: '" . implode(', ', $pages) . "'";
                    } else {
                        $confidence = 'Medium';
                        $explanation = "Vimeo ID $vimeo_id found on a different legacy HTML page: '" . implode(', ', $pages) . "'";
                    }
                } else {
                    $source = 'Unknown';
                    $explanation = 'Vimeo video embed with ID ' . $vimeo_id . ', but no matching embed in legacy HTML files';
                    $confidence = 'Medium';
                }
            } else {
                $source = 'Unknown';
                $explanation = 'Non-Vimeo or unparseable video URL: ' . $url;
                $confidence = 'Low';
            }
        }
        
        $is_ai = 'No';
        if ($type === 'Image') {
            $is_ai = check_image_ai_signatures($url);
        } else {
            $is_ai = 'N/A';
        }
        
        $resolution = ($width !== 'N/A' && $width !== 'Unknown') ? "{$width}x{$height}" : $width;
        
        $audit_rows[] = [
            'Client Name' => $client_name,
            'Is Featured Client' => $is_featured,
            'Client Page Link' => "http://astro-e3es.localhost:8080/clients/{$client_slug}/",
            'Asset Type' => $type,
            'Asset URL / Source' => $url,
            'Page Location / Type' => $location,
            'Resolution' => $resolution,
            'File Size (KB)' => $size_kb,
            'Is Resolution Sufficient' => $sufficient,
            'Is AI Generated / Assisted' => $is_ai,
            'Association Source' => $source,
            'Association Explanation' => $explanation,
            'Confidence Level' => $confidence
        ];
    }
}

// 4. Output CSV
echo "Writing CSV audit report to: $output_csv_path...\n";
$fp = fopen($output_csv_path, 'w');

// UTF-8 BOM for Excel compatibility
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
fputcsv($fp, array_keys($audit_rows[0]));

// Write rows
foreach ($audit_rows as $row) {
    fputcsv($fp, array_values($row));
}

fclose($fp);

echo "🏁 Audit complete! Generated CSV with " . count($audit_rows) . " asset records.\n\n";
