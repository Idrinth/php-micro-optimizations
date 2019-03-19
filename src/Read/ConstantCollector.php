<?php

namespace De\Idrinth\PHPMicroOptimizations\Read;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class ConstantCollector extends NodeVisitorAbstract
{
    private $constants = [];
    private $namespace = '';
    private $inClass = false;
    public function enterNode(Node $node) {
        if ($node instanceof Namespace_) {
            $this->namespace = "$node->name\\";
        } elseif ($node instanceof FuncCall && isset($node->args[0]) && $node->args[0]->value instanceof String_ && $node->name instanceof Name && "$node->name" === 'define') {
            $this->constants[] = $node->args[0]->value->value;
        } elseif(!$this->inClass && $node instanceof Const_) {
            $this->constants[] = ltrim("$this->namespace$node->name", '\\');
        } elseif ($node instanceof ClassLike) {
            $this->inClass = true;
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassLike) {
            $this->inClass = false;
        }
    }
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = '';
    }
    public function yield()
    {
        return $this->constants;
    }
}
