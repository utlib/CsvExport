<?php

/**
 * Escapes a string for inclusion as a CSV entry.
 * <p>
 * Characters escaped include:
 * <ul>
 * <li>Comma</li>
 * <li>Double Quote</li>
 * <li>Newline</li>
 * </ul>
 * Strings containing these will be surrounded in double quotes. The double quote character is escaped to "" (two double quotes).
 * </p>
 * @param string $str
 * @return string
 */
function csvEscape($str) {
    // No need to escape
    if (strpbrk($str, "\n,\"") === FALSE) {
        return $str;
    // Need to escape --- repeat the double quotes and surround in double quotes
    } else {
        return '"' . str_replace('"', '""', $str) . '"';
    } 
}

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
 * Get an array of CSV-escaped metadata and other properties for an item, given the elements in order.
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
                $row[] = csvEscape($elementText->text);
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
    $row[] = csvEscape(join($tagNames, ','));
    // Files
    $files = $item->getFiles();
    $fileUrls = array();
    foreach ($files as $file) {
        // Use original file name if it is a URL, otherwise use the web path
        $fileUrls[] = (preg_match('/^http[s]?:/', $file->original_filename)) ? $file->original_filename : $file->getWebPath();
    }
    $row[] = csvEscape(join($fileUrls, ','));
    // Item type
    $row[] = ($item->item_type_id === null) ? '' : csvEscape(($item->getItemType()->name));
    // Collection
    $row[] = ($item->collection_id === null) ? '' : csvEscape(metadata($item->getCollection(), array('Dublin Core', 'Title'), array('no_escape' => true, 'no_filter' => true)));
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
    
    // Fix for UTF-8: Byte-order mark
    echo "\xEF\xBB\xBF";

    // Header: Metadata
    // Metadata that belong to an element set are labelled "<Element Set Name>:<Element Name>"
    foreach($elements as $element) {
        echo csvEscape(($element->element_set_id === null) ? $element->name : "{$element->getElementSet()->name}:{$element->name}");
        echo ',';
    }
    // Header: Property tail
    echo "tags,file,itemType,collection,public,featured\n";

    // Body
    foreach ($items as $item) {
        echo join(getCsvRow($item, $elements), ',');
        echo "\n";
    }
}