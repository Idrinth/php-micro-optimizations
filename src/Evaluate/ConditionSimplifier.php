<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;

/**
 * @internal
 */
class ConditionSimplifier extends BaseConditionEvaluator
{
    public function enterNode(Node $node) {
        if ($node instanceof BooleanNot && $node->expr instanceof BooleanNot) {
            return $node->expr->expr;
        }
        if ($node instanceof LogicalAnd || $node instanceof BooleanAnd) {
            if ($this->isAlwaysTrue($node->left)) {
                return $node->right;
            }
            if ($this->isAlwaysTrue($node->right)) {
                return $node->left;
            }
            if ($this->isAlwaysFalse($node->left) || $this->isAlwaysFalse($node->right)) {
                return $node->left;
            }
        }
        if ($node instanceof LogicalOr || $node instanceof BooleanOr) {
            if ($this->isAlwaysTrue($node->left)) {
                return $node->left;
            }
            if ($this->isAlwaysFalse($node->left)){
                return $node->right;
            }
            if ($this->isAlwaysFalse($node->right)) {
                return $node->left;
            }
        }
        if ($node instanceof LogicalXOr) {
            if ($this->isAlwaysTrue($node->left) && $this->isAlwaysFalse($node->right)) {
                return new ConstFetch('true');
            }
            if ($this->isAlwaysFalse($node->left) && $this->isAlwaysTrue($node->right)) {
                return new ConstFetch('true');
            }
            if ($this->isAlwaysTrue($node->left) && $this->isAlwaysTrue($node->right)) {
                return new ConstFetch('false');
            }
            if ($this->isAlwaysFalse($node->left) && $this->isAlwaysFalse($node->right)) {
                return new ConstFetch('false');
            }
        }
    }
}
