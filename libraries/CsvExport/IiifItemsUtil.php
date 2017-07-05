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
