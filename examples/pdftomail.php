<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';
require_once("Mail.php");
class Creport extends Cezpdf{
	function Creport($p,$o){
  		$this->__construct($p, $o,'none');
	}
}
$pdf = new Creport('a4','portrait');

$pdf -> ezSetMargins(20,20,20,20);

$mainFont = 'Times-Roman';
// select a font
$pdf->selectFont($mainFont);
$size=12;

$height = $pdf->getFontHeight($size);
// modified to use the local file if it can
$pdf->openHere('Fit');

$pdf->ezText("PNG grayscaled with alpha channel");
$pdf->ezImage('images/test_grayscaled_alpha.png',0,0,'none','right');

if (isset($_GET['d']) && $_GET['d']){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
} else {
  //$pdf->ezStream(array('compress'=>0));
  //die;
  //$pdf->options['compression']=0;
  $doc = $pdf->ezOutput();
  
$recipients = array( 'ole.koeckemann@gmail.com' );
/* boundary */ 
$boundary = strtoupper(md5(uniqid(time()))); 

$headers = array (
    'From' => 'ole1986@users.sourceforge.net',
    'To' => join(', ', $recipients),
    'Subject' => 'Test compressed pdf',
    'Content-type' => 'multipart/mixed; boundary='.$boundary,
    'MIME-Version' => '1.0'
    
);

$body = "This is a multi-part message in MIME format  --  Dies ist eine mehrteilige Nachricht im MIME-Format\n"; 
/* Hier faengt der normale Mail-Text an */ 
$body .= "\n--$boundary"; 
$body .= "\nContent-type: text/plain"; 
$body .= "\nContent-Transfer-Encoding: 8bit"; 
$body .= "\n\nTEST"; 

/* Hier faengt der Datei-Anhang an */ 
$body .= "\n--$boundary"; 
$body .= "\nContent-type: application/pdf name=\"test.pdf\""; 
/* Lese aus dem Array $contenttypes die Codierung fuer den MIME-Typ des Anhangs aus */ 
$body .= "\nContent-Transfer-Encoding: base64"; 
$body .= "\nContent-Disposition: attachment; filename=\"test.pdf\""; 
$body .= "\n\n".chunk_split(base64_encode($doc)); 

/* Gibt das Ende der eMail aus */ 
$body .= "\n--$boundary--"; 

$mail_object =& Mail::factory('smtp',
    array(
        'host' => 'prwebmail',
        'auth' => true,
        'username' => 'pdf-php',
        'password' => '1intuz3', # As set on your project's config page
        'debug' => true, # uncomment to enable debugging
    ));

$mail_object->send($recipients, $headers, $body);
}
?>