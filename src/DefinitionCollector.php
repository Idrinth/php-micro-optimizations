<?php

namespace De\Idrinth\PHPMicroOptimizations;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

class DefinitionCollector extends NodeVisitorAbstract
{
    private $constants = [];
    private $functions = [];
    private $namespace = '';
    private $inClass = false;
    private $uses = [];
    public function enterNode(Node $node) {
        if ($node instanceof Namespace_) {
            $this->namespace = "$node->name\\";
        } elseif ($node instanceof FuncCall && isset($node->args[0]) && $node->args[0]->value instanceof String_ && $node->name instanceof Name && "$node->name" === 'define') {
            $this->constants[] = $node->args[0]->value->value;
        } elseif(!$this->inClass && $node instanceof Const_) {
            $this->constants[] = ltrim("$this->namespace$node->name", '\\');
        } elseif ($node instanceof ClassLike) {
            $this->inClass = true;
        } elseif ($node instanceof Function_) {
            $this->functions[] = ltrim("$this->namespace$node->name", '\\');
        }
    }
    public function leaveNode(Node $node) {
        if ($node instanceof ClassLike) {
            $this->inClass = false;
        }
    }
    public function resetNamespace()
    {
        $this->namespace = '';
        $this->uses = [];
    }
    public function getConstants()
    {
        return $this->constants;
    }
    public function getFunctions()
    {
        return $this->functions;
    }
}
