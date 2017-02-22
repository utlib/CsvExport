<?php
// Collect all items shown in order and add their sub-items
$allItems = array();
foreach ($items as $item) {
    $allItems[] = CsvExport_ItemAttachUtil::getThisAndAnnotations($item);
}
// Render CSV
printCsvExport($allItems);
