<?php

namespace De\Idrinth\PHPMicroOptimizations\Clean;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class FullyQualifier extends NodeVisitorAbstract
{
    private $functions;
    private $constants;
    private $namespace = '';
    
    public function __construct(array $functions, array $constants)
    {
        $this->functions = $functions;
        $this->constants = $constants;
    }
    public function enterNode(Node $node) {
        if ($node instanceof Namespace_) {
            $this->namespace = "$node->name\\";
        } elseif ($node instanceof FuncCall && $node->name instanceof Name && $this->shouldChange($node->name)) {
            $node->name = new FullyQualified("$node->name", $node->name->getAttributes());
        } elseif ($node instanceof ConstFetch && $this->shouldChange($node->name)) {
            $node->name = new FullyQualified("$node->name", $node->name->getAttributes());
        }
    }
    private function shouldChange(Name $name)
    {
        return !$name->isFullyQualified() && !in_array("$this->namespace$name", $this->constants, true);
    }

    public function beforeTraverse(array $nodes)
    {
        $this->namespace = '';
        $this->uses = [];
    }
}
