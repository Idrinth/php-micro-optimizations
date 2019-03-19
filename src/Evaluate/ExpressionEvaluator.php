<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class ExpressionEvaluator extends NodeVisitorAbstract
{
    private $evaluator;
    public function __construct() {
        $this->evaluator = new ConstExprEvaluator();
    }

    public function enterNode(Node $node) {
        if ($node instanceof Expr && !$node instanceof Scalar) {
            try {
                $result = $this->evaluator->evaluateSilently($node);
                if (is_int($result)) {
                    return new LNumber($result);
                }
                if (is_float($result)) {
                    return new DNumber($result);
                }
                if (is_string($result)) {
                    return new String_($result);
                }
                if ($result instanceof Node) {
                    return $result;
                }
            } catch (ConstExprEvaluationException $ex) {
                // not important
            }
        }
    }
    private function getConcatParts(Concat $node): array
    {
        $parts = [];
        if ($node->left instanceof Concat) {
            $parts = array_merge($parts, $this->getConcatParts($node->left));
        } else {
            $parts[] = $node->left;
        }
        if ($node->right instanceof Concat) {
            $parts = array_merge($parts, $this->getConcatParts($node->right));
        } else {
            $parts[] = $node->right;
        }
        return $parts;
    }
    private function canConcat(Node $node)
    {
        return $node instanceof String_ || $node instanceof DNumber || $node instanceof LNumber;
    }
    public function leaveNode(Node $node) {
        if (!$node instanceof Concat) {
            return;
        }
        $parts = $this->getConcatParts($node);
        do {
            $prev = count($parts);
            foreach($parts as $pos => $part) {
                if($this->canConcat($part) && isset($parts[$pos+1]) && $this->canConcat($parts[$pos+1])) {
                    array_splice($parts, $pos, 2, [new String_($part->value . $parts[$pos+1]->value)]);
                    break;
                }
            }
        } while(count($parts) < $prev);
        while (count($parts) > 1) {
            array_splice($parts, 0, 2, [new Concat($parts[0], $parts[1])]);
        }
        return $parts[0];
    }
}
