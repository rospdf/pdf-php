<?php
error_reporting(E_ALL);
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
,array('num'=>4,'name'=>'<c:color:1,0,0>saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman saruman</c:color>','type'=>'bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude bad dude','typeFill'=>array(1,0,0),'typeText'=>array(1,1,1))
,array('num'=>5,'name'=>'sauron','type'=>'really bad dude','typeFill'=>array(0,0,0),'typeText'=>array(1,1,1))
);

$cols = array('num'=>'No', 'type'=>'Type','name'=>'<i>Alias</i>');

$conf = array("evenColumns"=>1, "shaded"=>0, "shadeCol"=>array(1, 1, 0), "shadeCol2"=>array(0.85, 0.85, 0.85), 'xPos'=>'center', 'xOrientation'=>'center', 'gridlines'=>31);
$pdf->ezTable($data, $cols, "", $conf);

if (isset($_GET['d']) && $_GET['d']){
  echo $pdf->ezOutput(TRUE);
} else {
  $pdf->ezStream();
}
?>
