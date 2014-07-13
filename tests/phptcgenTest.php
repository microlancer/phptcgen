<?php

use \Zend\Json\Json;

class phptcgenTest extends \PHPUnit_Framework_Testcase
{
    public function testRun()
    {
        // Clean up
        exec('rm -rf output/');
        // Run
        $command = '../bin/phptcgen ../examples/ ../output/';
        $output = '';
        exec($command, $output);

        // Uncomment to debug the output
        //echo implode("\n", $output);
        
        $this->assertFileEquals(__DIR__ . '/../baselines/Example1Test.php', '../output/Example1Test.php');
        $this->assertFileEquals(__DIR__ . '/../baselines/Example2/Example2Test.php', '../output/Example2/Example2Test.php');
        $this->assertFileEquals(__DIR__ . '/../baselines/Example3/Example3Test.php', '../output/Example3/Example3Test.php');
    }
}
