<?php
include_once "../src/Cezpdf.php";
$pdf = new CezPDF("a4",'','color', array(0.7,0.8,0.8));
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'E:/xampp/xampp/tmp';
}

$pdf->selectFont('../src/fonts/Helvetica');

// table data
$data = array(
 array('num'=>1,'name'=>'gandalf','type'=>'wizard')
,array('num'=>2,'name'=>'bilbo','type'=>'hobbit','url'=>'http://www.ros.co.nz/pdf/')
,array('num'=>3,'name'=>'frodo','type'=>'hobbit')
,array('num'=>4,'name'=>'saruman','type'=>'bad dude','url'=>'http://sourceforge.net/projects/pdf-php')
,array('num'=>5,'name'=>'sauron','type'=>'really bad dude')
);

$cols = array('type'=>'Type','name'=>'<i>Alias</i>');

$pdf -> ezSetCmMargins(2, 1.25, 1.5, 1.5);

	$width = $pdf->ez['pageWidth']  - $pdf->ez['leftMargin'] - $pdf->ez['rightMargin'];
		$pdf->setLineStyle(0.5);
		$pdf->SetStrokeColor(1,0,0);
		$pdf->rectangle($pdf->ez['leftMargin'],
						 $pdf->ez['pageHeight'] - $pdf->ez['topMargin'],
						 // 72, 72);
						 $pdf->ez['pageWidth']  - $pdf->ez['leftMargin'] - $pdf->ez['rightMargin'],
						 -($pdf->ez['pageHeight'] - $pdf->ez['topMargin']  - $pdf->ez['bottomMargin']));
		$pdf->line($pdf->ez['leftMargin'] + $width /2,
					$pdf->ez['pageHeight'] - $pdf->ez['topMargin'],
					$pdf->ez['leftMargin'] + $width /2,
		 		 -($pdf->ez['pageHeight'] - $pdf->ez['topMargin']  - $pdf->ez['bottomMargin']));

				 
$pdf->ezImage('../ros.jpg', 0, 0, 'none', 'left');
		
$pdf->ezText("In this test case the margins and the vertical centre of the page are drawn with a thin red line\n\n");
$pdf->ezText("This image is the first thing output on the page, but its top part goes above the top margin.\n\n");
$pdf->ezText("This text starts at the left marigin of the page, but tables are shifted a little (colGap) to the left, regardless of their positioning.\n\n");

$pdf->ezText("\nFull width table\n");
$pdf->ezTable($data, $cols,'', array('showHeadings'=>1,'shaded'=>0,'showLines'=>1, 'width'=>$width));

$pdf->ezText("\nLeft aligned table\n");
$pdf->ezTable($data, $cols,'', array('showHeadings'=>1,'shaded'=>0,'showLines'=>1, 'xPos'=>'left', 'xOrientation'=>'right'));

$pdf->ezText("\nRight aligned table\n");
$pdf->ezTable($data, $cols,'', array('showHeadings'=>1,'shaded'=>0,'showLines'=>1, 'xPos'=>'right', 'xOrientation'=>'left'));


if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream();
}
?>