<?php

/**
 * The main plugin configuration form.
 * @package CsvExport
 * @subpackage Form
 */
class CsvExport_Form_Config extends Omeka_Form {
    /**
     * Sets up elements for this form.
     */
    public function init() {
        // Top-level parent
        parent::init();
        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
        // Canonical file URL?
        $this->addElement('checkbox', 'canonical_file_urls', array(
            'label' => __('Export canonical file URLs?'),
            'description' => __('Whether to cite canonical file URLs (checked) or local Omeka original URLs (unchecked) for items in exported CSV files.'),
            'value' => get_option('csv_export_canonical_file_urls'),
        ));
        $this->addElement('text', 'separator_character', array(
            'label' => __('CSV separator character'),
            'description' => __('Set the character that will be used to separate fields in exported CSV files.'),
            'value' => get_option('csv_export_separator_character'),
        ));
        $this->addElement('text', 'separator_character_internal', array(
            'label' => __('Internal CSV separator character'),
            'description' => __('Set the character that will be used to separate elements of the same field in exported CSV files.'),
            'value' => get_option('csv_export_separator_character_internal'),
        ));
    }
}
