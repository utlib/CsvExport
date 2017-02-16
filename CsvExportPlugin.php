<?php
class CsvExportPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_filters = array(
        'response_contexts',
        'action_contexts',
    );
    
    /**
     * HOOK respond_contexts: Adds the response MIME types for the CSV export format
     * @param array $contexts
     * @return array
     */
    public function filterResponseContexts($contexts) {
        $contexts['csv'] = array(
            'suffix' => 'csv',
            'headers' => array(
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=export.csv'
            ),
        );
        return $contexts;
    }
    
    /**
     * HOOK action_contexts: Add CSV as an export on Items browse/show and Collections show actions
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
?>
