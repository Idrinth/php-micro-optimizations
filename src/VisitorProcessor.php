<?php

namespace De\Idrinth\PHPMicroOptimizations;

interface VisitorProcessor
{
    public function process(string $file, array $ast): array;
}
