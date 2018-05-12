<?php

error_reporting(E_ALL);
date_default_timezone_set('UTC');

include_once '../src/Cezpdf.php';
$pdf = new CezPDF('a4');

$pdf->selectFont('Helvetica');

// some general data used for table output
$data = array(
array('num' => 1, 'name' => 'gandalf', 'type' => 'wizard', 'typeFill' => array(0, 1, 0)), array('num' => 2, 'name' => 'bilbo', 'type' => 'hobbit', 'typeFill' => array(0.588235294118, 0.407843137255, 0)), array('num' => 3, 'name' => 'frodo', 'type' => 'hobbit', 'typeFill' => array(0.588235294118, 0.407843137255, 0)), array('num' => 4, 'name' => 'saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman', 'type' => 'bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude', 'typeFill' => array(1, 0, 0), 'typeColor' => array(1, 1, 1)), array('num' => 5, 'name' => 'sauron', 'type' => 'really bad dude', 'typeFill' => array(0, 0, 0), 'typeColor' => array(1, 1, 1)),
);

$cols = array('num' => 'No', 'type' => 'Type', 'name' => '<i>Alias</i>');

$conf = array(
'evenColumns' => 2,
'evenColumnsMin' => 40,
'maxWidth' => 350,
'shadeHeadingCol' => array(0.6, 0.6, 0.5),
'shaded' => 1,
'shadeCol' => array(0.95, 0.95, 0.95),
'shadeCol2' => array(0.85, 0.85, 0.85),
'xPos' => 'right',
'xOrientation' => 'left',
'gridlines' => 31,
'cols' => array('num' => array('bgcolor' => array(1, 1, 0))),
);

// custom column widths
// $conf['cols'] = array(
//                   /*'num' => ['width' => 30],*/
//                   /*'type' => ['width' => 100]*/
//                 );

$pdf->ezTable($data, $cols, '', $conf);

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream();
}
