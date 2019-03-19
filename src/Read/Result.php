<?php

namespace De\Idrinth\PHPMicroOptimizations\Read;

class Result
{
    private $functions;
    private $constants;
    private $asts;
    public function __construct(array $functions, array $constants, array $asts) {
        $this->functions = $functions;
        $this->constants = $constants;
        $this->asts = $asts;
    }
    public function getFunctions(): array {
        return $this->functions;
    }

    public function getConstants(): array {
        return $this->constants;
    }

    public function getAsts(): array {
        return $this->asts;
    }
}
