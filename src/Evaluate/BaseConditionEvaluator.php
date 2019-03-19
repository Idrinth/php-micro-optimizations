<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
abstract class BaseConditionEvaluator extends NodeVisitorAbstract
{
    protected function isAlwaysTrue(Node $expression): bool
    {
        if ($expression instanceof ConstFetch && $expression->name->toLowerString() === 'true') {
            return true;
        }
        if (($expression instanceof DNumber || $expression instanceof LNumber) && $expression->value !== 0) {
            return true;
        }
        if ($expression instanceof String_ && $expression->value !== '') {
            return true;
        }
        if ($expression instanceof BooleanNot) {
            return $this->isAlwaysFalse($expression->expr);
        }
        return false;
    }
    protected function isAlwaysFalse(Node $expression): bool
    {
        if ($expression instanceof ConstFetch && $expression->name->toLowerString() === 'false') {
            return true;
        }
        if ($expression instanceof ConstFetch && $expression->name->toLowerString() === 'null') {
            return true;
        }
        if (($expression instanceof DNumber || $expression instanceof LNumber) && $expression->value === 0) {
            return true;
        }
        if ($expression instanceof String_ && $expression->value === '') {
            return true;
        }
        if ($expression instanceof BooleanNot) {
            return $this->isAlwaysTrue($expression->expr);
        }
        return false;
    }
}