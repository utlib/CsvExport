<?php

function csvEscape($str) {
    if (strpbrk($str, "\n,\"") === FALSE) {
        return $str;
    } else {
        return '"' . str_replace('"', '""', $str) . '"';
    } 
}

function getOrderedElements() {
    $elements = array();
    $elementSets = get_db()->getTable('ElementSet')->findAll();
    foreach ($elementSets as $elementSet) {
        $select = get_db()->getTable('Element')->getSelect()
                ->where('element_set_id = ?', array($elementSet->id))
                ->order('order ASC');
        foreach (get_db()->getTable('Element')->fetchObjects($select) as $element) {
            $elements[] = $element;
        }
    }
    return $elements;
}

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

function printCsvExport($items) {
    // Get all elements as columns
    $elements = getOrderedElements();

    // Header
    foreach($elements as $element) {
        echo csvEscape(($element->element_set_id === null) ? $element->name : "{$element->getElementSet()->name}:{$element->name}");
        echo ',';
    }
    echo "tags,file,itemType,collection,public,featured\n";

    // Body
    foreach ($items as $item) {
        echo join(getCsvRow($item, $elements), ',');
        echo "\n";
    }
}