<?php
// PHP Script to wrap project details block inside a wp:e3es/project block for Rio Hondo ISD (post ID 1733).

$post_id = 1733;
$post = get_post($post_id);

if (!$post) {
    echo "Error: Post $post_id not found.\n";
    exit(1);
}

$content = $post->post_content;

// Locate the e3es/project-details block
$details_start = '<!-- wp:e3es/project-details';
$details_end = '<!-- /wp:e3es/project-details -->';

$pos_start = strpos($content, $details_start);
$pos_end = strpos($content, $details_end);

if ($pos_start !== false && $pos_end !== false) {
    $end_idx = $pos_end + strlen($details_end);
    $details_block = substr($content, $pos_start, $end_idx - $pos_start);
    
    // Construct the wrapped block
    $wrapped_project = "<!-- wp:e3es/project {\"sectionId\":\"project-details\",\"title\":\"Energy Efficiency & Facility Upgrades\",\"heroImageUrl\":\"\"} -->\n";
    $wrapped_project .= "<div class=\"wp-block-e3es-project project-section\" id=\"project-details\" style=\"--hero-img:none\"><div class=\"project-section__header\"><div class=\"project-section__info\"><span class=";
    // Handle quotes carefully in PHP string
    $wrapped_project .= '"project-section__eyebrow">Project 1</span><h3 class="project-section__title">Energy Efficiency & Facility Upgrades</h3></div></div><div class="project-section__content">';
    $wrapped_project .= "\n" . $details_block . "\n";
    $wrapped_project .= "</div></div>\n<!-- /wp:e3es/project -->";
    
    // Replace the unwrapped details block with the wrapped one
    $new_content = substr_replace($content, $wrapped_project, $pos_start, $end_idx - $pos_start);
    
    wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $new_content
    ));
    
    echo "Successfully wrapped project details for Rio Hondo ISD.\n";
} else {
    echo "Could not find project-details block in Rio Hondo ISD content.\n";
}
?>
