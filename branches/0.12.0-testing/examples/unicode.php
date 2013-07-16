<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf{
	function Creport($p,$o){
  		$this->__construct($p, $o,'none',array());
  		$this->isUnicode = false;
  		// always embed the font for the time being
  		//$this->embedFont = false;
	}
}
$pdf = new Creport('a4','portrait');
$pdf->ezSetMargins(20,20,20,20);
//$pdf->rtl = true; // all text output to "right to left"
//$pdf->setPreferences('Direction','R2L'); // optional: set the preferences to "Right To Left"

$f = (isset($_GET['font']))?$_GET['font']:'Helvetica';

$mainFont = '../src/fonts/'.$f;
// select a font and use font subsetting
$pdf->selectFont($mainFont, '', 1, false);



//$pdf->ezText("Some European special chars:");
$pdf->ezText("This class <b>is designed to</b> provide a <b>non-module</b>, non-commercial alternative to dynamically creating
pdf documents from within PHP.
Obviously this will not be quite as quick as the module alternatives, but it is surprisingly fast, this
demonstration page is almost a worst case due to the large number of fonts which are displayed.
There are a number of features which can be within a Pdf document that it is not at the moment
possible to use with this class, but I feel that it is useful enough to be released.
This document describes the possible useful calls to the class, the readme.php file (which will create
this pdf) should be sufficient as an introduction.
Note that this document was generated using the demo script 'readme.php' which came with this
package.", 14, array('justification'=> 'full'));
//$pdf->ezText("Cyrillic:");
//$pdf->ezText("<u>КкЛлМмНнОоПпРрСсТтУу</u>");
//$pdf->ezText("Arabic:");
//$pdf->ezText("لبسبيلتتاف لالبالفقث بببب");
//$pdf->ezText("Hebrew:");
//$pdf->ezText("אבגדהוזחטיכלמנסעפצקרשת");
//$pdf->ezText("Chinese:");
//$pdf->ezText("汉语/漢語 <- Some fonts might not contain these glyphs. Tested with Arial Unicode");

//$pdf->isUnicode = false;
//$pdf->selectFont('../src/fonts/Courier');
//$pdf->ezText("\nThis text is using Courier in a non-unicode standard");

// reusing the mainFont does not require to enable unicode with $this->isUnicode

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