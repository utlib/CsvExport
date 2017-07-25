<?php
class CsvExport_ItemAttachUtil {
    /**
     * Return a list of Items, including the provided Item and its annotations.
     * @param Item $item
     * @return Item[]
     */
    public static function getThisAndAnnotations($item) {
        // If this not an annotation-type Item
        if ($item->item_type_id != get_option('iiifitems_annotation_item_type')) {
            // Return it and all attached annotations
            return array_merge(array($item), CsvExport_IiifItemsUtil::findAnnotationItemsUnder($item));
        // End: If this not an annotation-type Item
        }
        // Otherwise, return all subitems
        return array($item);
    }
}
