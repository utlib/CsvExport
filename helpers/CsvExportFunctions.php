<?php

/**
 * Return a sorted list of Elements, grouped by the ElementSet they belong in.
 * @return Element[]
 */
function getOrderedElements() {
    $table = get_db()->getTable('Element');
    $select = $table->getSelect()->order('element_set_id ASC')->order('order ASC');
    return $table->fetchObjects($select);
}

/**
 * Get an array of metadata and other properties for an item, given the elements in order.
 * <p>
 * The entries are in the following order:
 * <ul>
 * <li>All metadata</li>
 * <li>Tags (comma separated)</li>
 * <li>Files (comma separated)</li>
 * <li>Item type</li>
 * <li>Collectin name</li>
 * <li>Public (0=private, 1=public)</li>
 * <li>Featured (0=not featured, 1=featured)</li>
 * </ul>
 * </p>
 * @param Item $item
 * @param Element[] $elements
 * @return string[]
 */
function getCsvRow($item, $elements) {
    $row = array();
    // Element texts
    $elementTexts = get_db()->getTable('ElementText')->findByRecord($item);
    foreach ($elements as $element) {
        $hasEmptyElementText = true;
        foreach ($elementTexts as $elementText) {
            if ($elementText->element_id === $element->id) {
                $row[] = $elementText->text;
                $hasEmptyElementText = false;
                break;
            }
        }
        if ($hasEmptyElementText) {
            $row[] = '';
        }
    }
    // Tail with tags, file, itemType, collection, public, featured
    // Tags
    $tags = $item->getTags();
    $tagNames = array();
    foreach ($tags as $tag) {
        $tagNames[] = $tag->name;
    }
    $row[] = join($tagNames, ',');
    // Files
    $files = $item->getFiles();
    $fileUrls = array();
    $useCanonical = get_option('csv_export_canonical_file_urls');
    foreach ($files as $file) {
        // Canonical: Use original file name if it is a URL, otherwise use the local web path
        if ($useCanonical) {
            $fileUrls[] = (preg_match('/^http[s]?:/', $file->original_filename)) ? $file->original_filename : $file->getWebPath();
        }
        // Not canonical: Always use the local web path
        else {
            $fileUrls[] = $file->getWebPath();
        }
    }
    $row[] = join($fileUrls, ',');
    // Item type
    $row[] = ($item->item_type_id === null) ? '' : ($item->getItemType()->name);
    // Collection
    $row[] = ($item->collection_id === null) ? '' : metadata($item->getCollection(), array('Dublin Core', 'Title'), array('no_escape' => true, 'no_filter' => true));
    // Public?
    $row[] = $item->public ? '1' : '0';
    // Featured?
    $row[] = $item->featured ? '1' : '0';
    // Done
    return $row;
}

/**
 * Echo the header, followed by the given Items in CSV row form.
 * @param Item[] $items
 */
function printCsvExport($items) {
    // Get all elements as columns
    $elements = getOrderedElements();
    
    // Start writing
    $f = fopen('php://output', 'w');
    
    // Fix for UTF-8: Byte-order mark
    fwrite($f, "\xEF\xBB\xBF");

    // Header: Metadata
    // Metadata that belong to an element set are labelled "<Element Set Name>:<Element Name>"
    $baseHeaderEntries = array();
    foreach($elements as $element) {
        $baseHeaderEntries[] = ($element->element_set_id === null) ? $element->name : "{$element->getElementSet()->name}:{$element->name}";
    }
    // Header: Property tail
    $headerEntries = array_merge($baseHeaderEntries, array('tags', 'file', 'itemType', 'collection', 'public', 'featured'));
    // Header: Write it in
    fputcsv($f, $headerEntries, ',', '"', "\0");

    // Body
    foreach ($items as $item) {
        fputcsv($f, getCsvRow($item, $elements), ',', '"', "\0");
    }
}