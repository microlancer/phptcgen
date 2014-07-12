<?php

namespace PhpTcGen\Generator;

use DocBlock\Parser;

class Generator
{

    private $inputDir;
    private $inputFile;
    private $outputDir;
    private $outputFile;
    private $files;

    public function __construct()
    {
        $this->inputDir = null;
        $this->inputFile = null;
        $this->outputDir = null;
        $this->outputFile = null;
        $this->files = array();
    }

    public function createTestCases($input, $output)
    {
        if (empty($input) || empty($output)) {
            throw new \Exception('Input dir/file and output dir/file must be specified for processing.');
        }

        if (is_readable($input) && is_dir($input)) {
            $this->inputDir = $input;
        } else if (is_readable($input) && is_file($input)) {
            $this->inputFile = $input;
        } else {
            throw new \Exception('Cannot use file or directory ' . $input);
        }
        
        if (is_readable($output) && is_dir($output)) {
            $this->outputDir = $output;
        } else if (is_readable($output) && is_file($output)) {
            $this->outputFile = $output;
        } else if (preg_match('/.*\.php$/', $output)) {
            $this->outputFile = $output;
        } else {
            mkdir($output);
            $this->outputDir = $output;
        }

        if ($this->inputDir && $this->outputFile) {
            throw new \Exception('Cannot use a single file as the output for a directory.');
        }

        if ($this->inputFile) {
            $this->processInputFile();
        } else {
            $this->processInputDir($this->inputDir);
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
            if (is_dir($dir->path . $file)) {
                $this->getFiles($dir->path . $file, $prefix . $file . '/');
            } else {
                echo 'Adding file: ' . $prefix . $file . "\n";
                $this->files[] = array(
                    'path' => $prefix,
                    'file' => $file,
                    'class' => str_replace('.php', '', $file),
                    'methods' => array(),
                );
            }
        }
        $dir->close();
    }

    private function processInputDir($inputDir)
    {
        $this->getFiles($inputDir);
        foreach ($this->files as $key => $file) {
            include_once $inputDir . '/' . $file['path'] . $file['file'];
            $className = str_replace('/', '\\', $file['path']) . $file['class'];
            //$obj = new $className();
            $parser = new Parser();
            $parser->analyze($className);
            $methods = $parser->getMethods();
            foreach($methods as $method)
            {

                $annotations = $method->getAnnotations(array("tc_takes", "tc_uses", "tc_may"));
                if (!is_array($annotations)) {
                    continue;
                }
                $tcArray = array(
                    'takes' => array(),
                    'uses' => array(),
                    'may' => array(),
                );
                $takes = array();
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
            }
            $this->files[$key]['methods'][$method->getName()] = $tcArray;
        }
        var_dump($this->files);
    }
}