<?php

/**
 * Migration 1.1.0.1: Add option for the separator character.
 * @package CsvExport
 * @subpackage Migration
 */
class CsvExport_Migration_1_1_0_1 extends CsvExport_BaseMigration {
    public static $version = '1.1.0.1';

    /**
     * Migrate up
     */
    public function up() {
        set_option('csv_export_separator_character', ",");
    }
}
