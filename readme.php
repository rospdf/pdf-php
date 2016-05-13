<?php
//===================================================================================================
// this is the php file which creates the readme.pdf file, this is not seriously
// suggested as a good way to create such a file, nor a great example of prose,
// but hopefully it will be useful
//
// adding ?d=1 to the url calling this will cause the pdf code itself to ve echoed to the
// browser, this is quite useful for debugging purposes.
// there is no option to save directly to a file here, but this would be trivial to implement.
//
// note that this file comprisises both the demo code, and the generator of the pdf documentation
//
//===================================================================================================


// don't want any warnings turning up in the pdf code if the server is set to 'anal' mode.
//error_reporting(7);
error_reporting(E_ALL);
set_time_limit(1800);

include './src/Cezpdf.php';

// define a clas extension to allow the use of a callback to get the table of contents, and to put the dots in the toc
class Creport extends Cezpdf {
	
	public $reportContents = array();
	
	function Creport($p,$o,$t,$op){
	  parent::__construct($p,$o,$t,$op);
	  
	  $this->RegisterCallbackFunc("rf", "rf:?.*?", "appearance");
	  $this->RegisterCallbackFunc("dots", "dots:?.*?", "appearance");
	  
	  //$this->Compression = 0;
	}

	public function rf(&$sender, &$cb, $bbox, $params){
		$app = &$cb['appearance'];
		// used to set it into background
		$app->ZIndex = -10;
		// used to ignore justification offset in Callbacks
		$app->JustifyCallback = FALSE;

		// get senders lower y position (to calculate from margins)
		$y = $sender->GetBBox('y');
		
		// level of toc
		$lvl = $params[0][0];
		// label name
		$lbl = rawurldecode(substr($params[0],1));
		
		array_push($this->reportContents, array($lbl, &$this->CURPAGE->PageNum, $lvl));
		
		//$sender->page->Name = "tocpage".$sender->page->PageNum;
		
		$h = $sender->GetFontHeight();
		
		switch ($lvl){
			case '1':
				$app->AddColor(0.8, 0.8, 0.8);
				$app->AddRectangle(0, $bbox[3] - $y, 0, -$h, TRUE);
				break;
			case '2':
				$app->AddColor(0.9, 0.9, 0.9);
				$app->AddRectangle(0, $bbox[3] - $y, 0, -$h, TRUE);
				break;
		}
		
		return $sender->Tj($params[1]);
	}

	function dots(&$sender, &$cb, $bbox, $params){
		$app = &$cb['appearance'];
		//$app->JustifyCallback = FALSE;
		
		// draw a dotted line over to the right and put on a page number
	  	$lvl = $params[0][0];
	  	$lbl = substr($params[0],1);
	  	
	  	$initBBox = $app->GetBBox();
	  	
	  	$ls = new Cpdf_LineStyle(1, 'butt', 'butt', array(1,2));
	  	$app->AddLine(0,0, $initBBox[2] - $bbox[2], 0, $ls);
	  	
	  	switch($lvl){
	  		case '1':
	  			$size=16;
  				break;
  			case '2':
  				$size=12;
  				break;
	  	}
	  	
	  	$app->SetFont("Helvetica", $size);
	  	$app->UpdateBBox(array('uy'=> $bbox[3] - $app->GetFontHeight() - $app->GetFontDescender()), TRUE);
	  	$app->AddText($lbl, 0, 'right');
	  	$app->UpdateBBox($initBBox);
	}
}
// I am in NZ, so will design my page for A4 paper.. but don't get me started on that.
// (defaults to legal)
// this code has been modified to use ezpdf.

$project_url = "http://pdf-php.sf.net";
$project_version = "0.13.0";

$pdf = new Creport('a4','portrait', 'none', null);
//Cpdf::$DEBUGLEVEL = Cpdf::DEBUG_OUTPUT;
// to test on windows xampp
  if(strpos(PHP_OS, 'WIN') !== false){
    $pdf->tempPath = 'E:/xampp/xampp/tmp';
  }
$start = microtime(true);

$pdf->ezSetMargins(50,70,50,50);

// put a line top and bottom on all the pages
$appFooter = $pdf->NewAppearance(array('adduy'=> 10,'ly'=> 20));
$appFooter->SetPageMode(Cpdf_Content::PMODE_ALL);

$appFooter->AddLine(0, $appFooter->GetBBox('height'), $appFooter->GetBBox('width'));
$appFooter->UpdateBBox(array('uy'=> 40), TRUE);

$appFooter->SetFont("Helvetica", 8);
$appFooter->AddText($project_url . " - Version " .$project_version);

$appFooter->AddLine(0, 20, $appFooter->GetBBox('width'));

//$pdf->ezSetDy(-100);

$mainFont = 'Times-Roman';
$codeFont = 'Courier';
// select a font
$pdf->selectFont($mainFont);

$pdf->ezText("PHP Pdf Creation\n",30,array('justification'=>'centre'));
$pdf->ezText("Module-free creation of Pdf documents\nfrom within PHP\n",20,array('justification'=>'centre'));
$pdf->ezText("developed by R&OS Ltd",18,array('justification'=>'centre'));
//$pdf->ezText("<c:alink:$project_url>$project_url</c:alink>\n\nVersion $project_version",18,array('justification'=>'centre'));

//$pdf->ezSetDy(-100);
// modified to use the local file if it can
//$pdf->openHere('Fit');


function ros_logo(&$pdf,$x,$y,$height,$wl=0,$wr=0){
	global $project_url;
	
	$app = $pdf->NewAppearance(array('uy'=> 350, 'ly'=> 270, 'addux'=> 50, 'addlx'=> -50));
	$app->BreakPage = 0; 
	
	$app->AddColor(0.6,0,0);
	$app->AddRectangle(0, 0, null, null, TRUE);
	$app->AddColor(1,1,1);
	$app->SetFont('Helvetica', 85, 'b');
	
	// used to restore the BBbox if neccassary
	//$tmp = $app->BBox;
	$app->UpdateBBox(array('addlx'=> 50, 'addux'=> -50));
	$app->LineGap = ($app->GetFontDescender() * 2);
	$app->AddText("R&OS");
	
	$app->SetFont('Helvetica', 8, 'b');
	$app->AddColor(0.6,0,0);
	$app->AddText($project_url, 0, "right");
}

ros_logo($pdf,150,$pdf->y-100,80,150,200);
$pdf->selectFont($mainFont);

if (file_exists('ros.jpg')){
  //$pdf->addJpegFromFile('ros.jpg',199,$pdf->y,200,0);
}

//-----------------------------------------------------------
// load up the document content
$data=file('./data.txt');

$pdf->ezNewPage();

$size=12;
$height = $pdf->getFontHeight($size);
$textOptions = array('justification'=>'full');
$collecting=0;
$code='';

foreach ($data as $line){
  // go through each line, showing it as required, if it is surrounded by '<>' then
  // assume that it is a title
  $line=chop($line);
  if (strlen($line) && $line[0]=='#'){
    // comment, or new page request
    switch($line){
      case '#NP':
        $pdf->ezNewPage();
        break;
      case '#C':
        $pdf->selectFont($codeFont);
        $textOptions = array('justification'=>'left','left'=>20,'right'=>20);
        $size=10;
        break;
      case '#c':
        $pdf->selectFont($mainFont);
        $textOptions = array('justification'=>'full');
        $size=12;
        break;
      case '#X':
        $collecting=1;
        break;
      case '#x':
        //$pdf->saveState();
        //eval($code);
        //$pdf->restoreState();
        //$pdf->selectFont($mainFont);
        //$code='';
        $collecting=0;
        break;
    }
  } else if ($collecting){
    $code.=$line;
  } else if (((strlen($line)>1 && $line[1]=='<') ) && $line[strlen($line)-1]=='>') {
    // then this is a title
    switch($line[0]){
      case '1':
        $tmp = substr($line,2,strlen($line)-3);
        $tmp2 = $tmp.'<C:rf:1'.rawurlencode($tmp).'>';
        $pdf->ezText($tmp2 ,26,array('justification'=>'left'));
        break;
      default:
      	$tmp = substr($line,2,strlen($line)-3);
        // add a grey bar, highlighting the change
        $tmp2 = $tmp.'<C:rf:2'.rawurlencode($tmp).'>';
        $pdf->ezText($tmp2,18,array('justification'=>'left'));
        break;
    }
  } else {
    // then this is just text
    // the ezpdf function will take care of all of the wrapping etc.
    $pdf->ezText($line,$size,$textOptions);
  }

}

//$pdf->ezStopPageNumbers(1,1);

// now add the table of contents, including internal links
$pdf->InsertMode(2);
$pdf->ezNewPage();

$pdf->ezText("Contents\n",26,array('justification'=>'centre'));
$xpos = 520;

foreach($pdf->reportContents as $k=>&$v){
	switch ($v[2]){
    	case '1':
      	$pdf->ezText('<c:ilink:'.$v[1].'>'.$v[0].' Page: '.$v[1].'</c:ilink>',16,array('aright'=>$xpos));
      	break;
    	case '2':
      	$pdf->ezText('<c:ilink:'.$v[1].'>'.$v[0].' Page: '.$v[1].'</c:ilink>',12,array('left'=>50,'aright'=>$xpos));
      	break;
	}
}
$pdf->PageOffset(10);

if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  $pdf->ezStream();
}

$end = microtime(true) - $start;
error_log($end);

?>