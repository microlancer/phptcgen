<?php

namespace PhpTcGen\Generator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $generator = new \PhpTcGen\Generator\Generator;
        $this->assertInstanceOf('PhpTcGen\Generator\Generator', $generator);
    }
}