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
    }
}
