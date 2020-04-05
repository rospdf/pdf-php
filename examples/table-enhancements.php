<?php
date_default_timezone_set('UTC');

include_once '../src/Cezpdf.php';
$pdf = new CezPDF('a4');

$pdf->selectFont('Helvetica');

// some general data used for table output
$data = [
    ['num' => 1, 'numColor' => [1,1,1], 'name' => 'gandalf', 'type' => 'wizard', 'typeFill' => [0, 1, 0], 'typeColor' => [1,0,0]],
    ['num' => 2, 'numColor' => [1,1,1], 'name' => 'bilbo', 'type' => 'hobbit'],
    ['num' => 3, 'numColor' => [1,1,1], 'name' => 'frodo', 'nameColor' => [0,0,1], 'type' => 'hobbit'],
    ['num' => 4, 'numColor' => [1,1,1], 'name' => str_repeat('saruman ', 300), 'type' => str_repeat('bad dude', 300), 'typeFill' => [197/255, 213/255, 203/255], 'typeColor' => [159/255, 168/255, 163/255]],
    ['num' => 5, 'numColor' => [1,1,1], 'name' => 'sauron', 'type' => 'really bad dude', 'typeFill' => [0, 0, 0], 'typeColor' => [1, 1, 1]],
];

$cols = ['num' => 'No', 'type' => 'Type', 'name' => '<i>Alias</i>'];

$conf = [
    'evenColumns' => 2,
    'evenColumnsMin' => 40,
    'maxWidth' => 350,
    'shadeHeadingCol' => [23/255, 62/255, 67/255],
    'textCol' => [1,1,1],
    'shaded' => 2,
    'shadeCol' => [0.95, 0.95, 0.95],
    'shadeCol2' => [0.75, 0.75, 0.75],
    'xPos' => 'right',
    'xOrientation' => 'left',
    'gridlines' => 31,
    'cols' => [
        'num' => ['bgcolor' => [90/255, 118/255, 112/255]]
    ] // global background color for 'num' column
];

$pdf->ezTable($data, $cols, '', $conf);

if (isset($_GET['d']) && $_GET['d']) {
    echo "<pre>" . $pdf->ezOutput(true) . "</pre>";
} else {
    $pdf->ezStream();
}
