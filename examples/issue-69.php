<?php
set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

require 'Cezpdf.php';

$W = 270;

$Options = array();
$Options["showHeadings"] = 0;
$Options["shaded"] = 0;
$Options["fontSize"] = 10;
$Options["textCol"] = array(0, 0, 0);
$Options["titleFontSize"] = 12;
$Options["titleGap"] = 5;
$Options["rowGap"] = 4;
$Options["colGap"] = 4;
$Options["lineCol"] = array(0, 0, 0);
$Options["maxWidth"] = $W;
$Options["cols"] = array();
$Options["cols"]["Col0"] = array("justification"=>"left", "width"=>$W);
$Options["evenColumns"] = 0;
$Options["protectRows"] = 3;
$Options["xPos"] = 30;
$Options["xOrientation"] = "right";
$Options["innerLineThickness"] = 0.25;
$Options["outerLineThickness"] = 0.25;
$Options["width"] = $W;
$Options["gridlines"] = EZ_GRIDLINE_DEFAULT;

$Columns = array();
$Columns["Col0"] = "";

$Data = array();
$Data[0] = array();
$Data[0]["Col0"] = "<b><u>Bold and Underlined</u></b>";

$P = new Cezpdf("a4", "portrait");
if (strpos(PHP_OS, 'WIN') !== false) {
    $P->tempPath = 'C:/temp';
}

$P->ezSetMargins(30, 30, 30, 30);

$P->ezText("<b><u>Bold and Underlined</u></b>", 10, array("justification"=>"left"));
$P->ezText(" ", 10);
$P->ezTable($Data, $Columns, "", $Options);
$P->ezText(" ", 10);

$Data[0]["Col0"] = "<u><b>Bold and Underlined</b></u>";

$P->ezText("<u><b>Bold and Underlined</b></u>", 10, array("justification"=>"left"));
$P->ezText(" ", 10);
$P->ezTable($Data, $Columns, "", $Options);
$P->ezText(" ", 10);

$P->ezStream(['compress' => 0]);
?>
