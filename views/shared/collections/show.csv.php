<?php
printCsvExport(get_db()->getTable('Item')->findBy(array('collection' => $collection->id)));
