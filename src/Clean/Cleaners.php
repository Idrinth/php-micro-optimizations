<?php

namespace De\Idrinth\PHPMicroOptimizations\Clean;

use De\Idrinth\PHPMicroOptimizations\VisitorProcessor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class Cleaners implements VisitorProcessor
{
    private $traverser;
    public function __construct(array $functions, array $constants) {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NoopRemover());
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor(new FullyQualifier($functions, $constants));
        $this->traverser->addVisitor(new UseRemover());
    }
    public function process(string $file, array $ast): array {
        return $this->traverser->traverse($ast);
    }
}
