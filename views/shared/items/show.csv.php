<?php
$db = get_db();

// Find all Dublin Core elements
$dublinCoreElementSet = $db->getTable('ElementSet')->findByName('Dublin Core');
$dublinCoreElements = $dublinCoreElementSet->getElements();

// Find all item-specific elements
$itemTypeElements = $item->getItemTypeElements();

// Print header containing all elements
$elements = array_merge($dublinCoreElements, $itemTypeElements);
echo join(array_map(function($element) {
    return 'Dublin Core:' . csvEscape($element->name);
}, $elements), ',');
echo ",tags,file\n";

// Build element text info
$result = array();
$elementTexts = $db->getTable('ElementText')->findByRecord($item);
foreach ($elements as $element) {
    // Search for corresponding element text
    $nullText = true;
    foreach ($elementTexts as $elementText) {
        if ($elementText->element_id == $element->id) {
            $result[] = csvEscape($elementText->text);
            $nullText = false;
            break;
        }
    }
    // Add placeholder if missing
    if ($nullText) {
        $result[] = '';
    }
}
// Build tags info
$tags = $item->getTags();
$tagNames = array();
foreach ($tags as $tag) {
    $tagNames[] = $tag->name;
}
$result[] = csvEscape(join($tagNames, ','));
// Build file URL info
$files = $item->getFiles();
$fileUrls = array();
foreach ($files as $file) {
    // Use original file name if it is a URL, otherwise use the web path
    $fileUrls[] = (preg_match('/^http[s]?:/', $file->original_filename)) ? $file->original_filename : $file->getWebPath();
}
$result[] = csvEscape(join($fileUrls, ','));

// Done, print CSV line
echo join($result, ',');