<?php

namespace De\Idrinth\PHPMicroOptimizations\Evaluate;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class FunctionToConstant extends NodeVisitorAbstract
{
    private $file;
    private $dir;
    public function setFile($file)
    {
        $this->file = $file;
        $this->dir = dirname($file);
    }
    public function enterNode(Node $node) {
        if (!$node instanceof FuncCall) {
            return;
        }
        if ("$node->name" === 'phpversion' && count($node->args) === 0) {
            return new ConstFetch(new FullyQualified('PHP_VERSION'));
        }
        if ("$node->name" === 'dirname' && count($node->args) === 1 && $node->args[0]->value instanceof File) {
            return new Dir();
        }
        if ("$node->name" === 'basename' && $node->args[0]->value instanceof File) {
            return $this->fromBasename($this->file, ...$node->args);
        }
        if ("$node->name" === 'basename' && $node->args[0]->value instanceof Dir) {
            return $this->fromBasename($this->dir, ...$node->args);
        }
    }
    private function fromBasename(string $path, Node\Arg ...$arguments)
    {
        if (count($arguments) === 1) {
            return new String_(basename($path));
        }
        if($arguments[1]->value instanceof String_) {
            return new String_(basename($path, $arguments[1]->value->value));
        }
        return null;
    }
}
