<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use De\Idrinth\PHPMicroOptimizations\RepeatedTraverser;
use De\Idrinth\PHPMicroOptimizations\VisitorProcessor;

class Evaluators implements VisitorProcessor
{
    private $traverser;
    /**
     * @var FunctionToConstant 
     */
    private $function2constant;
    public function __construct()
    {
        $this->traverser = new RepeatedTraverser();
        $this->traverser->addVisitor(new ExpressionEvaluator());
        $this->traverser->addVisitor(new ConditionSimplifier());
        $this->traverser->addVisitor(new ConditionalSimplifier());
        $this->traverser->addVisitor(new ArrayKeyExistsHandler());
        $this->traverser->addVisitor($this->function2constant = new FunctionToConstant());
    }

    public function process(string $file, array $ast): array {
        $this->function2constant->setFile($file);
        return $this->traverser->traverse($ast);
    }

}
