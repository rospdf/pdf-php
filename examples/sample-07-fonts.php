<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cpdf.php';

$pdf = new Cpdf_Extension(Cpdf_Common::$Layout['A4']);
$pdf->FontSubset = true;
// to test on windows xampp
if(strpos(PHP_OS, 'WIN') !== false)
    Cpdf::$TempPath = 'D:/xampp/tmp';

$textObject = $pdf->NewText();

$textObject->SetFont('Helvetica', 10);
$textObject->AddText("Helvetica\nLorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n");

$textObject->SetFont('Courier', 10);
$textObject->AddText("Courier\nLorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n");

$textObject->SetFont('Times-Roman', 10);
$textObject->AddText("Times-Roman\nLorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n");

$textObject->SetFont('ZapfDingbats', 10);
$textObject->AddText("ZapfDingbats\nLorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n");

// define FreeSerif as Unicode
$textObject->SetFont('FreeSerif', 10,'',true);
$textObject->AddText("FreeSerif (Unicode font)\nLorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");
$textObject->AddText("Ыюм ыт вэрыар янжольэнж, хёз ыт конжюль пожжёт ютроквюы. Эжт нэ жкрипта промпта дычэрунт, шэа импэрдеэт ажжынтиор экз. Модо мовэт адвыржаряюм жят йн, жят дикырыт элььэефэнд продыжщэт ед.\n");
// TODO: fix bug for RTL fonts as they cause an error in sprintf, line 2531 for Cpdf.php
$textObject->AddText("FreeSerif does not contain chinese glyphs. Let us test the .notdef glyph:");
$textObject->AddText("汉 语 漢 語");

// Output the PDF - use parameter 1 to set a filename
$pdf->Stream(basename(__FILE__, '.php').'.pdf');
?>