<?php
class CsvExport_ItemAttachUtil {
    /**
     * Return a list of Items, including the provided Item and its annotations.
     * @param Item $item
     * @return Item[]
     */
    public static function getThisAndAnnotations($item) {
        // Set up holder for subitems (include the starting item)
        $subItems = array($item);
        // If this not an annotation-type Item
        if ($item->item_type_id != get_option('iiifitems_annotation_item_type')) {
            // Append all items with "on canvas" equal to this item's UUID
            foreach (IiifItems_AnnotationUtil::findAnnotationItemsUnder($item) as $subItem) {
                $subItems[] = $subItem;
            }
        // End: If this not an annotation-type Item
        }
        // Return all subitems
        return $subItems;
    }
}
