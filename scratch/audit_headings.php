<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$posts = get_posts(['post_type' => 'clients', 'posts_per_page' => -1]);
$report = [];

function check_headings($blocks, $post_slug, &$list) {
    foreach ($blocks as $b) {
        if ($b['blockName'] === 'e3es/project') {
            $proj_title = isset($b['attrs']['title']) ? $b['attrs']['title'] : 'Untitled Project';
            $list[$proj_title] = [];
            foreach ($b['innerBlocks'] as $ib) {
                if ($ib['blockName'] === 'core/heading') {
                    $level = isset($ib['attrs']['level']) ? $ib['attrs']['level'] : 2; // Gutenberg default is 2 if not set
                    $text = strip_tags($ib['innerHTML']);
                    $list[$proj_title][] = [
                        'text' => trim($text),
                        'level' => $level
                    ];
                }
            }
        }
        if (!empty($b['innerBlocks'])) {
            check_headings($b['innerBlocks'], $post_slug, $list);
        }
    }
}

foreach ($posts as $p) {
    $blocks = parse_blocks($p->post_content);
    $headings_by_project = [];
    check_headings($blocks, $p->post_name, $headings_by_project);
    if (!empty($headings_by_project)) {
        $report[$p->post_name] = $headings_by_project;
    }
}

file_put_contents('/Users/bryanpaul/Local Sites/astro-e3es/scratch/headings_audit.json', json_encode($report, JSON_PRETTY_PRINT));
echo "Audited " . count($report) . " posts. Saved to headings_audit.json\n";
