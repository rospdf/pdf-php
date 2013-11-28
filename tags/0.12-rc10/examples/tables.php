<?php
$ext = '../extensions/CezTableImage.php';
if(!file_exists($ext)){
	die('This example requires the CezTableImage.php extension');
}

include $ext;
$pdf = new CezTableImage("a4");

$pdf->selectFont('Helvetica');

// table data
$data = array(
 array('num'=>1,'name'=>'gandalf','type'=>'wizard')
,array('num'=>2,'name'=>'bilbo','type'=>'hobbit','url'=>'http://www.ros.co.nz/pdf/')
,array('num'=>3,'name'=>'frodo','type'=>'hobbit')
,array('num'=>4,'name'=>'saruman','type'=>'bad dude','url'=>'http://sourceforge.net/projects/pdf-php')
,array('num'=>5,'name'=>'sauron','type'=>'really bad dude')
);

$cols = array('type'=>'Type','name'=>'<i>Alias</i>');


$pdf->ezText("Simple data output\n");
$pdf->ezTable($data);

$pdf->ezText("\nAn example defining the columns and a title\n");
$pdf->ezTable($data,$cols,'This table as a title');

$pdf->ezText("\nNo headings or shading, or lines\n");
$pdf->ezTable($data, $cols, '', array('showHeadings'=>0,'shaded'=>0,'showLines'=>0));

$pdf->ezText("\nAnother example with <b>showLines</b> option set to 3 for horizontal lines\n");
$pdf->ezTable($data, $cols,'', array('showHeadings'=>1,'shaded'=>0,'showLines'=>3));

$pdf->ezText("\nAnother example with <b>showLines</b> option set to 4 , only head line\n");
$pdf->ezTable($data,array('type'=>'Type','name'=>'<i>Alias</i>'),'' ,array('showHeadings'=>1,'shaded'=>0,'showLines'=>4));
              
$pdf->ezText("\nExample to show shaded headings <b>since 0.12-rc9</b>\n");
$pdf->ezTable($data,$cols,'',array('shadeHeadingCol'=>array(0.4,0.6,0.6),'width'=>400));

$pdf->ezText("\nAnother example with colored columns and a colored header\n");
$pdf->ezTable($data, $cols, ''
        ,array('showHeadings'=>1,'showBgCol'=>1,'width'=>400, 
        'shadeHeadingCol'=>array(0.6,0.6,0.5)
        ,'cols'=> array(
                    'name'=>array('bgcolor'=>array(0.9,0.9,0.7))
                   ,'type'=>array('bgcolor'=>array(0.6,0.4,0.2))
                  )
        ));
        
$pdf->ezText("\nJustified table and columns <b>and almost forgot: row shading</b>\n");
$pdf->ezTable($data,$cols,'',array('xPos'=>90,'xOrientation'=>'right','width'=>300,
         'shaded'=>2,
         'shadeCol'=> array(0.9,0.9,0.7),
         'shadeCol2'=> array(0.6,0.4,0.2),
         'shadeHeadingCol'=>array(0.6,0.6,0.5)
         ,'cols'=>array('type'=>array('justification'=>'right'),'name'=>array('width'=>100))
         ));

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