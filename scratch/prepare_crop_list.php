<?php
require '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-load.php';

$ref_dir = '/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Reference Sheets';
$ref_folders = [];
if (file_exists($ref_dir)) {
    $dir = new DirectoryIterator($ref_dir);
    foreach ($dir as $fileinfo) {
        if ($fileinfo->isDir() && !$fileinfo->isDot()) {
            $ref_folders[strtolower($fileinfo->getFilename())] = $fileinfo->getPathname();
        }
    }
}

function get_ref_folder_path($title, $ref_dir, $ref_folders) {
    $clean_title = str_replace(['ISD', 'CISD', 'CSD', 'ESD', 'CCSD', ' - ', '-'], '', $title);
    $clean_title = preg_replace('/\s+/', ' ', $clean_title);
    $clean_title = trim($clean_title);
    
    $words = explode(' ', $clean_title);
    $best_match = null;
    $max_matches = 0;
    
    foreach ($ref_folders as $folder_name => $path) {
        $matches = 0;
        foreach ($words as $w) {
            if (empty($w)) continue;
            if (strpos($folder_name, strtolower($w)) !== false) {
                $matches++;
            }
        }
        if ($matches > $max_matches) {
            $max_matches = $matches;
            $best_match = $path;
        }
    }
    
    return $best_match;
}

$clients_raw = [
    ['Anderson-Shiro CISD', 'anderson-shiro-cisd'],
    ['Baird-ISD', 'baird-isd'],
    ['Ballinger ISD', 'ballinger-isd'],
    ['Banquete ISD', 'banquete-isd'],
    ['Bellevue ISD', 'bellevue-isd'],
    ['Big Sandy ISD', 'big-sandy-isd'],
    ['Bowie ISD', 'bowie-isd'],
    ['Brenham ISD', 'brenham-isd'],
    ['Brownsville ISD', 'brownsville-isd'],
    ['Caddo Mills ISD', 'caddo-mills-isd'],
    ['Castleberry ISD', 'castleberry-isd'],
    ['Cedar Hill ISD', 'cedar-hill-isd'],
    ['Chico ISD', 'chico-isd'],
    ['Cleveland ISD', 'cleveland-isd'],
    ['Columbia-Brazoria ISD', 'columbia-brazoria-isd'],
    ['Corsicana ISD', 'corsicana-isd'],
    ['DeSoto ISD', 'desoto-isd'],
    ['Eagle Pass ISD', 'eagle-pass-isd'],
    ['Edgewood ISD', 'edgewood-isd'],
    ['Edcouch-Elsa ISD', 'edcouch-elsa-isd'],
    ['Ennis ISD', 'ennis-isd'],
    ['Ferris ISD', 'ferris-isd'],
    ['Gainesville ISD', 'gainesville-isd'],
    ['Galena Park ISD', 'galena-park-isd'],
    ['Gruver ISD', 'gruver-isd'],
    ['Hardin County', 'hardin-county'],
    ['Hardin-Jefferson ISD', 'hardin-jefferson-isd'],
    ['Hawkins ISD', 'hawkins-isd'],
    ['Highland Park ISD', 'highland-park-isd'],
    ['Hondo ISD', 'hondo-isd'],
    ['Houston Community College', 'houston-community-college'],
    ['Idea Public Schools', 'idea-public-schools'],
    ['Ingram ISD', 'ingram-isd'],
    ['Italy ISD', 'italy-isd'],
    ['Jasper ISD', 'jasper-isd'],
    ['Katy ISD', 'katy-isd'],
    ['Kennedale ISD', 'kennedale-isd'],
    ['Lamesa ISD', 'lamesa-isd'],
    ['Lancaster ISD', 'lancaster-isd'],
    ['Liberty ISD', 'liberty-isd'],
    ['Llano ISD', 'llano-isd'],
    ['Lubbock ISD', 'lubbock-isd'],
    ['Lufkin ISD', 'lufkin-isd'],
    ['Lyford ISD', 'lyford-isd'],
    ['Manor ISD', 'manor-isd'],
    ['Marble Falls ISD', 'marble-falls-isd'],
    ['Mercedes ISD', 'mercedes-isd'],
    ['Mesquite ISD', 'mesquite-isd'],
    ['Moulton ISD', 'moulton-isd'],
    ['Nacogdoches ISD', 'nacogdoches-isd'],
    ['Needville ISD', 'needville-isd'],
    ['New Boston ISD', 'new-boston-isd'],
    ['Nocona ISD', 'nocona-isd'],
    ['Normangee ISD', 'normangee-isd'],
    ['Odem-Edroy ISD', 'odem-edroy-isd'],
    ['Pecos ISD', 'pecos-isd'],
    ['Pflugerville ISD', 'pflugerville-isd'],
    ['Pilot Point ISD', 'pilot-point-isd'],
    ['Poolville ISD', 'poolville-isd'],
    ['Port Neches-Groves ISD', 'port-neches-groves-isd'],
    ['Prosper ISD', 'prosper-isd'],
    ['Raymondville ISD', 'raymondville-isd'],
    ['Ricardo ISD', 'ricardo-isd'],
    ['Robstown ISD', 'robstown-isd'],
    ['Roscoe Collegiate ISD', 'roscoe-collegiate-isd'],
    ['Rusk ISD', 'rusk-isd'],
    ['Saint Jo ISD', 'saint-jo-isd'],
    ['San Benito CISD', 'san-benito-cisd'],
    ['San Angelo ISD', 'san-angelo-isd'],
    ['Sanger ISD', 'sanger-isd'],
    ['Santa Fe ISD', 'santa-fe-isd'],
    ['Silsbee ISD', 'silsbee-isd'],
    ['Skidmore-Tynan ISD', 'skidmore-tynan-isd'],
    ['Somerset ISD', 'somerset-isd'],
    ['Texas Facilities Commission', 'texas-facilities-commission'],
    ['Tom Bean ISD', 'tom-bean-isd'],
    ['Trenton ISD', 'trenton-isd'],
    ['Trinity ISD', 'trinity-isd'],
    ['Valley View ISD', 'valley-view-isd'],
    ['Vernon ISD', 'vernon-isd'],
    ['Waxahachie ISD', 'waxahachie-isd'],
    ['Weslaco ISD', 'weslaco-isd'],
    ['Woodville ISD', 'woodville-isd']
];

$res = [];
foreach ($clients_raw as $c) {
    list($title, $slug) = $c;
    $folder = get_ref_folder_path($title, $ref_dir, $ref_folders);
    if (!$folder) continue;
    
    // Find any layout JPEG in this folder (usually Jason Flowers - ... .jpg)
    $layout_files = glob("$folder/Jason Flowers - *.jpg");
    if (empty($layout_files)) {
        $layout_files = glob("$folder/*.jpg");
    }
    
    if (!empty($layout_files)) {
        $layout_file = $layout_files[0];
        // Only add if it's actually the layout sheet and not a cropped file
        if (strpos(basename($layout_file), 'cropped') === false) {
            $res[] = [
                'slug' => $slug,
                'title' => $title,
                'folder' => $folder,
                'layout_file' => $layout_file
            ];
        }
    }
}

echo json_encode($res);
