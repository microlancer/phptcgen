<?php
error_reporting( E_ALL | E_STRICT );

if (class_exists('PHPUnit_Runner_Version', true)) {
    $phpUnitVersion = PHPUnit_Runner_Version::id();
    if ('@package_version@' !== $phpUnitVersion && version_compare($phpUnitVersion, '3.7.0', '<')) {
        echo 'This version of PHPUnit (' . PHPUnit_Runner_Version::id() . ') is not supported.' . PHP_EOL;
        exit(1);
    }
    unset($phpUnitVersion);
}

/**
 * Setup autoloading
 */
include __DIR__ . '/_autoload.php';
