<?php

set_include_path('../src/'.PATH_SEPARATOR.get_include_path());
date_default_timezone_set('UTC');

require 'Cezpdf.php';

class Creport extends Cezpdf
{
    public function __construct($p, $o)
    {
        parent::__construct($p, $o, 'none', array());
    }
}

$pdf = new Creport('A4', 'LANDSCAPE');

if (strpos(PHP_OS, 'WIN') !== false) {
    $pdf->tempPath = 'C:/temp';
}

$aHeader = array();
$dept_name = array();
$aRecord01 = array();
$aRecord0204 = array();
$aDept_total_amt = array();
$aDept_percentage = array();
$aDept_total = array();
$i = 0;

$ypos = 540; // vertical starting position
$font_size_8 = 8;
$font_size_10 = 10;
$font_size_12 = 12;
$font_size_14 = 14;
$row_spacing = 15;
// ---------- COLUMN POSITIONS BEGIN ----------
$x1 = array('justification' => 'left', 'left' => 1, 'spacing' => 0);
$x1b = array('justification' => 'full', 'left' => 1, 'spacing' => 1);
$x1c = array('justification' => 'left', 'left' => 1, 'spacing' => 1.5);
$x4 = array('justification' => 'left', 'left' => 645, 'spacing' => 0);
$x5 = array('justification' => 'right', 'right' => 0, 'spacing' => 0);
// ---------- COLUMN POSITIONS END ----------
// ---------- COLUMN HEADER VALUES BEGIN ----------
$pdf->selectFont('Helvetica-Bold');
$title = 'Program Plan Narrative';
$title_width = $pdf->getTextWidth($font_size_14, "$title");
$title_position = (420.95 - $title_width * 0.5);
// ---------- COLUMN HEADER VALUES END ----------
$prev_dept = 'ZZZZ';
$curr_dept = ' ';
$curr_progid = 'XXXXXXXX';
$prev_progid = ' ';
$total_flag = 'no';
$new_page_flag = 'no';
// ---------- PRINTS HEADER AND FOOTER BEGINS ----------
$pdf->selectFont('Helvetica-Bold');
$all = $pdf->openObject();
$pdf->saveState();
$pdf->addText($title_position, $ypos, $font_size_14, $title);
$ypos = $ypos - $row_spacing;
$pdf->selectFont('Helvetica');
$pdf->setLineStyle(1, 'round');
$pdf->line(76, 516, 770, 516); // column header line
$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all, 'all');
// ---------- HEADER AND FOOTER ENDS ----------

$pdf->ezSetMargins(75, 40, 75, 75);

while ($i < 1) {
    $curr_progid = 'AGS211';
    $curr_deptid = 'AGS';
    $curr_pgmno = '211';
    $nar_p[0] = "<u>A. Statement of Program Objectives</u>\n\nTo assist in protecting the rights of public and private land ownership by
providing land surveying services.";
    $nar_p[1] = "<u>B.Description of Request and Compliance with Section 37-68(1)(A)(B)</u>\n\nNo new programs are being proposed at this time. The program complies
with Section 37-68(1)(A)(B).";
    $nar_p[2] = "<u>C. Description of Activities Performed</u>\n\nMajor activities include statewide field surveying services and furnishing of maps and descriptions of all government and selected private lands as a service to State Agencies who require this program's technical assistance. Maps and descriptions are utilized by these agencies for various types of land transactions.\nChecking and processing all Land Court and File Plan maps referred by the Land Court and the Bureau of Conveyances, respectively, prior to these maps being adjudicated and recorded.
Assist the Department of Land and Natural Resources (DLNR) by reviewing all shoreline applications statewide. Maps are reviewed, checked on the ground and recommendations are forwarded to the Chairman of the Board of Land and Natural Resources.\nThe State is required to respond through the Circuit Courts on all Quiet Title Actions in which the State is cited as the defendant. The interest of the State as well as the general public are thoroughly researched and reported to the Attorney General. The program is also involved in litigation as expert witnesses.";
    $nar_p[3] = "<u>D. Statement of Key Policies Pursued</u>\n\nIn support of the Hawaii State Plan, the program provides office and field land surveying services to facilitate the achievements of priority directives of the agencies serviced. Included as part of the policy is the protection of the State government and individuals property rights. For the State's socio-cultural advancement with regard to housing, the program will assist in effectively accommodating the housing needs of Hawaii's people. Subdivision maps submitted on behalf of government agencies such as the Hawaii Public Housing Authority, the Department of Hawaiian Home Lands, the Federal Government, and the private sector are checked and processed in a timely manner. To aid in exercising an overall conservation ethic in the use of Hawaii's resources, the program reviews all shoreline certification applications to insure conformance with existing shoreline administrative rules and statutes.
";
    $nar_p[4] = "<u>E. Identification of Important Program Relationships</u>\n\nAlthough essential activities exist between this program and other government agencies as well as others in the private sector, respective objectives of the parties involved are distinct and do not warrant integration. On land litigations, the Department of the Attorney General relies on the program's expertise and professional knowledge as expert witness.";
    $nar_p[5] = "<u>F. Description of Major External Trends Affecting the Program</u>\n\nThe amendment to Chapters 205 and 669, HRS, Shoreline Setback Act and Quiet Title Actions, significantly increased the program's workload. Special attention is concentrated in preserving the public's rights to access along beaches, forest lands and historic sites. In addition, previously unaccounted for old school grants, government remnants, and government roads have been claimed on behalf of the State. Numerous illegal use of Government lands especially along shorelines have been detected and reported to the DLNR. Subsequent actions by the DLNR have resulted in the sale or lease of lands or assessment of penalties that resulted in increased revenues.";
    $nar_p[6] = "<u>G. Discussion of Cost, Effectiveness, and Program Size Data</u>\n\nAcquisition of computers, scanners and electronic surveying instruments together with the use of e-mail and the internet have expedited services and dramatically improved accessibility to the public and government agencies requesting survey maps and survey information. However, limited funding and staff reductions negatively impacts the program's effectiveness.";
    $nar_p[7] = "<u>H. Discussion of Program Revenues</u>\n\nRevenues for this program are derived from the sale of copies of maps and descriptions and prints of Land Court and File Plan maps. Fees are also assessed for the checking and processing of all Land Court and File Plan subdivision maps and field check of original Land Court Applications.";
    $nar_p[8] = "<u>I. Summary of Analysis Performed</u>\n\nAn in-depth program analysis has not been performed for this program.";
    $nar_p[9] = "<u>J. Further Considerations</u>\n\nRapidly changing technology in the field of computers and surveying equipment requires the program's constant need to update its software and equipment. The continued observations of the latest developments in equipment methodology are a necessary ingredient for a successful operation.";

    $title_hdr = 'LAND SURVEY';
    $title_progstr = substr(chunk_split(trim('11030703'), 2, ' '), 0, -1);
    $title_progid = $curr_deptid.$curr_pgmno;
    $prog_title = $title_progid.': ';

    if ($curr_progid != $prev_progid) {
        if ($new_page_flag == 'yes') {
            $new_page_flag = 'no';

            if ($curr_deptid != 'ZZZ') {
                $pdf->ezNewPage();
            }
        }
        $pdf->selectFont('Helvetica');
        if ($curr_deptid == 'ZZZ') {
        } else {
            $pdf->ezSetMargins(75, 40, 75, 75);
            $all2 = $pdf->openObject();
            $pdf->saveState();
            $pdf->selectFont('Helvetica-Bold');

            $ypos = $pdf->ezText("$prog_title $title_hdr", $font_size_10, $x1);
            $pdf->ezText("$title_progstr", $font_size_10, $x5);
            $pdf->selectFont('Helvetica');
            $pdf->restoreState();
            $pdf->closeObject();
        }
        // ----- writes the detail line  begin -----

        $pdf->ezSetMargins(90, 40, 75, 75);
        $pdf->ezColumnsStart(array('gap' => 30));

        $z = 0;
        if ($curr_deptid == 'ZZZ') {
            $loop_narr = 0;
        } else {
            $loop_narr = 10;
        }

        for ($z = 0; $z < $loop_narr; ++$z) {
            $pdf->selectFont('Helvetica');
            $pdf->ezText($nar_p[$z]."\n", $font_size_10, $x1b);
            $pdf->addObject($all2, 'add');
        }
        // ----- writes detail line end -----
        ++$i;
    } //$curr_dept = $prev_dept
    $pdf->ezColumnsStop();
    $new_page_flag = 'yes';
    $prev_progid = $curr_progid;
}

if (isset($_GET['d']) && $_GET['d']) {
    echo '<pre>';
    echo $pdf->ezOutput(true);
    echo '</pre>';
} else {
    $pdf->ezStream();
}
