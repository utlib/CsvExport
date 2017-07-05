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
        // For each sub-item under the collection
        $items = get_db()->getTable('Item')->findBy(array('collection' => $collection->id));
        foreach ($items as $item) {
            // Recurse and join to subitem list
            foreach(CsvExport_ItemAttachUtil::getThisAndAnnotations($item) as $subItem) {
                $subItems[] = $subItem;
            }
        // End: For each sub-item under the collection
        }
        // Return all subitems
        return $subItems;
    }
}
