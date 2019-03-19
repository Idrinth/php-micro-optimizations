<?php

namespace De\Idrinth\PHPMicroOptimizations;

use PhpParser\NodeTraverser;

class RepeatedTraverser extends NodeTraverser
{
    public function traverse(array $nodes): array
    {
        do {
            $pre = json_encode($nodes);
            $nodes = parent::traverse($nodes);
        } while ($pre !== json_encode($nodes));
        return $nodes;
    }
}
