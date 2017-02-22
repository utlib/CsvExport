<?php
class CsvExport_CollectionAttachUtil {
    /**
     * Return a recursive list of all items under this collection
     * @param Collection $collection
     * @return Item[]
     */
    public static function getSubitems($collection) {
        // Set up holder for subitems
        $subItems = array();
        // If this is a Collection-type collection
        if (CsvExport_IiifItemsUtil::isCollection($collection)) {
            // For each sub-collection
            foreach (CsvExport_IiifItemsUtil::findSubmembersFor($collection) as $subCollection) {
                // Recurse and join to subitem list
                foreach (CsvExport_CollectionAttachUtil::getSubitems($subCollection) as $subItem) {
                    $subItems[] = $subItem;
                }
            // End: For each sub-collection
            }
        // Otherwise, if it this is a Manifest-type collection
        } else {
            // For each sub-item under the collection
            $items = get_db()->getTable('Item')->findBy(array('collection' => $collection->id));
            foreach ($items as $item) {
                // Recurse and join to subitem list
                foreach(CsvExport_ItemAttachUtil::getThisAndAnnotations($item) as $subItem) {
                    $subItems[] = $subItem;
                }
            // End: For each sub-collection under the collection
            }
        // End: If this is a Collection-type collection
        }
        // Return all subitems
        return $subItems;
    }
}
