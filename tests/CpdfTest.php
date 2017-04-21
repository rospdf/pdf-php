<?php

namespace Cpdf\Tests;

use ROSPDF\Cpdf;
use ROSPDF\CpdfExtension;

class CpdfTest extends \PHPUnit_Framework_TestCase
{

    protected $cpdf;

    public function setUp()
    {
        $this->cpdf = new CpdfExtension(Cpdf::$Layout['A4']);
    }

    public function testConstructor()
    {
        $this->assertTrue(is_object($this->cpdf));
    }
}
