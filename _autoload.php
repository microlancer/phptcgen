<?php
/**
 * Setup autoloading
 */

include_once __DIR__ . '/vendor/autoload.php';

$loader = new Zend\Loader\StandardAutoloader(
    array(
        Zend\Loader\StandardAutoloader::LOAD_NS => array(
            'PhpTcGen' => __DIR__ . '/src/',
            'Example2' => __DIR__ . '/examples/Example2/',
        ),
    )
);
$loader->register();
