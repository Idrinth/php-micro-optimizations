<?php

namespace De\Idrinth\PHPMicroOptimizations\Clean;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class UseRemover extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        return $node instanceof Use_ ? NodeTraverser::REMOVE_NODE : null;
    }
}
