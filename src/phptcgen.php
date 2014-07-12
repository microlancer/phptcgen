#!/usr/bin/env php
<?php

include '_autoload.php';

use PhpTcGen\Generator\Generator;

if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Usage: phptcgen <input-dir-or-file> <output-dir>\n";
    exit(1);
}

list($command, $input, $output) = $argv;

echo "Generating test cases for $input into $output\n";
$generator = new Generator();
$generator->createTestCases($input, $output);

