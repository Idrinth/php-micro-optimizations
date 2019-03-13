<?php

namespace De\Idrinth\PHPMicroOptimizations;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class ConditionSimplifier extends NodeVisitorAbstract
{
    public function enterNode(Node $node) {
        if ($node instanceof Ternary) {
            if ($this->isAlwaysTrue($node->cond)) {
                return $node->if ?? $node->cond;
            }
            if ($this->isAlwaysFalse($node->cond)) {
                return $node->else;
            }
        }
        if ($node instanceof If_ && $this->isAlwaysTrue($node->cond)) {
            $node->else = null;
            $node->elseifs = [];
            return $node;
        }
        if ($node instanceof If_ && $this->isAlwaysFalse($node->cond) && $node->elseifs) {
            $first = array_shift($node->elseifs);
            return new If_($first->cond, [
                'stmts' => $first->stmts,
                'elseifs' => $node->elseifs??[],
                'else' =>$node->else
            ]);
        }
        if (($node instanceof If_ || $node instanceof ElseIf_) && $this->isAlwaysFalse($node->cond)) {
            $node->stmts = [];
            return $node;
        }
    }
    public function leaveNode(Node $node) {
        if ($node instanceof If_ && $this->isAlwaysTrue($node->cond)) {
            return $node->stmts;
        }
        if ($node instanceof If_ && $this->isAlwaysFalse($node->cond)) {
            if ($node->else) {
                return $node->else;
            }
            return NodeTraverser::REMOVE_NODE;
        }
    }
    private function isAlwaysTrue(Node $expression)
    {
        if ($expression instanceof ConstFetch && $expression->name->toLowerString() === 'true') {
            return true;
        }
        return false;
    }
    private function isAlwaysFalse(Node $expression)
    {
        if ($expression instanceof ConstFetch && $expression->name->toLowerString() === 'false') {
            return true;
        }
        if ($expression instanceof ConstFetch && $expression->name->toLowerString() === 'null') {
            return true;
        }
        return false;
    }
}
