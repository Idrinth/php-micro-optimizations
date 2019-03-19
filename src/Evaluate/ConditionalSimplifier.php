<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BinaryOp\LogicalXor;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;

/**
 * @internal
 */
class ConditionalSimplifier extends BaseConditionEvaluator
{
    public function enterNode(Node $node) {
        if ($node instanceof Ternary) {
            if ($this->isAlwaysTrue($node->cond)) {
                return $node->if ?? $node->cond;
            }
            if ($this->isAlwaysFalse($node->cond)) {
                return $node->else;
            }
            if ($node->cond instanceof BooleanNot && $node->if) {
                return new Ternary($node->cond->expr, $node->else, $node->if);
            }
        }
        if ($node instanceof If_ && $this->isAlwaysTrue($node->cond)) {
            $node->else = null;
            $node->elseifs = [];
            return $node;
        }
        if ($node instanceof If_ && $this->isAlwaysFalse($node->cond) && $node->elseifs) {
            while ($this->isAlwaysFalse($node->cond) && $node->elseifs) {
                $first = array_shift($node->elseifs);
                $node = new If_($first->cond, [
                    'stmts' => $first->stmts,
                    'elseifs' => $node->elseifs??[],
                    'else' =>$node->else
                ]);
            }
            return $node;
        }
        if (($node instanceof If_ || $node instanceof ElseIf_) && $this->isAlwaysFalse($node->cond)) {
            $node->stmts = [];
            return $node;
        }
        if ($node instanceof If_ && count($node->elseifs) === 0 && $node->else instanceof Else_ && count($node->stmts) === 1 && count($node->else->stmts) === 1 && $node->stmts[0] instanceof Expr && $node->else->stmts[0] instanceof Expr) {
            return new Ternary($node->cond, $node->stmts[0], $node->else->stmts[0]);
        }
    }
    public function leaveNode(Node $node)
    {
        if (!$node instanceof If_) {
            return;
        }
        if ($node->cond instanceof LogicalAnd || $node->cond instanceof LogicalOr || $node->cond instanceof LogicalXor || $node->cond instanceof BooleanAnd || $node->cond instanceof BooleanOr) {
            if ($node->cond->left instanceof FuncCall || $node->cond->left instanceof MethodCall || $node->cond->left instanceof StaticCall) {
                $pre = $node->cond->left;
                $node->cond = $node->cond->right;
                return [new Expression($pre), new Nop(), $node];
            }
            return;
        }
        if ($this->isAlwaysTrue($node->cond)) {
            return $node->stmts;
        }
        if ($this->isAlwaysFalse($node->cond)) {
            if ($node->else) {
                return $node->else;
            }
            return NodeTraverser::REMOVE_NODE;
        }
    }
}
