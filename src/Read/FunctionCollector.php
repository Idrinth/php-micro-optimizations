<?php

namespace De\Idrinth\PHPMicroOptimizations\Read;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class FunctionCollector extends NodeVisitorAbstract
{
    private $functions = [];
    private $namespace = '';
    public function enterNode(Node $node) {
        if ($node instanceof Namespace_) {
            $this->namespace = "$node->name\\";
        } elseif ($node instanceof Function_) {
            $this->functions[] = ltrim("$this->namespace$node->name", '\\');
        }
    }
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = '';
    }
    public function yield()
    {
        return $this->functions;
    }
}
