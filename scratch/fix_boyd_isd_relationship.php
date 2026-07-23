<?php
/**
 * Fix Boyd ISD Relationship Paragraph Position
 * Moves the relationship paragraph above the first project block.
 */

$wp_load = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';
if (!file_exists($wp_load)) {
    die("Cannot find wp-load.php at: $wp_load\n");
}
require_once $wp_load;

echo "🔧 Fixing Boyd ISD relationship paragraph...\n";

$p = get_page_by_path('boyd-isd', OBJECT, 'clients');
if (!$p) {
    die("Cannot find Boyd ISD client post.\n");
}

$content = $p->post_content;

// Parse the content blocks
$blocks = parse_blocks($content);

// Let's print out the block structure to locate the paragraph
function print_block_summary($blocks, $depth = 0) {
    foreach ($blocks as $idx => $b) {
        if (empty($b['blockName'])) continue;
        echo str_repeat("  ", $depth) . "- [$idx] {$b['blockName']}\n";
        if (!empty($b['innerBlocks'])) {
            print_block_summary($b['innerBlocks'], $depth + 1);
        }
    }
}
print_block_summary($blocks);

// Find the first e3es/project block
$first_project_idx = -1;
foreach ($blocks as $idx => $b) {
    if ($b['blockName'] === 'e3es/project') {
        $first_project_idx = $idx;
        break;
    }
}

if ($first_project_idx !== -1) {
    $project_block = &$blocks[$first_project_idx];
    
    // Find paragraph inside project inner blocks
    $para_idx = -1;
    foreach ($project_block['innerBlocks'] as $idx => $ib) {
        if ($ib['blockName'] === 'core/paragraph') {
            $para_idx = $idx;
            break;
        }
    }
    
    if ($para_idx !== -1) {
        $para_block = $project_block['innerBlocks'][$para_idx];
        
        // Remove it from project inner blocks
        array_splice($project_block['innerBlocks'], $para_idx, 1);
        
        // Insert it before the project block in the root blocks list
        array_splice($blocks, $first_project_idx, 0, [$para_block]);
        
        echo "Successfully moved paragraph block above project block!\n";
        
        // Save the updated blocks
        $new_content = serialize_blocks($blocks);
        
        wp_update_post([
            'ID' => $p->ID,
            'post_content' => $new_content
        ]);
        
        echo "Boyd ISD post content updated!\n";
    } else {
        echo "Could not find paragraph inside first project block.\n";
    }
} else {
    echo "Could not find any project block in Boyd ISD.\n";
}
