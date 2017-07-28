<?php
class CsvExportPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'config',
        'config_form',
        'upgrade',
    );
    
    protected $_filters = array(
        'response_contexts',
        'action_contexts',
    );
    
    /**
     * HOOK install: Set initial plugin configurations.
     */
    public function hookInstall() {
        set_option('csv_export_canonical_file_urls', 0);
    }
    
    /**
     * HOOK uninstall: Remove plugin configuration entries.
     */
    public function hookUninstall() {
        delete_option('csv_export_canonical_file_urls');
    }
    
    /**
     * HOOK config: Process configuration submissions.
     * @param array $args
     */
    public function hookConfig($args) {
        $post = $args['post'];
        set_option('csv_export_canonical_file_urls', $post['canonical_file_urls']);
    }
    
    /**
     * HOOK config_form: Render the plugin's configuration form.
     */
    public function hookConfigForm() {
        $form = new CsvExport_Form_Config();
        $form->removeDecorator('Form');
        $form->removeDecorator('Zend_Form_Decorator_Form');
        echo $form;
    }
    
    /**
     * HOOK upgrade: Run migrations.
     * @param array $args
     */
    public function hookUpgrade($args) {
        // Get the previous version and the current version numbers
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        $doMigrate = false;

        // Sort all migrations by version number
        $versions = array();
        foreach (glob(dirname(__FILE__) . '/libraries/CsvExport/Migration/*.php') as $migrationFile) {
            $className = 'CsvExport_Migration_' . basename($migrationFile, '.php');
            include $migrationFile;
            $versions[$className::$version] = new $className();
        }
        uksort($versions, 'version_compare');

        // Run migrations in ascending version number order
        // Start with the version after the previous version and work upwards
        foreach ($versions as $version => $migration) {
            if (version_compare($version, $oldVersion, '>')) {
                $doMigrate = true;
            }
            if ($doMigrate) {
                $migration->up();
                if (version_compare($version, $newVersion, '>')) {
                    break;
                }
            }
        }
    }
    
    /**
     * FILTER respond_contexts: Adds the response MIME types for the CSV export format
     * @param array $contexts
     * @return array
     */
    public function filterResponseContexts($contexts) {
        $contexts['csv'] = array(
            'suffix' => 'csv',
            'headers' => array(
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename=export.csv'
            ),
        );
        return $contexts;
    }
    
    /**
     * FILTER action_contexts: Add CSV as an export on Items browse/show and Collections show actions
     * @param array $contexts
     * @param array $args
     * @return array
     */
    public function filterActionContexts($contexts, $args) {
        // Browse and show views for Items
        if ($args['controller'] instanceof ItemsController) {
            $contexts['browse'][] = 'csv';
            $contexts['show'][] = 'csv';
        // Show view for Collections
        } elseif ($args['controller'] instanceof CollectionsController) {
            $contexts['show'][] = 'csv';
        }
        return $contexts;
    }
}

// Plugin-wide setup
if (!defined('CSV_EXPORT_PLUGIN_DIR')) {
    define('CSV_EXPORT_PLUGIN_DIR', dirname(__FILE__));
}
require_once(CSV_EXPORT_PLUGIN_DIR . '/helpers/CsvExportFunctions.php');
