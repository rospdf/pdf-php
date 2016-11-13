<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('UTC');

include_once '../src/Cezpdf.php';
$pdf = new CezPDF("a4");

if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'C:/temp';
}

$pdf->selectFont('Helvetica');

// some general data used for table output
$data = array(
array('num'=>1,'name'=>'gandalf','type'=>'wizard','typeFill'=>array(0,1,0))
,array('num'=>2,'name'=>'bilbo','type'=>'hobbit','typeFill'=>array(0.588235294118, 0.407843137255, 0))
,array('num'=>3,'name'=>'frodo','type'=>'hobbit','typeFill'=>array(0.588235294118, 0.407843137255, 0))
,array('num'=>4,'name'=>'saruman','type'=>'bad dude','typeFill'=>array(1,0,0),'typeText'=>array(1,1,1,))
,array('num'=>5,'name'=>'sauron','type'=>'really bad dude','typeFill'=>array(0,0,0),'typeText'=>array(1,1,1,))
);

$cols = array('num'=>'No', 'type'=>'Type','name'=>'<i>Alias</i>');
//$coloptions = array('num'=> array('justification'=>'right'), 'name'=> array('justification'=>'left'),'type'=> array('justification'=>'center'));

$pdf->ezTable($data, $cols);

if (isset($_GET['d']) && $_GET['d']){
  echo $pdf->ezOutput(TRUE);
} else {
  $pdf->ezStream();
}
?>