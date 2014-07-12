<?php
/**
 * Setup autoloading
 */

include_once __DIR__ . '/../vendor/autoload.php';

$loader = new Zend\Loader\StandardAutoloader(
    array(
        Zend\Loader\StandardAutoloader::LOAD_NS => array(
            'Wsa' => __DIR__ . '/../src/Wsa/src/Wsa',
            'Live' => __DIR__ . '/../module/Live/src/Live',
            'Main' => __DIR__ . '/../module/Main/src/Main',
            'Vod' => __DIR__ . '/../module/Vod/src/Vod',
            'Bi' => __DIR__ . '/../module/Bi/src/Bi',
            'JsonSchema' => __DIR__ . '/../vendor/JsonSchema/src/JsonSchema/src/JsonSchema',
        ),
    )
);
$loader->register();
