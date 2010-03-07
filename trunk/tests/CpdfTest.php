<?php
require_once 'PHPUnit/Framework.php';
require_once '../src/class.pdf.php';

class CpdfTest extends PHPUnit_Framework_TestCase
{
    private $pdf;

    function setUp()
    {
        $this->pdf = new Cpdf();
    }

    function testSelectFontAddsTheFontToTheFontStackWithAValidFont()
    {
        $fontnr = $this->pdf->selectFont('../src/fonts/Courier');
        $this->assertEquals(1, $fontnr);
    }

    function testSelectFontWillAddAFontToTheFontStackWithAnInvalidFontName()
    {
        $fontnr = $this->pdf->selectFont('SomethingInvalid');
        $this->assertEquals(0, $fontnr);
    }

}