#!/usr/bin/env php
<?php

// Autoloader for the phptcgen application
include __DIR__ . '/../_autoload.php';

// Autoloader for the target application (always checks relative to current execution folder)
if (file_exists('./autoload.php')) {
    include './autoload.php';
} else {
    echo "\n";
    echo "!!! Warning: No ./autoload.php file defined; you may be unable to load your target sources.\n";
    echo "             For an example autoload.php, see user-autoload-example.php in phptcgen directory.\n";
    echo "\n";
}

use PhpTcGen\Generator\Generator;

if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Usage: phptcgen <input-dir-or-file> <output-dir>\n";
    exit(1);
}

list($command, $input, $output) = $argv;

echo "Generating test cases for $input into $output\n";
$generator = new Generator();
$generator->createTestCases($input, $output);

