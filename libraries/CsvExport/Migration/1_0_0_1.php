<?php

/**
 * Migration 1.0.0.1: Add option for canonical or local paths in exported file URLs.
 * @package CsvExport
 * @subpackage Migration
 */
class CsvExport_Migration_1_0_0_1 extends CsvExport_BaseMigration {
    public static $version = '1.0.0.1';
    
    /**
     * Migrate up
     */
    public function up() {
        set_option('csv_export_canonical_file_urls', 0);
    }
}
