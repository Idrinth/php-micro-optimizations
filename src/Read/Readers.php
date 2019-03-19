<?php

namespace De\Idrinth\PHPMicroOptimizations\Read;

use De\Idrinth\PHPMicroOptimizations\VisitorProcessor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class Readers implements VisitorProcessor
{
    private $traverser;
    private $functions;
    private $constants;
    private $parser;
    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this->functions = new FunctionCollector());
        $this->traverser->addVisitor($this->constants = new ConstantCollector());
    }

    public function process(string $file, array $ast): array {
        return $this->traverser->traverse($this->parser->parse(file_get_contents($file)));
    }
    /**
     * @param Node[] $asts
     * @return Result
     */
    public function getResults(array $asts): Result
    {
        return new Result($this->functions->yield(), $this->constants->yield(), $asts);
    }
}
