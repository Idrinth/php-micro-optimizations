<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class UnreachableCode extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        if (!$node instanceof FunctionLike) {
            return null;
        }
        $node->stmts = $this->adjustLast($node->stmts??[]);
        return $node;
    }

    /**
     * @param Node[] $statements
     * @return Node[]
     */
    private function adjustLast(array $statements): array
    {
        foreach ($statements as $pos => $statement) {
            if ($statement instanceof Return_ || $statement instanceof Throw_) {
                return array_slice($statements, 0, $pos +1);
            }
            if ($statement instanceof Expression && $statement->expr instanceof Exit_) {
                return array_slice($statements, 0, $pos +1);
            }
        }
        return $statements;
    }
}
