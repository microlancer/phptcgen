<?php

namespace PhpTcGen\Generator;

use \DocBlock\Parser;
use \Zend\Filter\Word\CamelCaseToSeparator;

/**
 * Test case generator class
*/
class Generator
{
    const VERSION = '1.0.0';

    private $inputDir;
    private $inputFile;
    private $outputDir;
    private $outputFile;
    private $files;
    private $outcomes;
    private $verbose;

    public function __construct()
    {
        $this->verbose = null;
        $this->inputDir = null;
        $this->inputFile = null;
        $this->outputDir = null;
        $this->outputFile = null;
        $this->files = array();
        $this->outcomes = array();
    }

    public function createTestCases($input, $output)
    {
        if (empty($input) || empty($output)) {
            throw new \Exception('Input dir/file and output dir/file must be specified for processing.');
        }

        if (is_readable($input) && is_dir($input)) {
            $this->inputDir = preg_replace('/(\\/)+$/', '', trim($input)); // remove trailing slashes
        } else if (is_readable($input) && is_file($input)) {
            $this->inputFile = $input;
        } else {
            throw new \Exception('Cannot use file or directory ' . $input);
        }
        
        if (is_readable($output) && is_dir($output)) {
            $this->outputDir = preg_replace('/(\/)+$/', '', trim($output)); // remove trailing slashes
        } else if (is_readable($output) && is_file($output)) {
            $this->outputFile = $output;
        } else if (preg_match('/.*\.php$/', $output)) {
            $this->outputFile = $output;
        } else {
            mkdir($output);
            $this->outputDir = preg_replace('/(\/)+$/', '', trim($output)); // remove trailing slashes
        }

        if ($this->inputDir && $this->outputFile) {
            throw new \Exception('Cannot use a single file as the output for a directory.');
        }

        if ($this->inputFile) {
            $this->processInputFile();
        } else {
            $this->processInputDir();
        }
    }

    private function processInputFile()
    {
        // TODO
        throw new \Exception('Not implemented yet.');
    }

    private function getFiles($inputDir, $prefix = '')
    {
        $dir = dir($inputDir);
        while (false !== ($file = $dir->read())) {
            if (in_array($file, array('.', '..'))) {
                continue;
            }

            $fullFile = preg_replace('/(\\/)+$/', '', $dir->path) . '/' . $file;
            if (is_dir($fullFile)) {
                $this->getFiles($fullFile, $prefix . $file . '/');
            } else if (is_file($fullFile) && is_readable($fullFile) && preg_match('#.php$#', $file)) {
                if ($this->verbose) {
                    echo 'Adding file: ' . $prefix . $file . "\n";
                }
                $this->files[] = array(
                    'path' => $prefix,
                    'file' => $file,
                    'class' => str_replace('.php', '', $file),
                    'methods' => array(),
                );
            } else {
                // Skip any non-readable, non-php files.
                echo 'Skipping file: ' . $fullFile . "\n";
            }
        }
        $dir->close();
    }

    private function getAnnotationsForFiles()
    {
        echo "Processing " . count($this->files) . ' files.' . "\n";

        foreach ($this->files as $key => $file) {

            // We should be parsing the source, rather than loading it. Then we wouldn't have to worry about
            // executing the code (and missing include file dependencies, etc.).
            $includeFile = $this->inputDir . '/' . $file['path'] . $file['file'];

            exec("php -l $includeFile", $output, $return);

            if ($return !== 0) {
                echo $output;
                echo "!!! Warning: Unable to include " . $includeFile . " due to PHP syntax errors.\n";
                unset($this->files[$key]);
                continue;
            }

            $className = '\\' . str_replace('/', '\\', $file['path']) . $file['class'];

            if (class_exists($className, false) || interface_exists($className, false)) {

                // Class was already previously loaded.
                echo "!!! Warning: Unable to load previously loaded class: $className ($includeFile)\n";
                unset($this->files[$key]);
                continue;

            }

            include_once $includeFile;

            $parser = new Parser();

            if (interface_exists($className, false)) {
                echo "!!! Skipping interface class: $className\n";
                unset($this->files[$key]);
                continue;
            }

            if (!class_exists($className, false)) {
                // Todo: We need to handle Abstract class methods somehow.
                echo "!!! Warning: Unable to load class: $className (perhaps abstract or interface)\n";
                unset($this->files[$key]);
                continue;
            }


            echo "Analyzing $className\n";

            $parser->analyze($className);
            $methods = $parser->getMethods();

            if (!is_array($methods)) {
                echo "!!! Warning: Unable to get methods of class " . $className . "\n";
                unset($this->files[$key]);
                continue;
            } else {

//                echo "Found " . count($methods) . ' methods' . "\n";

            }

            foreach($methods as $method)
            {

                $annotations = $method->getAnnotations(array("tc_takes", "tc_uses", "tc_may"));

                if (!is_array($annotations) || empty($annotations)) {
//                    echo "No annotations\n";
                    continue;
                }
                
                echo "Found " . count($annotations) . " annotations.\n";

                $tcArray = array(
                    'takes' => array(),
                    'uses' => array(),
                    'may' => array(),
                );

                foreach ($annotations as $annotation)
                {
                    switch ($annotation->getName()) {
                        case '@tc_takes':
                            $tcArray['takes'] = array_merge($tcArray['takes'], preg_split('/[\s\,]+/', implode(',', $annotation->values)));
                            break;
                        case '@tc_uses':
                            $tcArray['uses'] = array_merge($tcArray['uses'], preg_split('/[\s\,]+/', implode(',', $annotation->values)));
                            break;
                        case '@tc_may':
                            $tcArray['may'] = array_merge($tcArray['may'], preg_split('/[\s\,]+/', implode(',', $annotation->values)));
                            break;
                        default:
                            throw new \Exception('Unexpected annotation name: ' . $annotation->getName());
                    }
                }
                $methodKey = '\\' . str_replace('/', '\\', $file['path']) . $file['class'] . '::' . $method->getName();
                $this->dependencies[$methodKey] = $tcArray['uses'];
                $this->outcomes[$methodKey] = $tcArray['may'];
                $this->files[$key]['methods'][$method->getName()] = $tcArray;
            }
            
        }
    }

    private function getAllOutcomesByMethod($method)
    {

        list($className, $methodName) = explode('::', $method);
        if (!method_exists($className, $methodName)) {
            throw new \Exception("Unable to find $className::$methodName\n");
        }
        // Direct outcomes
        if (!empty($this->outcomes[$method])) {
            $ret = $this->outcomes[$method];
        } else {
            // No direct outcomes
            $ret = array();
        }

        // The possible outcomes based on dependencies of $method
        if (!empty($this->dependencies[$method])) {
            foreach ($this->dependencies[$method] as $dependency) {
                $ret = array_merge($ret, $this->getAllOutcomesByMethod($dependency));
            }
        }

        
        return $ret;
    }

    private function processInputDir()
    {
        $this->getFiles($this->inputDir . '/');
        $this->getAnnotationsForFiles();

        foreach ($this->files as $file) {
            $testFile = $this->outputDir . '/' . $file['path'] . $file['class'] . 'Test.php';
            echo "Creating $testFile\n";
            if (!is_dir(dirname($testFile))) {
                mkdir(dirname($testFile), 0777, true);
            }
            $testPhpCode = file_get_contents(__DIR__ . '/../templates/template-class.txt');
            $testMethods = array();

            foreach ($file['methods'] as $methodName => $tcArray) {

                // @tc_takes test cases
                foreach ($tcArray['takes'] as $takes) {
                    $testMethodName = ucfirst($methodName) . 'Takes' . ucfirst($takes);

                    $c = new CamelCaseToSeparator();
                    $c->setSeparator(' ');
                    $reason = 'when input is ' . strtolower($c->filter($takes));

                    $testPhpMethod = file_get_contents(__DIR__ . '/../templates/template-method.txt');

                    $testPhpMethod = str_replace('%TCGEN_METHODNAME%', $methodName, $testPhpMethod);
                    $testPhpMethod = str_replace('%TCGEN_METHODNAME_UCFIRST%', $testMethodName, $testPhpMethod);
                    $testPhpMethod = str_replace('%REASON%', $reason, $testPhpMethod);
                    $testPhpMethod = str_replace('%TCGEN_CLASSNAME%', $file['class'], $testPhpMethod);

                    $testMethods[] = $testPhpMethod;
                }
                
                // @tc_uses test cases

                foreach ($tcArray['uses'] as $uses) {

                    $usesFullClass = '';

                    if (!preg_match('#::.+$#', $uses)) {
                        throw new \Exception(
                            '@tc_uses dependency format should be \\YourNamespace\\YourClass::methodName; got: ' .
                            $uses);
                    }

                    list($usesFullClass, $usesMethodName) = explode('::', $uses);

                    
                    $usesNamespace = preg_replace('#\\.*?$#', '', $usesFullClass);
                    $usesClass = preg_replace('#.*\\\#', '', $usesFullClass);

                    $outcomes = $this->getAllOutcomesByMethod($uses);

                    foreach ($outcomes as $outcome) {

                        $testMethodName = ucfirst($methodName) . 'Uses' . ucfirst($usesMethodName);
                        $testMethodName = ucfirst($methodName) . 'When' . ucfirst($usesClass) . ucfirst($usesMethodName) . ucfirst($outcome);

                        $c = new CamelCaseToSeparator();
                        $c->setSeparator(' ');
                        $reason = 'when dependency of call to ' . $uses . '() results in ' . strtolower($c->filter($outcome));

                        $testPhpMethod = file_get_contents(__DIR__ . '/../templates/template-method.txt');

                        $testPhpMethod = str_replace('%TCGEN_METHODNAME%', $methodName, $testPhpMethod);
                        $testPhpMethod = str_replace('%TCGEN_METHODNAME_UCFIRST%', $testMethodName, $testPhpMethod);
                        $testPhpMethod = str_replace('%REASON%', $reason, $testPhpMethod);
                        $testPhpMethod = str_replace('%TCGEN_CLASSNAME%', $file['class'], $testPhpMethod);

                        $testMethods[] = $testPhpMethod;
                    }
                }
            }

            $testNamespace = 'Test';

            if ($file['path']) {
                $testNamespace .= '\\' . preg_replace('#\\\$#', '', str_replace('/', '\\', $file['path']));
            }

            $testPhpCode = str_replace('%TCGEN_TESTNAMESPACE%', $testNamespace, $testPhpCode);
            $testPhpCode = str_replace('%TCGEN_FULLCLASSNAME%', '\\' . str_replace('/', '\\', $file['path']) . $file['class'], $testPhpCode);
            $testPhpCode = str_replace('%TCGEN_CLASSNAME%', $file['class'], $testPhpCode);
            $testPhpCode = str_replace('%TCGEN_TESTCLASSNAME%', $file['class'] . 'Test', $testPhpCode);
            $testPhpCode = str_replace('%TCGEN_METHODS%', implode("\n\n", $testMethods), $testPhpCode);
            $testPhpCode = str_replace('%TCGEN_VERSION%', self::VERSION, $testPhpCode);

            file_put_contents($testFile, $testPhpCode);
        }
    }
}
