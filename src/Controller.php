<?php

namespace De\Idrinth\PHPMicroOptimizations;

use De\Idrinth\PHPMicroOptimizations\Clean\Cleaners;
use De\Idrinth\PHPMicroOptimizations\Evaluate\Evaluators;
use De\Idrinth\PHPMicroOptimizations\Read\Readers;
use De\Idrinth\PHPMicroOptimizations\Read\Result;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\Finder;

class Controller
{
    private $dirs = [];
    public function __construct(string ...$dirs) {
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $this->dirs = realpath($dir);
            } elseif (is_dir(getcwd() . DIRECTORY_SEPARATOR . $dir)) {
                $this->dirs = realpath(getcwd() . DIRECTORY_SEPARATOR . $dir);
            }
        }
    }
    public function run(): void
    {
        $data = $this->read();
        $this->write(
            $this->processAst(
                new Evaluators(),
                $this->processAst(
                    new Cleaners($data->getFunctions(), $data->getConstants()),
                    $data->getAsts()
                )
            )
        );
    }
    private function read(): Result
    {
        $reader = new Readers();
        $asts = [];
        foreach(Finder::create()->files()->name('*.php*')->in($this->dirs) as $file) {
            $asts["$file"] = $reader->process("$file", []);
        }
        return $reader->getResults($asts);
    }
    private function processAst(VisitorProcessor $processor, array $asts): array
    {
        $processed = [];
        foreach ($asts as $file => $ast) {
            $processed[$file] = $processor->process($file, $ast);
        }
        return $processed;
    }
    private function write(array $asts): void
    {
        $writer = new Standard();
        foreach ($asts as $file => $ast) {
            file_put_contents(str_replace('test', 'tests', $file), $writer->prettyPrintFile($ast));
        }
    }
}
