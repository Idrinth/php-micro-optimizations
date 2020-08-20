<?php

namespace De\Idrinth\PHPMicroOptimizations;

use PhpParser\NodeTraverser;

final class RepeatedTraverser extends NodeTraverser
{
    public function traverse(array $nodes): array
    {
        do {
            $pre = json_encode($nodes);
            $nodes = parent::traverse($nodes);
            echo '.';
        } while ($pre !== json_encode($nodes));
        return $nodes;
    }
}
