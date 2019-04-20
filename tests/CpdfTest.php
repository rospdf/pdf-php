<?php

include_once 'src/Cezpdf.php';

class CpdfTest extends \PHPUnit_Framework_TestCase
{
    private $output;
    private $outDir = 'tests/out';

    public function __construct()
    {
        parent::__construct();

        if (!is_dir('tests/out')) {
            mkdir('tests/out');
        }
    }

    /**
     * simple text output test
     */
    public function test_SimplePdf()
    {
        $pdf = new Cezpdf('a4', 'portrait');
        $pdf->addText(30, 760, 12, "Hello world");

        $this->output = $pdf->ezOutput();

        $this->savePdf(__FUNCTION__ . '.pdf');

        $this->assertTrue($this->validate());
    }

    public function test_CoreFontsPdf()
    {
        $pdf = new Cezpdf('a4', 'portrait');
        
        $letters = implode('', range('A', 'Z'));
        $numbers = implode('', range(0, 9));

        foreach (['Helvetica', 'Courier', 'Times-Roman', 'Symbol'] as $v) {
            $pdf->selectFont($v);
            $pdf->ezText("<b>$v:</b>\n$letters $numbers");
            $pdf->ezText("");
        }

        $this->output = $pdf->ezOutput(true);

        $this->savePdf(__FUNCTION__ . '.pdf');

        $this->assertTrue($this->validate());
    }

    /**
     * test using image
     */
    public function test_ImagePdf()
    {
        $pdf = new Cezpdf('a4', 'portrait');
        $pdf->ezText('');
        $pdf->ezImage('ros.jpg');

        $this->output = $pdf->ezOutput();

        $this->savePdf(__FUNCTION__ . '.pdf');

        $this->assertTrue($this->validate());
    }


    public function test_NonUnicodePdf()
    {
        $pdf = new Cezpdf('a4', 'portrait');
        //$pdf->isUnicode = true;

        $pdf->selectFont('SouthernAire', '', 1, true);
        //$pdf->ezText("Franz #! א", 30);
        $pdf->ezText("Hello World!\nHello Earth!", 30);

        $this->output = $pdf->ezOutput(['compression' => 0]);
        //$this->output = $pdf->ezOutput();
        
        $this->savePdf(__FUNCTION__ . '.pdf');

        $this->assertTrue($this->validate());
    }

    /**
     * Test (unicode) TTF fonts while subsetting the font program
     *
     * FIX: Unicode fonts invalidates the pdf - see below error from https://www.pdf-online.com/osa/validate.aspx
     * ----
     * The key CapHeight is required but missing.
     * The embedded font program 'AAAAAD+FreeSerif' cannot be read.
     * The key Encoding has a value Identity-H which is prohibited.
     * The document does not conform to the requested standard.
     * The document doesn't conform to the PDF reference (missing required entries, wrong value types, etc.).
     * The document contains fonts without embedded font programs or encoding information (CMAPs).
     * The document does not conform to the PDF 1.3 standard.
     * ----
     */
    public function test_UnicodePdf()
    {
        $pdf = new Cezpdf('a4', 'portrait');
        $pdf->isUnicode = true;

        $pdf->selectFont('FreeSerif', '', 1, true);
        //$pdf->ezText("Franz #! א", 30);
        $pdf->ezText("Hello World!\nHello Earth!", 30);

        $this->output = $pdf->ezOutput();
        
        $this->savePdf(__FUNCTION__ . '.pdf');

        $this->assertTrue($this->validate());
    }

    /**
     * Test (unicode) TTF fonts while implementing the full font program
     */
    public function test_UnicodeFullPdf()
    {
        $pdf = new Cezpdf('a4', 'portrait');
        $pdf->isUnicode = true;

        $pdf->selectFont('FreeSerif', '', 1);
        $pdf->ezText("Hello World!\nHello Earth!", 30);

        $this->output = $pdf->ezOutput();
        
        $this->savePdf(__FUNCTION__ . '.pdf');

        $this->assertTrue($this->validate());
    }

    /**
     * save the pdf output into a file
     */
    private function savePdf($filename)
    {
        file_put_contents($this->outDir . '/' . $filename, $this->output);
    }

    /**
     * pdf document validation (simplified)
     *
     * TODO: Validate against ISOs and embed fonts
     */
    private function validate()
    {
        if (substr($this->output, 0, 4) != '%PDF') {
            return false;
        }

        $lines = preg_split('/\n/', $this->output, -1, PREG_SPLIT_NO_EMPTY);

        $eof = $lines[count($lines) - 1];
        $size = $lines[count($lines) - 2];

        if ($eof != '%%EOF') {
            return false;
        }

        // calculated from the size from trailer, assume the next is 'xref'
        $xref = substr($this->output, intval($size), 4);

        if ($xref !== 'xref') {
            return false;
        }

        return true;
    }

    /**
     * TODO: Implement xref validation
     */
    private function validateXref()
    {
    }

    /**
     * TODO: Implement font validation
     */
    private function validateFont()
    {
    }
}
