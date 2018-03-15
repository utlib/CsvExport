<?php
/**
 * Utility classes copied over from IIIF Items
 */
class CsvExport_IiifItemsUtil {
    /**
     * Return the text of the given IIIF Items metadata element.
     * @param Record $record
     * @param string $optionSlug The internal option slug for the IIIF Items metadata element
     * @return string
     */
    protected static function raw_iiif_metadata($record, $optionSlug) {
        if ($elementText = get_db()->getTable('ElementText')->findBySql('element_texts.element_id = ? AND element_texts.record_type = ? AND element_texts.record_id = ?', array(get_option($optionSlug), get_class($record), $record->id), true)) {
            return $elementText->text;
        } else {
            return '';
        }
    }
    
    /**
     * Find subsidiary IIIF collections and manifests under this Omeka collection.
     * @param Collection $collection
     * @return Collection[]
     */
    public static function findSubmembersFor($collection) {
        $myUuid = self::raw_iiif_metadata($collection, 'iiifitems_collection_uuid_element');
        if (!$myUuid) {
            return null;
        }
        $matches = get_db()->getTable('ElementText')->findBySql(
            'element_texts.element_id = ? AND element_texts.text = ?',
            array(get_option('iiifitems_collection_parent_element'), $myUuid)
        );
        $results = array();
        foreach ($matches as $match) {
            $candidate = get_record_by_id($match->record_type, $match->record_id);
            $results[] = $candidate;
        }
        return $results;
    }
    
    /**
     * Return whether this collection is set to the Collection type.
     * @param Collection $collection
     * @return boolean
     */
    public static function isCollection($collection) {
        try {
            $iiifMetadataSlug = 'iiifitems_collection_type_element';
            $iiifTypeText = self::raw_iiif_metadata($collection, $iiifMetadataSlug);
            if ($iiifTypeText) {
                return $iiifTypeText == 'Collection';
            }
        } catch (Exception $ex) {
        }
        return false;
    }
    
    /**
     * Return an array of annotations for a non-annotation item, as Item records.
     * @param type $item
     * @return array
     */
    public static function findAnnotationItemsUnder($item) {
        $elementTextTable = get_db()->getTable('ElementText');
        $uuid = self::raw_iiif_metadata($item, 'iiifitems_item_uuid_element');
        $onCanvasMatches = $elementTextTable->findBySql("element_texts.record_type = ? AND element_texts.element_id = ? AND element_texts.text = ?", array(
            'Item',
            get_option('iiifitems_annotation_on_element'),
            $uuid,
        ));
        $annoItems = array();
        foreach ($onCanvasMatches as $onCanvasMatch) {
            $annoItems[] = get_record_by_id('Item', $onCanvasMatch->record_id);
        }
        return $annoItems;
    }
}
