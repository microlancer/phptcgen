#!/usr/bin/env php
<?php

if (!isset($argv[1])) {
    echo "Usage: phptcgen <file>\n";
    exit(1);
}
echo "Generating test cases for {$argv[1]}...\n";
