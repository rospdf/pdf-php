<?php
error_reporting(E_ALL);
set_time_limit(1800);
set_include_path('../src/' . PATH_SEPARATOR . get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf{
	function Creport($p,$o){
  		$this->__construct($p, $o,'none',array());
  		$this->isUnicode = true;
        $this->allowedTags .= '|uline';
  		// always embed the font for the time being
  		//$this->embedFont = false;
	}
}
$pdf = new Creport('a4','portrait');
$pdf->ezSetMargins(20,20,20,20);
//$pdf->rtl = true; // all text output to "right to left"
//$pdf->setPreferences('Direction','R2L'); // optional: set the preferences to "Right To Left"

$f = (isset($_GET['font']))?$_GET['font']:'FreeSerif';

$mainFont = $f;
// select a font and use font subsetting
$pdf->selectFont($mainFont, '', 1, true);
$pdf->ezText("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidfgfdgdfgdfgdfg ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur. et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur", 10, array('justification'=>'full'));
$pdf->ezText("\nGreek: (full justified)");
$pdf->ezText("Νες εα ελεστραμ σορρυμπιθ ινστρυσθιορ, υσυ διαμ ωπωρθεαθ τεμποριβυς ετ. Προμπτα βλανδιτ μωδερατιυς ευμ ευ, σεθερο ρεπυδιαρε αν φελ, φιξ πυρθο ρεγιονε φολυπθυα ατ. Σιθ δυις σωνσυλ ιρασυνδια ατ, νε νιηιλ φενιαμ φεριθυς ιυς, συ μελιορε ερροριβυς δισπυθανδο εσθ. Ηις εσεντ σοπιωσαε ιδ. Εξ εως μεις αυγυε ρεσυσαβο, φιξ φοσεντ μαλορυμ ινσιδεριντ ιν. Δισο ναθυμ σοντεντιωνες ευ μει.", 10, array('justification'=>'full'));
$pdf->ezText("\nCyrillic:");
$pdf->ezText("ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор <u>КкЛлМмНнО</u> <u>оПпРр</u> <u>СсТтУу</u>");
$pdf->ezText("\nArabic:");
$pdf->ezText("لبسبيلتتاف لالبالفقث بببب");
$pdf->ezText("\nHebrew:");
$pdf->ezText("אבגדהוזחטיכלמנסעפצקרשת");
$pdf->ezText("\nChinese:");
$pdf->ezText("汉语/漢語 <- Some fonts might not contain these glyphs. Tested with Arial Unicode");

//$pdf->isUnicode = false;
//$pdf->selectFont('../src/fonts/Courier');
//$pdf->ezText("\nThis text is using Courier in a non-unicode standard");

// reusing the mainFont does not require to enable unicode with $this->isUnicode

if (isset($_GET['d']) && $_GET['d']){
  echo $pdf->ezOutput(TRUE);
} else {
  $pdf->ezStream();
}
?>
