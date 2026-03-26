<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('odalar.xlsx');
$sheet = $spreadsheet->getActiveSheet();
$rows = [];
foreach ($sheet->getRowIterator(2) as $row) {
    $cells = $row->getCellIterator();
    $cells->setIterateOnlyExistingCells(false);
    $data = [];
    foreach ($cells as $cell) {
        $data[] = $cell->getValue();
    }
    if (!empty($data[0])) {
        $rows[] = ['oda_no' => $data[0], 'konum' => $data[1]];
    }
}
echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
