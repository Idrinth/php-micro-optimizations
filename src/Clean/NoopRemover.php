<?php

namespace De\Idrinth\PHPMicroOptimizations\Clean;

use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class NoopRemover extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        return $node instanceof Nop ? NodeTraverser::REMOVE_NODE : null;
    }
}
