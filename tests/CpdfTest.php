<?php

namespace Cpdf\Tests;

class CpdfTest extends \PHPUnit_Framework_TestCase
{

    protected $cpdf;

    public function setUp()
    {
        $this->cpdf = new \Cpdf_Extension(\Cpdf_Common::$Layout['A4']);
    }

    public function testConstructor()
    {
        $this->assertTrue(is_object($this->cpdf));
    }
}
