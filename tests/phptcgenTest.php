<?php

use \Zend\Json\Json;

class phptcgenTest extends \PHPUnit_Framework_Testcase
{
    public function testRun()
    {
        // Clean up
        exec('rm -rf output/');
        // Run
        $command = '../bin/phptcgen ../examples/ ./output/';
        $output = '';
        exec($command, $output);
        $this->assertFileExists('output/Example1Test.php', "$command\n" . implode("\n", $output));
    }
}
