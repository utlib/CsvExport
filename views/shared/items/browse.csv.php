<?php
$db = get_db();

// Find all Dublin Core elements
$dublinCoreElementSet = $db->getTable('ElementSet')->findBy(array(
    'name' => 'Dublin Core'
))[0];
$dublinCoreElements = $dublinCoreElementSet->getElements();


// Print header containing all elements, plus file URLs
echo join(array_map(function($element) {
    return 'Dublin Core:' . csvEscape($element->name);
}, $dublinCoreElements), ',');
echo ",tags,file\n";

// For each item to print
foreach ($items as $item) {
    $lineInfo = array();
    // Build Dublin Core info
    foreach ($dublinCoreElements as $element) {
        $lineInfo[] = csvEscape(metadata($item, array('Dublin Core', $element->name)));
    }
    // Build tags info
    $tags = $item->getTags();
    $tagNames = array();
    foreach ($tags as $tag) {
        $tagNames[] = $tag->name;
    }
    $lineInfo[] = csvEscape(join($tagNames, ','));
    // Build file URL info
    $files = $item->getFiles();
    $fileUrls = array();
    foreach ($files as $file) {
        // Use original file name if it is a URL, otherwise use the web path
        $fileUrls[] = (preg_match('/^http[s]?:/', $file->original_filename)) ? $file->original_filename : $file->getWebPath();
    }
    $lineInfo[] = csvEscape(join($fileUrls, ','));
    // Print CSV line
    echo join($lineInfo, ',');
    echo "\n";
}
