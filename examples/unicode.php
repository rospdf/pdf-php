<?php
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());

include 'Cezpdf.php';

class Creport extends Cezpdf
{
    public function __construct($p, $o)
    {
        parent::__construct($p, $o, 'none', []);
        $this->isUnicode = true;
        $this->allowedTags .= '|uline';
    }
}

$pdf = new Creport('a4', 'portrait');

$pdf->ezSetMargins(20, 20, 20, 20);

$mainFont = (isset($_GET['font'])) ? $_GET['font'] : 'FreeSerif';

$family = array(
    'b'=> $mainFont . 'Bold'
);

$pdf->setFontFamily($mainFont, $family);

// select a font and use font subsetting
$pdf->selectFont($mainFont, '', 1, true);

$pdf->ezText('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidfgfdgdfgdfgdfg ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur. et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur', 10, ['justification' => 'full']);
$pdf->ezText("\n<b>Greek: (full justified)</b>");
$pdf->ezText('Νες εα ελεστραμ σορρυμπιθ ινστρυσθιορ, υσυ διαμ ωπωρθεαθ τεμποριβυς ετ. Προμπτα βλανδιτ μωδερατιυς ευμ ευ, σεθερο ρεπυδιαρε αν φελ, φιξ πυρθο ρεγιονε φολυπθυα ατ. Σιθ δυις σωνσυλ ιρασυνδια ατ, νε νιηιλ φενιαμ φεριθυς ιυς, συ μελιορε ερροριβυς δισπυθανδο εσθ. Ηις εσεντ σοπιωσαε ιδ. Εξ εως μεις αυγυε ρεσυσαβο, φιξ φοσεντ μαλορυμ ινσιδεριντ ιν. Δισο ναθυμ σοντεντιωνες ευ μει.', 10, ['justification' => 'full']);
$pdf->ezText("\nCyrillic:");
$pdf->ezText('ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор ыёюз лобортис ажжынтиор <u>КкЛлМмНнО</u> <u>оПпРр</u> <u>СсТтУу</u>', 10, ['justification' => 'full']);
$pdf->ezText("\nArabic:");
$pdf->ezText('لبسبيلتتاف لالبالفقث بببب');
$pdf->ezText("\nHebrew:");
$pdf->ezText('אבגדהוזחטיכלמנסעפצקרשת');
$pdf->ezText("\nChinese:");
$pdf->ezText('汉语/漢語 <- Not supported in FreeSerif');

//$pdf->isUnicode = false;
//$pdf->selectFont('../src/fonts/Courier');
//$pdf->ezText("\nThis text is using Courier in a non-unicode standard");

// reusing the mainFont does not require to enable unicode with $this->isUnicode

if (isset($_GET['d']) && $_GET['d']) {
    echo $pdf->ezOutput(true);
} else {
    $pdf->ezStream();
}
