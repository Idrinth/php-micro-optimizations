<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class ArrayKeyExistsHandler extends NodeVisitorAbstract
{
    private function isUseableFunctionCall(Node $node) {
        return $node->cond instanceof FuncCall 
            && $node->cond->name instanceof Name 
            && $node->cond->name->toLowerString() === 'array_key_exists'
            && $node->if instanceof ArrayDimFetch
            && $node->else instanceof ConstFetch 
            && $node->else->name->toLowerString() === 'null' ;
    }
    private function isSameVariable(Expr $expr1, Expr $expr2): bool
    {
        return $expr1 instanceof Variable
            && $expr2 instanceof Variable
            && ($expr1->name instanceof Name || is_string($expr1->name))
            && ($expr2->name instanceof Name || is_string($expr2->name))
            && "$expr1->name" === "$expr2->name";
    }
    private function isSameConstant(Expr $expr1, Expr $expr2)
    {
        return $expr1 instanceof ConstFetch
            && $expr2 instanceof ConstFetch
            && $expr1->name->toString() === $expr2->name->toString();
    }
    private function isEqualScalar(Expr $expr1, Expr $expr2)
    {
        return $expr1 instanceof Scalar
            && $expr2 instanceof Scalar
            && $expr2->getType() === $expr1->getType()
            && $expr2->value === $expr1->value;
    }
    private function isArraySame(Arg $argument, Expr $list)
    {
        return $this->isSameConstant($argument->value, $list) || $this->isSameVariable($argument->value, $list);
    }
    private function isKeySame(Arg $argument, ?Expr $key)
    {
        if (!$key instanceof Expr) {
            return false;
        }
        return $this->isSameConstant($argument->value, $key)
            || $this->isSameVariable($argument->value, $key)
            || $this->isEqualScalar($argument->value, $key);
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof Ternary
            && $this->isUseableFunctionCall($node)
            && $this->isArraySame($node->cond->args[1], $node->if->var)
            && $this->isKeySame($node->cond->args[0], $node->if->dim)
        ) {
            return new Ternary(new Isset_([$node->if]), $node->if, $node->else);
        }
    }
}
